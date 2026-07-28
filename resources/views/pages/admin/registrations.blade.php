<?php

use App\Models\FellowRegistration;
use App\Services\GerejaOptionService;
use App\Services\TeamAssignmentService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new
#[Layout('layouts.app')]
#[Title('Data Pendaftar')]
class extends Component {
    use Toast;

    public string $search = '';

    public string $filterGereja = '';

    public string $filterGender = '';

    public string $filterTeam = '';

    public string $sortBy = 'terbaru';

    public ?string $previewUrl = null;

    public string $previewName = '';

    public bool $previewIsPdf = false;

    public function mount(): void
    {
        if (! Auth::check()) {
            $this->redirect(route('login'), navigate: true);
        }
    }

    public function logout(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        $this->redirect(route('login'), navigate: true);
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'filterGereja', 'filterGender', 'filterTeam', 'sortBy']);
        $this->sortBy = 'terbaru';
    }

    public function assignTeam(int $id, string $team, TeamAssignmentService $teams): void
    {
        $value = $team === '' ? null : (int) $team;

        try {
            $teams->assignManual($id, $value);
            $this->success('Tim diperbarui.', position: 'toast-bottom');
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), position: 'toast-bottom');
        }
    }

    public function showBukti(int $id): void
    {
        $registration = FellowRegistration::query()->findOrFail($id);

        if (! $registration->hasBuktiTf()) {
            $this->error('Bukti transfer tidak ditemukan di server.', position: 'toast-bottom');

            return;
        }

        $this->previewUrl = route('admin.bukti-tf', $registration);
        $this->previewName = $registration->nama;
        $this->previewIsPdf = str($registration->bukti_tf_path)->lower()->endsWith('.pdf');
    }

    public function closePreview(): void
    {
        $this->previewUrl = null;
        $this->previewName = '';
        $this->previewIsPdf = false;
    }

    public function deleteRegistration(int $id): void
    {
        $registration = FellowRegistration::query()->findOrFail($id);

        if ($registration->bukti_tf_path && Storage::disk('public')->exists($registration->bukti_tf_path)) {
            Storage::disk('public')->delete($registration->bukti_tf_path);
        }

        $registration->delete();

        if ($this->previewUrl) {
            $this->closePreview();
        }

        $this->success('Pendaftar dihapus.', position: 'toast-bottom');
    }

    public function whatsappLink(string $whatsapp): string
    {
        $digits = preg_replace('/\D+/', '', $whatsapp) ?? '';

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        } elseif (! str_starts_with($digits, '62') && $digits !== '') {
            $digits = '62'.$digits;
        }

        return 'https://wa.me/'.$digits;
    }

    public function registrations(): Collection
    {
        $query = FellowRegistration::query()
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';

                $query->where(function ($inner) use ($term) {
                    $inner->where('nama', 'like', $term)
                        ->orWhere('whatsapp', 'like', $term)
                        ->orWhere('gereja_lokal', 'like', $term);
                });
            })
            ->when($this->filterGereja !== '', fn ($query) => $query->where('gereja_lokal', $this->filterGereja))
            ->when($this->filterGender !== '', fn ($query) => $query->where('gender', $this->filterGender))
            ->when($this->filterTeam === 'none', fn ($query) => $query->whereNull('team'))
            ->when($this->filterTeam !== '' && $this->filterTeam !== 'none', fn ($query) => $query->where('team', (int) $this->filterTeam));

        $query = match ($this->sortBy) {
            'nama_asc' => $query->orderBy('nama'),
            'nama_desc' => $query->orderByDesc('nama'),
            'umur_asc' => $query->orderBy('umur')->orderBy('nama'),
            'umur_desc' => $query->orderByDesc('umur')->orderBy('nama'),
            'gereja_asc' => $query->orderBy('gereja_lokal')->orderBy('nama'),
            'gender_asc' => $query->orderBy('gender')->orderBy('nama'),
            default => $query->latest(),
        };

        return $query
            ->get()
            ->map(function (FellowRegistration $item) {
                return [
                    'id' => $item->id,
                    'nama' => $item->nama,
                    'gender' => $item->gender,
                    'umur' => $item->umur,
                    'whatsapp' => $item->whatsapp,
                    'whatsapp_link' => $this->whatsappLink($item->whatsapp),
                    'gereja_lokal' => $item->gereja_lokal,
                    'gereja_label' => GerejaOptionService::label($item->gereja_lokal),
                    'team' => $item->team,
                    'team_label' => $item->teamLabel(),
                    'bukti_url' => $item->hasBuktiTf() ? route('admin.bukti-tf', $item) : null,
                    'bukti_is_pdf' => str($item->bukti_tf_path ?? '')->lower()->endsWith('.pdf'),
                    'created_at' => $item->created_at?->format('d M Y H:i'),
                ];
            });
    }

    /**
     * @return array{total: int, male: int, female: int, churches: Collection<int, array{name: string, count: int}>}
     */
    public function stats(): array
    {
        $all = FellowRegistration::query()->get(['gender', 'gereja_lokal']);

        $churches = $all
            ->groupBy('gereja_lokal')
            ->map(fn (Collection $group, string $name) => [
                'name' => $name,
                'label' => GerejaOptionService::label($name),
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->values();

        return [
            'total' => $all->count(),
            'male' => $all->where('gender', 'Laki-laki')->count(),
            'female' => $all->where('gender', 'Perempuan')->count(),
            'churches' => $churches,
        ];
    }

    public function with(TeamAssignmentService $teams): array
    {
        GerejaOptionService::ensureSeeded();

        $gerejaOptions = array_merge(
            [['id' => '', 'name' => 'Semua gereja']],
            GerejaOptionService::selectOptions(),
        );

        $genderOptions = [
            ['id' => '', 'name' => 'Semua gender'],
            ['id' => 'Laki-laki', 'name' => 'Pria'],
            ['id' => 'Perempuan', 'name' => 'Wanita'],
        ];

        $sortOptions = [
            ['id' => 'terbaru', 'name' => 'Terbaru'],
            ['id' => 'nama_asc', 'name' => 'Nama A–Z'],
            ['id' => 'nama_desc', 'name' => 'Nama Z–A'],
            ['id' => 'umur_asc', 'name' => 'Umur termuda'],
            ['id' => 'umur_desc', 'name' => 'Umur tertua'],
            ['id' => 'gereja_asc', 'name' => 'Gereja A–Z'],
            ['id' => 'gender_asc', 'name' => 'Gender'],
        ];

        $teamFilterOptions = array_merge(
            [['id' => '', 'name' => 'Semua tim'], ['id' => 'none', 'name' => 'Belum ada tim']],
            array_values(array_filter(
                $teams->teamOptions(),
                fn (array $option) => $option['id'] !== '',
            )),
        );

        return [
            'rows' => $this->registrations(),
            'stats' => $this->stats(),
            'teamOptions' => $teams->teamOptions(),
            'gerejaOptions' => $gerejaOptions,
            'genderOptions' => $genderOptions,
            'sortOptions' => $sortOptions,
            'teamFilterOptions' => $teamFilterOptions,
        ];
    }
}; ?>

<div>
    <x-header title="Data Pendaftar Limitless" subtitle="Semua isian form dari landing page." separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Cari nama / WA / gereja..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Kelola Tim" icon="o-user-group" class="btn-primary btn-sm" link="{{ route('admin.teams') }}" />
            <x-button label="Logout" icon="o-power" class="btn-ghost" wire:click="logout" />
        </x-slot:actions>
    </x-header>

    <div class="space-y-6">
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
            <div class="rounded-2xl border border-base-300 bg-base-100 px-6 py-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-base-content/45">Total</p>
                <p class="mt-2 text-3xl font-bold tabular-nums">{{ $stats['total'] }}</p>
                <p class="mt-1 text-sm text-base-content/55">pendaftar</p>
            </div>

            <div class="rounded-2xl border border-base-300 bg-base-100 px-6 py-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-base-content/45">Gender</p>
                <div class="mt-3 flex items-center gap-5">
                    <span class="inline-flex items-center gap-2">
                        <x-gender-icon gender="Laki-laki" />
                        <strong class="text-lg tabular-nums text-blue-600">{{ $stats['male'] }}</strong>
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <x-gender-icon gender="Perempuan" />
                        <strong class="text-lg tabular-nums text-red-600">{{ $stats['female'] }}</strong>
                    </span>
                </div>
            </div>

            <div class="rounded-2xl border border-base-300 bg-base-100 px-6 py-5 shadow-sm sm:col-span-2 xl:col-span-1">
                <p class="text-xs font-semibold uppercase tracking-wide text-base-content/45">Gereja lokal</p>
                @if ($stats['churches']->isEmpty())
                    <p class="mt-3 text-sm text-base-content/50">Belum ada data.</p>
                @else
                    <div class="mt-3 flex flex-wrap gap-3">
                        @foreach ($stats['churches'] as $church)
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 rounded-xl border px-3.5 py-2.5 text-sm transition {{ $filterGereja === $church['name'] ? 'border-primary/50 bg-primary/10 font-semibold' : 'border-base-300 bg-base-200/60 hover:border-primary/30' }}"
                                wire:click="$set('filterGereja', '{{ $filterGereja === $church['name'] ? '' : $church['name'] }}')"
                            >
                                <span>{{ $church['label'] }}</span>
                                <span class="rounded-lg bg-base-100 px-2 py-0.5 text-xs font-bold tabular-nums">{{ $church['count'] }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="rounded-2xl border border-base-300 bg-base-100 p-5 shadow-sm sm:p-6">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end">
                <div class="grid flex-1 grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <label class="form-control w-full">
                        <span class="mb-2 text-xs font-medium text-base-content/60">Gereja</span>
                        <select class="select select-bordered w-full" wire:model.live="filterGereja">
                            @foreach ($gerejaOptions as $option)
                                <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="form-control w-full">
                        <span class="mb-2 text-xs font-medium text-base-content/60">Gender</span>
                        <select class="select select-bordered w-full" wire:model.live="filterGender">
                            @foreach ($genderOptions as $option)
                                <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="form-control w-full">
                        <span class="mb-2 text-xs font-medium text-base-content/60">Tim</span>
                        <select class="select select-bordered w-full" wire:model.live="filterTeam">
                            @foreach ($teamFilterOptions as $option)
                                <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="form-control w-full">
                        <span class="mb-2 text-xs font-medium text-base-content/60">Urutkan</span>
                        <select class="select select-bordered w-full" wire:model.live="sortBy">
                            @foreach ($sortOptions as $option)
                                <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <button
                    type="button"
                    class="btn btn-ghost shrink-0"
                    wire:click="resetFilters"
                >
                    <x-icon name="o-arrow-path" class="h-4 w-4" />
                    Reset
                </button>
            </div>
        </div>

        <x-card shadow class="!p-5 sm:!p-6">
            @if ($rows->isEmpty())
                <div class="py-14 text-center text-base-content/60">
                    Belum ada pendaftar{{ ($search || $filterGereja || $filterGender || $filterTeam) ? ' untuk filter ini' : '' }}.
                </div>
            @else
                <div class="mb-5 text-sm text-base-content/60">
                    Menampilkan <strong class="text-base-content">{{ $rows->count() }}</strong> pendaftar
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="w-10">#</th>
                                <th>Nama</th>
                                <th class="hidden md:table-cell">Gender</th>
                                <th>Umur</th>
                                <th>WhatsApp</th>
                                <th class="hidden sm:table-cell">Gereja</th>
                                <th>Tim</th>
                                <th>Bukti TF</th>
                                <th class="hidden lg:table-cell">Waktu</th>
                                <th class="w-16 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                <tr wire:key="reg-{{ $row['id'] }}" class="align-middle">
                                    <td class="py-4 tabular-nums text-base-content/60">{{ $loop->iteration }}</td>
                                    <td class="py-4 font-medium">{{ $row['nama'] }}</td>
                                    <td class="hidden py-4 md:table-cell">
                                        <x-gender-icon :gender="$row['gender']" />
                                    </td>
                                    <td class="py-4 tabular-nums">{{ $row['umur'] }}</td>
                                    <td class="whitespace-nowrap py-4">
                                        <a
                                            href="{{ $row['whatsapp_link'] }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1.5 text-sm font-semibold text-teal-700 hover:underline"
                                            title="Chat WhatsApp"
                                        >
                                            <x-icon name="o-chat-bubble-left-right" class="h-4 w-4" />
                                            {{ $row['whatsapp'] }}
                                        </a>
                                    </td>
                                    <td class="hidden py-4 sm:table-cell">{{ $row['gereja_label'] }}</td>
                                    <td class="min-w-36 py-4">
                                        <select
                                            class="select select-bordered select-sm w-full max-w-[9rem]"
                                            wire:change="assignTeam({{ $row['id'] }}, $event.target.value)"
                                        >
                                            @foreach ($teamOptions as $option)
                                                <option
                                                    value="{{ $option['id'] }}"
                                                    @selected((string) ($row['team'] ?? '') === (string) $option['id'])
                                                >
                                                    {{ $option['name'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="py-4">
                                        @if ($row['bukti_url'])
                                            <button
                                                type="button"
                                                class="btn btn-ghost btn-sm"
                                                wire:click="showBukti({{ $row['id'] }})"
                                            >
                                                Lihat
                                            </button>
                                        @else
                                            <span class="text-xs opacity-50">—</span>
                                        @endif
                                    </td>
                                    <td class="hidden whitespace-nowrap py-4 text-sm text-base-content/70 lg:table-cell">
                                        {{ $row['created_at'] }}
                                    </td>
                                    <td class="py-4 text-right">
                                        <x-button
                                            icon="o-trash"
                                            class="btn-ghost btn-sm text-error"
                                            wire:click="deleteRegistration({{ $row['id'] }})"
                                            wire:confirm="Hapus pendaftar {{ $row['nama'] }}?"
                                            spinner
                                            title="Hapus"
                                        />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>
    </div>

    @if ($previewUrl)
        <dialog class="modal modal-open">
            <div class="modal-box max-w-3xl">
                <h3 class="font-bold text-lg">Bukti TF — {{ $previewName }}</h3>
                <div class="mt-4">
                    @if ($previewIsPdf)
                        <iframe src="{{ $previewUrl }}" class="h-[70vh] w-full rounded-lg border"></iframe>
                    @else
                        <img src="{{ $previewUrl }}" alt="Bukti transfer" class="max-h-[70vh] w-full rounded-lg object-contain bg-base-200">
                    @endif
                </div>
                <div class="modal-action">
                    <a href="{{ $previewUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm">Buka tab baru</a>
                    <button type="button" class="btn btn-sm" wire:click="closePreview">Tutup</button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button type="button" wire:click="closePreview">close</button>
            </form>
        </dialog>
    @endif
</div>
