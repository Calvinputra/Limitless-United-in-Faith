<?php

use App\Models\FellowRegistration;
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

    public ?string $previewUrl = null;

    public string $previewName = '';

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

        if (! $registration->bukti_tf_path || ! Storage::disk('public')->exists($registration->bukti_tf_path)) {
            $this->error('Bukti transfer tidak ditemukan.', position: 'toast-bottom');

            return;
        }

        $this->previewUrl = Storage::disk('public')->url($registration->bukti_tf_path);
        $this->previewName = $registration->nama;
    }

    public function closePreview(): void
    {
        $this->previewUrl = null;
        $this->previewName = '';
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

    public function registrations(): Collection
    {
        return FellowRegistration::query()
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';

                $query->where(function ($inner) use ($term) {
                    $inner->where('nama', 'like', $term)
                        ->orWhere('whatsapp', 'like', $term)
                        ->orWhere('gereja_lokal', 'like', $term);
                });
            })
            ->latest()
            ->get()
            ->map(function (FellowRegistration $item) {
                return [
                    'id' => $item->id,
                    'nama' => $item->nama,
                    'gender' => $item->gender,
                    'umur' => $item->umur,
                    'whatsapp' => $item->whatsapp,
                    'gereja_lokal' => $item->gereja_lokal,
                    'team' => $item->team,
                    'team_label' => $item->teamLabel(),
                    'bukti_url' => $item->bukti_tf_path ? Storage::disk('public')->url($item->bukti_tf_path) : null,
                    'created_at' => $item->created_at?->format('d M Y H:i'),
                ];
            });
    }

    public function with(TeamAssignmentService $teams): array
    {
        return [
            'rows' => $this->registrations(),
            'teamOptions' => $teams->teamOptions(),
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

    <x-card shadow>
        @if ($rows->isEmpty())
            <div class="py-10 text-center text-base-content/60">
                Belum ada pendaftar.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table table-sm">
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
                            <tr wire:key="reg-{{ $row['id'] }}">
                                <td class="tabular-nums text-base-content/60">{{ $loop->iteration }}</td>
                                <td class="font-medium">{{ $row['nama'] }}</td>
                                <td class="hidden md:table-cell">
                                    <x-gender-icon :gender="$row['gender']" />
                                </td>
                                <td>{{ $row['umur'] }}</td>
                                <td class="whitespace-nowrap">{{ $row['whatsapp'] }}</td>
                                <td class="hidden sm:table-cell">{{ $row['gereja_lokal'] }}</td>
                                <td class="min-w-36">
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
                                <td>
                                    @if ($row['bukti_url'])
                                        <button
                                            type="button"
                                            class="btn btn-ghost btn-xs"
                                            wire:click="showBukti({{ $row['id'] }})"
                                        >
                                            Lihat
                                        </button>
                                    @else
                                        <span class="text-xs opacity-50">—</span>
                                    @endif
                                </td>
                                <td class="hidden whitespace-nowrap lg:table-cell text-xs text-base-content/70">
                                    {{ $row['created_at'] }}
                                </td>
                                <td class="text-right">
                                    <x-button
                                        icon="o-trash"
                                        class="btn-ghost btn-xs text-error"
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

    @if ($previewUrl)
        <dialog class="modal modal-open">
            <div class="modal-box max-w-3xl">
                <h3 class="font-bold text-lg">Bukti TF — {{ $previewName }}</h3>
                <div class="mt-4">
                    @if (str($previewUrl)->contains(['.pdf']))
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
