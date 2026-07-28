<?php

use App\Models\FellowRegistration;
use App\Services\GerejaOptionService;
use App\Services\TeamAssignmentService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new
#[Layout('layouts.app')]
#[Title('Manajemen Tim')]
class extends Component {
    use Toast;

    public int $teamCount = 4;

    public string $search = '';

    public function mount(TeamAssignmentService $teams): void
    {
        if (! Auth::check()) {
            $this->redirect(route('login'), navigate: true);

            return;
        }

        $this->teamCount = $teams->teamCount();
    }

    public function saveTeamCount(TeamAssignmentService $teams): void
    {
        $this->validate([
            'teamCount' => 'required|integer|min:1|max:20',
        ]);

        $teams->setTeamCount($this->teamCount);
        $this->teamCount = $teams->teamCount();

        // Reset assignment that exceeds new count
        FellowRegistration::query()
            ->where('team', '>', $this->teamCount)
            ->update(['team' => null]);

        $this->success("Jumlah tim diset ke {$this->teamCount}.", position: 'toast-bottom');
    }

    public function randomize(TeamAssignmentService $teams): void
    {
        $count = FellowRegistration::query()->count();

        if ($count === 0) {
            $this->error('Belum ada pendaftar untuk di-random.', position: 'toast-bottom');

            return;
        }

        $assigned = $teams->randomize();
        $this->success("Berhasil random {$assigned} orang ke {$this->teamCount} tim.", position: 'toast-bottom');
    }

    public function clearTeams(TeamAssignmentService $teams): void
    {
        $cleared = $teams->clearAll();
        $this->warning("Tim dikosongkan untuk {$cleared} orang.", position: 'toast-bottom');
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

    /**
     * @return list<array{key: string, label: string, class?: string}>
     */
    public function headers(): array
    {
        return [
            ['key' => 'nama', 'label' => 'Nama'],
            ['key' => 'gereja_lokal', 'label' => 'Gereja', 'class' => 'hidden sm:table-cell'],
            ['key' => 'gender', 'label' => 'Gender', 'class' => 'hidden md:table-cell'],
            ['key' => 'team_label', 'label' => 'Tim saat ini', 'class' => 'w-28'],
        ];
    }

    /**
     * @return Collection<int, array{team: int, count: int, members: Collection<int, FellowRegistration>}>
     */
    public function teamSummaries(): Collection
    {
        $registrations = FellowRegistration::query()->orderBy('nama')->get();

        return collect(range(1, $this->teamCount))->map(function (int $team) use ($registrations) {
            $members = $registrations->where('team', $team)->values();

            return [
                'team' => $team,
                'count' => $members->count(),
                'members' => $members,
            ];
        });
    }

    public function unassigned(): Collection
    {
        return FellowRegistration::query()
            ->whereNull('team')
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';
                $query->where(function ($inner) use ($term) {
                    $inner->where('nama', 'like', $term)
                        ->orWhere('gereja_lokal', 'like', $term);
                });
            })
            ->orderBy('nama')
            ->get();
    }

    public function allMembers(): Collection
    {
        return FellowRegistration::query()
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';
                $query->where(function ($inner) use ($term) {
                    $inner->where('nama', 'like', $term)
                        ->orWhere('gereja_lokal', 'like', $term)
                        ->orWhere('whatsapp', 'like', $term);
                });
            })
            ->orderBy('nama')
            ->get()
            ->map(fn (FellowRegistration $item) => [
                'id' => $item->id,
                'nama' => $item->nama,
                'gereja_lokal' => $item->gereja_lokal,
                'gereja_label' => GerejaOptionService::label($item->gereja_lokal),
                'gender' => $item->gender,
                'umur' => $item->umur,
                'team' => $item->team,
                'team_label' => $item->teamLabel(),
            ]);
    }

    public function with(TeamAssignmentService $teams): array
    {
        return [
            'headers' => $this->headers(),
            'rows' => $this->allMembers(),
            'summaries' => $this->teamSummaries(),
            'unassignedCount' => FellowRegistration::query()->whereNull('team')->count(),
            'teamOptions' => $teams->teamOptions(),
            'total' => FellowRegistration::query()->count(),
        ];
    }
}; ?>

