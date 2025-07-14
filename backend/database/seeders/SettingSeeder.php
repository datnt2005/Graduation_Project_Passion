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
            // cài đặt tên website
            [
                'key' => 'site_name',
                'value' => 'My Website',
                'type' => 'text',
                'group' => 'general',
                'description' => 'Tên website',
                'is_editable' => true,
            ],
            // Số điện thoại
            [
                'key' => 'phone_number',
                'value' => '0123456789',
                'type' => 'text',
                'group' => 'contact',
                'description' => 'Số điện thoại',
                'is_editable' => true,
            ],
            // số điện thoại nóng Hotline
            [
                'key' => 'hotline_number',
                'value' => '0123456789',
                'type' => 'text',
                'group' => 'contact',
                'description' => 'Số Hotline',
                'is_editable' => true,
            ]

        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate([
                'key' => $setting['key']
            ], $setting);
        }
    }
}
