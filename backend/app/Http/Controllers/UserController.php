<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return UserResource::collection($users);
    }

    public function batchDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        User::whereIn('id', $ids)->delete();
        return response()->json(['success' => true]);
    }

    public function batchAddRole(Request $request)
    {
        $ids = $request->input('ids', []);
        $role = $request->input('role');
        User::whereIn('id', $ids)->update(['role' => $role]);
        return response()->json(['success' => true]);
    }

    public function batchRemoveRole(Request $request)
    {
        $ids = $request->input('ids', []);
        $role = $request->input('role');
        User::whereIn('id', $ids)->where('role', $role)->update(['role' => null]);
        return response()->json(['success' => true]);
    }

public function store(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'unique:users,email',
                'regex:/^[a-zA-Z0-9._%+-]+@((gmail\.com)|(edu\.vn)|(yahoo\.com))$/'
            ],
            'password' => [
                'required',
                'string',
                'min:6',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/'
            ],
            'phone' => [
                'nullable',
                'string',
                'max:11',
                'unique:users,phone',
                'regex:/^(\+84|0)(3|5|7|8|9)[0-9]{8}$/'
            ],
            'role' => 'nullable|string',
            'status' => 'nullable|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ], [
            'name.required' => 'Tên không được để trống.',
            'name.max' => 'Tên không được vượt quá 255 ký tự.',
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email đã tồn tại.',
            'email.regex' => 'Email phải thuộc các domain gmail.com, edu.vn hoặc yahoo.com.',
            'password.required' => 'Mật khẩu không được để trống.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.regex' => 'Mật khẩu phải chứa ít nhất 1 chữ hoa, 1 chữ thường, 1 số và 1 ký tự đặc biệt.',
            'phone.unique' => 'Số điện thoại đã tồn tại.',
            'phone.max' => 'Số điện thoại không được vượt quá 11 ký tự.',
            'phone.regex' => 'Số điện thoại phải là số điện thoại hợp lệ (bắt đầu bằng +84 hoặc 0).',
            'avatar.image' => 'Ảnh đại diện phải là file ảnh.',
            'avatar.mimes' => 'Ảnh phải có định dạng jpeg, png, jpg, gif, svg hoặc webp.',
            'avatar.max' => 'Ảnh đại diện không được vượt quá 2MB.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['password'] = Hash::make($data['password']);

        $avatarPath = null;

        // Chỉ upload nếu thực sự có file, còn không thì bỏ qua (cho phép để trống)
        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            $file = $request->file('avatar');
            $filename = 'avatars/' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

            logger()->info('Attempting to upload file to R2', [
                'filename' => $filename,
                'file_size' => $file->getSize(),
                'file_type' => $file->getMimeType(),
            ]);

            try {
                $uploadResult = Storage::disk('r2')->put($filename, file_get_contents($file));
                logger()->info('R2 upload result', ['success' => $uploadResult, 'filename' => $filename]);

                if ($uploadResult) {
                    $avatarPath = $filename;
                    $data['avatar'] = $avatarPath;
                } else {
                    logger()->error('Failed to upload file to R2', ['filename' => $filename]);
                    throw new \Exception('Không thể upload file lên R2: Upload result false');
                }
            } catch (\Aws\S3\Exception\S3Exception $e) {
                logger()->error('S3 Exception during R2 upload', [
                    'error' => $e->getMessage(),
                    'aws_error' => $e->getAwsErrorCode(),
                    'aws_request_id' => $e->getAwsRequestId(),
                    'filename' => $filename,
                ]);
                throw new \Exception('Lỗi R2: ' . $e->getMessage());
            }
        } else {
            // Không có file, remove avatar field nếu có
            unset($data['avatar']);
        }

        $user = User::create($data);

        // Gắn thêm URL ảnh (nếu có, còn không là null)
        $user->avatar_url = $avatarPath ? Storage::disk('r2')->url($avatarPath) : null;

        logger()->info('User created successfully', ['user_id' => $user->id, 'avatar_path' => $avatarPath]);

        return response()->json([
            'success' => true,
            'message' => 'Tạo người dùng thành công.',
            'data' => new UserResource($user),
        ], 201);

    } catch (\Exception $e) {
        logger()->error('Error in UserController::store', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
}


    public function show(User $user)
    {
        return new UserResource($user);
    }

public function update(Request $request, User $user)
{
    try {

        $validator = Validator::make($request->all(), [
            'name'     => 'sometimes|required|string|max:255',
            'password' => [
                'sometimes',
                'required',
                'string',
                'min:6',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/'
            ],
            'old_password' => 'required_with:password|string',
            'avatar'  => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'role'    => 'sometimes|required|in:user,seller,admin',
            'status'  => 'sometimes|required|in:active,inactive,banned',
        ], [
            'password.regex' => 'Mật khẩu phải chứa ít nhất 1 chữ hoa, 1 chữ thường, 1 số và 1 ký tự đặc biệt.',
            'name.required' => 'Tên không được để trống.',
            'name.max' => 'Tên không được vượt quá 255 ký tự.',
            'avatar.image' => 'Ảnh đại diện phải là file ảnh.',
            'avatar.mimes' => 'Ảnh đại diện phải có định dạng jpeg, png, jpg, gif, svg hoặc webp.',
            'avatar.max' => 'Ảnh đại diện không được vượt quá 2MB.',
            'role.required' => 'Vai trò không được để trống.',
            'role.in' => 'Vai trò không hợp lệ.',
            'status.required' => 'Trạng thái không được để trống.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'old_password.required_with' => 'Vui lòng nhập mật khẩu cũ để đổi mật khẩu.'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // Xử lý đổi mật khẩu, bắt buộc phải có old_password check đúng
        if (isset($data['password'])) {
            if (!isset($data['old_password']) || !Hash::check($data['old_password'], $user->password)) {
                return response()->json(['error' => 'Mật khẩu cũ không đúng.'], 403);
            }
            $data['password'] = Hash::make($data['password']);
        }
        unset($data['old_password']);

        // Xử lý upload và resize avatar (nếu có)
        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            // Xóa ảnh cũ nếu có
            if ($user->avatar) {
                Storage::disk('r2')->delete($user->avatar);
            }
            $file = $request->file('avatar');
            $filename = 'avatars/' . $user->id . '_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();

            // Resize ảnh trước khi upload lên R2
            $img = Image::make($file)
                ->resize(512, 512, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->encode($file->getClientOriginalExtension());

            $uploadResult = Storage::disk('r2')->put($filename, (string) $img);

            Log::info('R2 upload result in update', [
                'success' => $uploadResult,
                'filename' => $filename,
            ]);

            if ($uploadResult) {
                $data['avatar'] = $filename;
            } else {
                Log::error('Failed to upload file to R2 in update', ['filename' => $filename]);
                throw new \Exception('Không thể upload file lên R2.');
            }
        } else {
            unset($data['avatar']);
        }

        $user->update($data);

        Log::info('User updated successfully', [
            'user_id'    => $user->id,
            'avatar_path'=> $user->avatar,
        ]);

        return new UserResource($user);

    } catch (\Exception $e) {
        // Log lỗi đơn giản, không log trace ở prod
        Log::error('Error in UserController::update', [
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'error' => 'Có lỗi xảy ra: ' . $e->getMessage(),
        ], 500);
    }
}


    public function destroy(User $user)
    {
        try {
            if ($user->avatar) {
                Storage::disk('r2')->delete($user->avatar);
            }
            $user->delete();

            Log::info('User deleted successfully', ['user_id' => $user->id]);

            return response()->json(null, 204);

        } catch (\Exception $e) {
            Log::error('Error in UserController::destroy', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }
}
