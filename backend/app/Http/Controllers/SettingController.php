<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{

    public function index()
    {
        $settings = Cache::remember('site_settings', 3600, function () {
            return Setting::where('is_editable', true)
                ->whereNotNull('group')
                ->get()
                ->groupBy('group');
        });

        return response()->json($settings);
    }

    public function update(Request $request)
    {
        $items = $request->all();

        if (!is_array($items)) {
            Log::error('Invalid settings data', ['data' => $items]);
            return response()->json(['error' => 'Dữ liệu phải là một mảng'], 400);
        }

        foreach ($items as $item) {
            if (!isset($item['key']) || !array_key_exists('value', $item)) {
                Log::warning('Skipping invalid setting item', ['item' => $item]);
                continue;
            }

            $setting = Setting::where('key', $item['key'])->first();
            if (!$setting) {
                Log::warning('Setting not found', ['key' => $item['key']]);
                continue;
            }

            // Validate file paths for file-type settings
            if ($setting->type === 'file' && $item['value'] && !Storage::disk('r2')->exists($item['value'])) {
                Log::warning('File not found in R2', ['key' => $item['key'], 'value' => $item['value']]);
                continue;
            }

            $setting->update(['value' => $item['value']]);
        }

        Cache::forget('site_settings');

        return response()->json(['message' => 'Settings updated successfully']);
    }


    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'key'  => 'required|string',
        ]);

        try {
            $file = $request->file('file');
            $filename = 'settings/' . time() . '_' . $file->getClientOriginalName();

            // Store file in R2
            $path = Storage::disk('r2')->put($filename, file_get_contents($file->getRealPath()), 'public');

            if ($path) {
                $setting = Setting::where('key', $request->key)->first();
                if ($setting) {
                    // Delete old file from R2 if it exists
                    if ($setting->value && Storage::disk('r2')->exists($setting->value)) {
                        Storage::disk('r2')->delete($setting->value);
                    }
                    $setting->value = $filename;
                    $setting->save();
                } else {
                    // Create new setting if it doesn't exist
                    Setting::create([
                        'key' => $request->key,
                        'value' => $filename,
                        'type' => 'file',
                        'group' => 'General', // Adjust as needed
                        'is_editable' => true,
                    ]);
                }

                Cache::forget('site_settings');

                return response()->json([
                    'success' => true,
                    'path' => $filename,
                    'url' => Storage::disk('r2')->url($filename),
                ]);
            }

            Log::error('Failed to upload file to R2', ['filename' => $filename]);
            return response()->json(['error' => 'Không thể upload file'], 500);
        } catch (\Exception $e) {
            Log::error('Upload error:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Lỗi server: ' . $e->getMessage()], 500);
        }
    }


    public function backup()
    {
        $settings = Setting::all();
        return response()->json($settings);
    }

    public function restore(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:json',
        ]);

        try {
            $data = json_decode(file_get_contents($request->file('file')->getRealPath()), true);

            if (!is_array($data)) {
                Log::error('Invalid restore data', ['data' => $data]);
                return response()->json(['error' => 'Dữ liệu khôi phục không hợp lệ'], 400);
            }

            foreach ($data as $item) {
                if (!isset($item['key'])) {
                    Log::warning('Skipping invalid restore item', ['item' => $item]);
                    continue;
                }

                // Validate file paths for file-type settings
                if (isset($item['type']) && $item['type'] === 'file' && $item['value'] && !Storage::disk('r2')->exists($item['value'])) {
                    Log::warning('File not found in R2 during restore', ['key' => $item['key'], 'value' => $item['value']]);
                    continue;
                }

                Setting::updateOrCreate(['key' => $item['key']], $item);
            }

            Cache::forget('site_settings');

            return response()->json(['message' => 'Đã phục hồi cài đặt']);
        } catch (\Exception $e) {
            Log::error('Restore error:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Lỗi server: ' . $e->getMessage()], 500);
        }
    }
}
