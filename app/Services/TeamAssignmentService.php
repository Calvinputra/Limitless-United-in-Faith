<?php

namespace App\Services;

use App\Models\FellowRegistration;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class TeamAssignmentService
{
    public function teamCount(): int
    {
        return Setting::teamCount();
    }

    public function setTeamCount(int $count): void
    {
        Setting::setValue('team_count', (string) max(1, min(20, $count)));
    }

    /**
     * @return list<array{id: int|string, name: string}>
     */
    public function teamOptions(bool $includeEmpty = true): array
    {
        $options = [];

        if ($includeEmpty) {
            $options[] = ['id' => '', 'name' => 'Belum ada tim'];
        }

        for ($i = 1; $i <= $this->teamCount(); $i++) {
            $options[] = ['id' => (string) $i, 'name' => 'Tim '.$i];
        }

        return $options;
    }

    public function assignManual(int $registrationId, ?int $team): void
    {
        if ($team !== null && ($team < 1 || $team > $this->teamCount())) {
            throw new \InvalidArgumentException('Nomor tim tidak valid.');
        }

        FellowRegistration::query()
            ->whereKey($registrationId)
            ->update(['team' => $team]);
    }

    public function randomize(): int
    {
        $teamCount = $this->teamCount();
        $registrations = FellowRegistration::query()->orderBy('id')->get();

        if ($registrations->isEmpty()) {
            return 0;
        }

        $shuffled = $registrations->shuffle()->values();

        DB::transaction(function () use ($shuffled, $teamCount) {
            foreach ($shuffled as $index => $registration) {
                $team = ($index % $teamCount) + 1;
                $registration->update(['team' => $team]);
            }
        });

        return $shuffled->count();
    }

    public function clearAll(): int
    {
        return FellowRegistration::query()->whereNotNull('team')->update(['team' => null]);
    }
}
