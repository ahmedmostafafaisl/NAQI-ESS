<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'my_team_attendance',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enables the "My Team" attendance feature.',
                'is_public' => true,
            ],
            [
                'key' => 'my_teamwork',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enables the "My Teamwork" feature.',
                'is_public' => true,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
