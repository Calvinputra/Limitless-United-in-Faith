<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use App\Services\GerejaOptionService;
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
        Setting::setValue('bank_account', Setting::getValue('bank_account', '4660260451') ?? '4660260451');
        Setting::setValue('bank_holder', Setting::getValue('bank_holder', 'Vera Lisiani Bong') ?? 'Vera Lisiani Bong');
        Setting::setValue('bank_remark', Setting::getValue('bank_remark', '') ?? '');
        Setting::setValue('transfer_amount', Setting::getValue('transfer_amount', '150000') ?? '150000');
        Setting::setValue('team_count', Setting::getValue('team_count', '4') ?? '4');

        GerejaOptionService::ensureSeeded();
    }
}
