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
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
                'phone' => 'nullable|string|max:20',
                'role' => 'nullable|string',
                'status' => 'nullable|string',
                'avatar' => 'nullable|file|image|max:2048',
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
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'role' => 'sometimes|required|in:user,seller,admin',
            'status' => 'sometimes|required|in:active,inactive,banned',
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
