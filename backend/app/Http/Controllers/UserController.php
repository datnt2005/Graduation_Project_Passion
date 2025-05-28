<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return UserResource::collection($users);
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
                'avatar' => 'nullable|file|image|max:2048',
            ], [
                'name.required' => 'Tên không được để trống.',
                'name.max' => 'Tên không được vượt quá 255 ký tự.',
                'email.required' => 'Email không được để trống.',
                'email.email' => 'Email không đúng định dạng.',
                'email.unique' => 'Email đã tồn tại.',
                'email.regex' => 'Email phải thuộc các domain gmail.com, hoặc yahoo.com.',
                'password.required' => 'Mật khẩu không được để trống.',
                'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
                'password.regex' => 'Mật khẩu phải chứa ít nhất 1 chữ hoa, 1 chữ thường, 1 số và 1 ký tự đặc biệt.',
                'phone.unique' => 'Số điện thoại đã tồn tại.',
                'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự.',
                'phone.regex' => 'Số điện thoại phải là số điện thoại hợp lệ (bắt đầu bằng +84 hoặc 0).',
                'avatar.image' => 'Ảnh đại diện phải là file ảnh.',
                'avatar.max' => 'Ảnh đại diện không được vượt quá 2MB.',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $validator->validated();

            $data['password'] = Hash::make($data['password']);

            if ($request->hasFile('avatar')) {
                $path = $request->file('avatar')->store('avatars', 's3');
                $data['avatar'] = "/passion/{$path}";
            }

            $user = User::create($data);

            return new UserResource($user);

        } catch (\Exception $e) {
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
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'password' => [
                'sometimes',
                'required',
                'string',
                'min:6',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/'
            ],
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'role' => 'sometimes|required|in:user,seller,admin',
            'status' => 'sometimes|required|in:active,inactive,banned',
        ], [
            'password.regex' => 'Mật khẩu phải chứa ít nhất 1 chữ hoa, 1 chữ thường, 1 số và 1 ký tự đặc biệt.',
            'name.required' => 'Tên không được để trống.',
            'name.max' => 'Tên không được vượt quá 255 ký tự.',
            'avatar.image' => 'Ảnh đại diện phải là file ảnh.',
            'avatar.mimes' => 'Ảnh đại diện phải có định dạng jpeg, png hoặc jpg.',
            'avatar.max' => 'Ảnh đại diện không được vượt quá 2MB.',
            'role.required' => 'Vai trò không được để trống.',
            'role.in' => 'Vai trò không hợp lệ.',
            'status.required' => 'Trạng thái không được để trống.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('s3')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 's3');
            $data['avatar'] = "/passion/{$path}";
        }

        $user->update($data);

        return new UserResource($user);
    }

    public function destroy(User $user)
    {
        if ($user->avatar) {
            Storage::disk('s3')->delete($user->avatar);
        }
        $user->delete();

        return response()->json(null, 204);
    }
}
