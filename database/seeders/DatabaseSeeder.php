<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin Limitless',
                'password' => Hash::make('admin123'),
            ],
        );

        Setting::setValue('bank_name', Setting::getValue('bank_name', 'BCA') ?? 'BCA');
        Setting::setValue('bank_account', Setting::getValue('bank_account', '1234567890') ?? '1234567890');
        Setting::setValue('bank_holder', Setting::getValue('bank_holder', 'Panitia Limitless') ?? 'Panitia Limitless');
        Setting::setValue('team_count', Setting::getValue('team_count', '4') ?? '4');
    }
}