<div>
    <x-header title="Manajemen Tim" subtitle="Atur jumlah tim, randomize, atau set manual per orang." separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Cari nama / gereja..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
    </x-header>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-6">
        <x-card title="Pengaturan" subtitle="Jumlah tim aktif" class="xl:col-span-1" shadow>
            <div class="flex flex-col sm:flex-row gap-3 items-end">
                <div class="flex-1 w-full">
                    <x-input
                        label="Berapa tim?"
                        type="number"
                        wire:model="teamCount"
                        min="1"
                        max="20"
                        icon="o-user-group"
                    />
                </div>
                <x-button label="Simpan" class="btn-primary" icon="o-check" wire:click="saveTeamCount" spinner="saveTeamCount" />
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                <div class="rounded-xl bg-base-200 p-3">
                    <p class="opacity-60">Total pendaftar</p>
                    <p class="text-2xl font-bold">{{ $total }}</p>
                </div>
                <div class="rounded-xl bg-base-200 p-3">
                    <p class="opacity-60">Belum punya tim</p>
                    <p class="text-2xl font-bold">{{ $unassignedCount }}</p>
                </div>
            </div>

            <div class="mt-4 flex flex-col sm:flex-row gap-2">
                <x-button
                    label="Randomize semua"
                    class="btn-secondary flex-1"
                    icon="o-arrow-path"
                    wire:click="randomize"
                    wire:confirm="Random ulang semua pendaftar ke {{ $teamCount }} tim?"
                    spinner="randomize"
                />
                <x-button
                    label="Kosongkan tim"
                    class="btn-ghost flex-1"
                    icon="o-x-mark"
                    wire:click="clearTeams"
                    wire:confirm="Yakin kosongkan semua tim?"
                    spinner="clearTeams"
                />
            </div>
        </x-card>

        <x-card title="Ringkasan tim" subtitle="Jumlah anggota per tim" class="xl:col-span-2" shadow>
            <div @class([
                'grid gap-3',
                'grid-cols-2',
                'md:grid-cols-2' => $teamCount <= 2,
                'md:grid-cols-3' => $teamCount === 3,
                'md:grid-cols-4' => $teamCount >= 4,
            ])>
                @foreach ($summaries as $summary)
                    <div class="rounded-xl border border-base-300 bg-base-100 p-3">
                        <p class="font-semibold text-primary">Tim {{ $summary['team'] }}</p>
                        <p class="text-2xl font-bold mt-1">{{ $summary['count'] }}</p>
                        <p class="text-xs opacity-60 mt-1">anggota</p>
                    </div>
                @endforeach
            </div>
        </x-card>
    </div>

    <x-card title="Set manual" subtitle="Pilih tim per orang kapan saja." shadow>
        @if ($rows->isEmpty())
            <div class="py-10 text-center text-base-content/60">
                Belum ada pendaftar.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th class="hidden sm:table-cell">Gereja</th>
                            <th>Gender</th>
                            <th>Umur</th>
                            <th>Tim</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr wire:key="member-{{ $row['id'] }}">
                                <td class="font-medium">{{ $row['nama'] }}</td>
                                <td class="hidden sm:table-cell">{{ $row['gereja_label'] }}</td>
                                <td>
                                    <x-gender-icon :gender="$row['gender']" />
                                </td>
                                <td class="tabular-nums">{{ $row['umur'] }}</td>
                                <td class="min-w-44">
                                    <select
                                        class="select select-bordered select-sm w-full max-w-xs"
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
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>

    <div @class([
        'mt-6 grid grid-cols-1 gap-4',
        'md:grid-cols-2',
        'xl:grid-cols-2' => $teamCount <= 2,
        'xl:grid-cols-3' => $teamCount >= 3,
    ])>
        @foreach ($summaries as $summary)
            <x-card title="Tim {{ $summary['team'] }}" subtitle="{{ $summary['count'] }} anggota" shadow>
                @if ($summary['members']->isEmpty())
                    <p class="text-sm opacity-60">Belum ada anggota.</p>
                @else
                    <ul class="space-y-2">
                        @foreach ($summary['members'] as $member)
                            <li class="flex items-center justify-between gap-2 rounded-lg bg-base-200 px-3 py-2">
                                <div>
                                    <p class="font-medium leading-tight">{{ $member->nama }}</p>
                                    <p class="text-xs opacity-60">{{ \App\Services\GerejaOptionService::label($member->gereja_lokal) }} · {{ $member->umur }} th</p>
                                </div>
                                <x-gender-icon :gender="$member->gender" size="sm" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>
        @endforeach
    </div>
</div>
