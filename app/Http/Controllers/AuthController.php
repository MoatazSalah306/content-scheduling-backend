<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request)
    {

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'timezone' => $request->timezone,
            'password' => Hash::make($request->password),
        ]);

        return $this->success([
            'user'  => $user,
            'token' => $user->createToken('api_token')->plainTextToken,
        ], 'User registered successfully', 201);

    }
    
    public function login(LoginRequest $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->error('Invalid credentials', 401);
        }

        $user = User::where('email', $request->email)->first();

        return $this->success([
            'user'  => $user,
            'token' => $user->createToken('api_token')->plainTextToken,
        ], 'Login successful');
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();
        return $this->success([], 'Logged out successfully');
    }
}
