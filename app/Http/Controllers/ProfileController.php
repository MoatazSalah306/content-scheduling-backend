<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    use ApiResponse;

    public function show()
    {
        return $this->success(auth()->user(), 'User profile fetched successfully');
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = auth()->user();

        $data = $request->only(['name', 'email']);

        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return $this->success($user->fresh(), 'Profile updated successfully');
    }
}
