<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validate = Validator::make($request->all(), [
           'username' => 'required',
            'password' => 'required'
        ]);

        if($validate->fails()){
            return response()->json([
                'message' => 'Invalid field',
                'errors' => $validate->errors()
            ], 422);
        }

        $data = $validate->getData();

        if(!Auth::validate($data)){
            return response()->json([
                'message' => 'Wrong username or password',
            ], 401);
        }

        $user = User::firstWhere('username', $data['username']);

        $token = $user->createToken('user login')->plainTextToken;

        return response()->json([
            'message' => 'Login success',
            'token' => $token,
            'user' => $user->only(['id', 'full_name', 'username', 'bio', 'is_private', 'created_at'])
        ], 200);
    }

    public function register(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'full_name' => 'required',
            'username' => 'required|unique:users,username|min:3|regex:/^[A-Za-z._]+$/',
            'bio' => 'required|max:100',
            'is_private' => 'boolean',
            'password' => 'required|min:6'
        ]);

        if($validate->fails()){
            return response()->json([
                'message' => 'Invalid field',
                'errors' => $validate->errors()
            ], 422);
        }

        $data = $validate->getData();

        $user = User::create($data);

        $token = $user->createToken('user register')->plainTextToken;

        return response()->json([
            'message' => 'Register success',
            'token' => $token,
            'user' => $user->only(['full_name', 'bio', 'username', 'is_private', 'id']),
        ], 200);
    }

    public function logout()
    {
        Auth::user()->tokens()->delete();
        return response()->json([
            'message' => 'Logout success',
        ], 200);
    }
}
