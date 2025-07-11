<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'site_logo',
                'value' => 'settings/logo.png',
                'type' => 'file',
                'group' => 'general',
                'description' => 'Logo chính của website',
                'is_editable' => true,
            ],
            [
                'key' => 'contact_email',
                'value' => 'support@example.com',
                'type' => 'text',
                'group' => 'contact',
                'description' => 'Email liên hệ hỗ trợ',
                'is_editable' => true,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate([
                'key' => $setting['key']
            ], $setting);
        }
    }
}