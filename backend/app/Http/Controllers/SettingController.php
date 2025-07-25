<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    /**
     * Lấy danh sách cài đặt theo nhóm.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $settings = Cache::remember('site_settings', 3600, function () {
                $settings = Setting::where('is_editable', true)
                    ->whereNotNull('group')
                    ->get();

                if ($settings->isEmpty()) {
                    return new \stdClass(); // Trả về object rỗng
                }

                return $settings->groupBy('group');
            });

            $response = is_array($settings) ? (object) $settings : $settings;
            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Error fetching settings:', [
                'error' => $e->getMessage(),
                'time' => now()->format('Y-m-d H:i:s')
            ]);
            return response()->json([
                'message' => 'Lỗi khi lấy dữ liệu cài đặt: ' . $e->getMessage(),
                'timestamp' => now()->format('Y-m-d H:i:s')
            ], 500);
        }
    }

    /**
     * Cập nhật các cài đặt.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
public function update(Request $request)
{
    try {
        $items = $request->all();
        Log::info('Received settings data:', ['data' => $items]);

        if (!is_array($items)) {
            Log::error('Invalid settings data:', ['data' => $items]);
            return response()->json(['error' => 'Dữ liệu phải là một mảng'], 400);
        }

        foreach ($items as $item) {
            Log::debug('Processing item:', ['item' => $item]);
            if (!isset($item['key']) || !array_key_exists('value', $item)) {
                Log::warning('Skipping invalid setting item:', ['item' => $item]);
                continue;
            }

            $setting = Setting::where('key', $item['key'])->first();
            if (!$setting) {
                Log::warning('Setting not found:', ['key' => $item['key']]);
                continue;
            }

            if ($setting->type === 'file' && $item['value'] && !Storage::disk('r2')->exists($item['value'])) {
                Log::warning('File not found in R2:', ['key' => $item['key'], 'value' => $item['value']]);
                continue;
            }

            $setting->update(['value' => $item['value']]);
            Log::info('Updated setting:', ['key' => $item['key'], 'value' => $item['value']]);
        }

        Cache::forget('site_settings');
        return response()->json(['message' => 'Settings updated successfully', 'timestamp' => now()->format('Y-m-d H:i:s')]);
    } catch (\Exception $e) {
        Log::error('Error updating settings:', ['error' => $e->getMessage(), 'time' => now()->format('Y-m-d H:i:s')]);
        return response()->json(['error' => 'Lỗi khi cập nhật: ' . $e->getMessage(), 'timestamp' => now()->format('Y-m-d H:i:s')], 500);
    }
}
    /**
     * Tải lên file cho cài đặt.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
                'key' => 'required|string',
            ]);

            $file = $request->file('file');
            $filename = 'settings/' . time() . '_' . $file->getClientOriginalName();

            $path = Storage::disk('r2')->put($filename, file_get_contents($file->getRealPath()), 'public');

            if ($path) {
                $setting = Setting::where('key', $request->key)->first();
                if ($setting) {
                    if ($setting->value && Storage::disk('r2')->exists($setting->value)) {
                        Storage::disk('r2')->delete($setting->value);
                    }
                    $setting->value = $filename;
                    $setting->save();
                } else {
                    Setting::create([
                        'key' => $request->key,
                        'value' => $filename,
                        'type' => 'file',
                        'group' => $request->input('group', 'General'),
                        'is_editable' => true,
                        'description' => $request->input('description', ''),
                    ]);
                }

                Cache::forget('site_settings');
                return response()->json([
                    'success' => true,
                    'path' => $filename,
                    'url' => Storage::disk('r2')->url($filename),
                    'timestamp' => now()->format('Y-m-d H:i:s')
                ]);
            }

            Log::error('Failed to upload file to R2:', ['filename' => $filename, 'time' => now()->format('Y-m-d H:i:s')]);
            return response()->json(['error' => 'Không thể upload file', 'timestamp' => now()->format('Y-m-d H:i:s')], 500);
        } catch (\Exception $e) {
            Log::error('Upload error:', ['error' => $e->getMessage(), 'request' => $request->all(), 'time' => now()->format('Y-m-d H:i:s')]);
            return response()->json(['error' => 'Lỗi server: ' . $e->getMessage(), 'timestamp' => now()->format('Y-m-d H:i:s')], 500);
        }
    }

    /**
     * Tạo bản sao lưu cài đặt.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function backup()
    {
        try {
            $settings = Setting::all();
            if ($settings->isEmpty()) {
                return response()->json(new \stdClass(), 200);
            }
            return response()->json($settings);
        } catch (\Exception $e) {
            Log::error('Error creating backup:', ['error' => $e->getMessage(), 'time' => now()->format('Y-m-d H:i:s')]);
            return response()->json(['error' => 'Lỗi khi sao lưu: ' . $e->getMessage(), 'timestamp' => now()->format('Y-m-d H:i:s')], 500);
        }
    }

    /**
     * Phục hồi cài đặt từ file.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:json',
            ]);

            $data = json_decode(file_get_contents($request->file('file')->getRealPath()), true);

            if (!is_array($data)) {
                Log::error('Invalid restore data:', ['data' => $data, 'time' => now()->format('Y-m-d H:i:s')]);
                return response()->json(['error' => 'Dữ liệu khôi phục không hợp lệ', 'timestamp' => now()->format('Y-m-d H:i:s')], 400);
            }

            foreach ($data as $item) {
                if (!isset($item['key'])) {
                    Log::warning('Skipping invalid restore item:', ['item' => $item, 'time' => now()->format('Y-m-d H:i:s')]);
                    continue;
                }

                if (isset($item['type']) && $item['type'] === 'file' && $item['value'] && !Storage::disk('r2')->exists($item['value'])) {
                    Log::warning('File not found in R2 during restore:', ['key' => $item['key'], 'value' => $item['value'], 'time' => now()->format('Y-m-d H:i:s')]);
                    $item['value'] = null;
                }

                Setting::updateOrCreate(
                    ['key' => $item['key']],
                    array_merge($item, ['is_editable' => $item['is_editable'] ?? true])
                );
            }

            Cache::forget('site_settings');
            return response()->json(['message' => 'Đã phục hồi cài đặt', 'timestamp' => now()->format('Y-m-d H:i:s')]);
        } catch (\Exception $e) {
            Log::error('Restore error:', ['error' => $e->getMessage(), 'request' => $request->all(), 'time' => now()->format('Y-m-d H:i:s')]);
            return response()->json(['error' => 'Lỗi server: ' . $e->getMessage(), 'timestamp' => now()->format('Y-m-d H:i:s')], 500);
        }
    }

    /**
     * Xóa một cài đặt cụ thể.
     *
     * @param string $key
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($key)
    {
        try {
            $setting = Setting::where('key', $key)->first();
            if (!$setting) {
                Log::warning('Setting not found for deletion:', ['key' => $key, 'time' => now()->format('Y-m-d H:i:s')]);
                return response()->json(['error' => 'Cài đặt không tồn tại', 'timestamp' => now()->format('Y-m-d H:i:s')], 404);
            }

            if ($setting->type === 'file' && $setting->value && Storage::disk('r2')->exists($setting->value)) {
                Storage::disk('r2')->delete($setting->value);
            }

            $setting->delete();
            Cache::forget('site_settings');
            return response()->json(['message' => 'Cài đặt đã được xóa', 'timestamp' => now()->format('Y-m-d H:i:s')]);
        } catch (\Exception $e) {
            Log::error('Error deleting setting:', ['error' => $e->getMessage(), 'key' => $key, 'time' => now()->format('Y-m-d H:i:s')]);
            return response()->json(['error' => 'Lỗi khi xóa: ' . $e->getMessage(), 'timestamp' => now()->format('Y-m-d H:i:s')], 500);
        }
    }
}