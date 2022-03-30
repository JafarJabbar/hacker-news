<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;

class AuthController extends Controller
{
    /**
     *  Login method
     */
    public function login(Request $request)
    {
        $rules = [
            'email' => 'required|email:rfc,dns',
            'password' => 'required|min:6',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response([
                'status' => false,
                'code' => 400,
                'title' => $validator->errors()->first()
            ]);
        }
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response([
                'status' => false,
                'code' => 400,
                'title' => "Bad credentials"
            ]);
        }
        if (!Hash::check($request->password, $user->password)) {
            return response([
                'status' => false,
                'code' => 400,
                'title' => 'Bad credentials'
            ]);
        } else {
            $user->last_login = date('Y-m-d H:i:s');
            $user->save();
            return response([
                'status' => true,
                'code' => 200,
                'user' => new UserResource($user),
                'token' => $user->createToken('token')->plainTextToken
            ]);
        }
    }

    /**
     * User register
     *
     * @bodyParam full_name string required The full_name of the user. Example: Ad Soyad
     * @bodyParam phone string required The phone number of the user. Example: +994500000000
     * @bodyParam email string required The email of the user. Example: test@example.com
     * @bodyParam password string required The password  of the user. Example: 123abcBCA
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email:rfc,dns|unique:users,email',
            'password' => 'required|confirmed|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'status' => false,
                'title' => $validator->errors()->first()
            ]);
        }
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->last_login = date('Y-m-d H:i:s');
        $user->save();
        return response()->json([
            'status' => true,
            'code' => 201,
            'user' => new UserResource($user),
            'token' => $user->createToken('token')->plainTextToken
        ]);
    }

    /**
     * Logout current user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => "Successfully logged out"
        ]);
    }

    /**
     * Profile of logged in user
     */
    public function profile(Request $request)
    {
        return response([
            'code' => 200,
            'status' => true,
            'user' => new UserResource($request->user()),
        ]);
    }
}
