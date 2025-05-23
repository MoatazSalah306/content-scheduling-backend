<?php

namespace App\Http\Controllers;

use App\Models\Platform;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class PlatformController extends Controller
{
     use ApiResponse;

    public function index()
    {
        $platforms = Platform::all();
        return $this->success($platforms, 'Available platforms');
    }

    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'platform_id' => 'required|exists:platforms,id',
        ]);

        $user = auth()->user();
        $platformId = $validated['platform_id'];

        if ($user->platforms()->where('platform_id', $platformId)->exists()) {
            $user->platforms()->detach($platformId);
            $message = 'Platform deactivated';
        } else {
            $user->platforms()->attach($platformId);
            $message = 'Platform activated';
        }

        return $this->success([], $message);
    }
}
