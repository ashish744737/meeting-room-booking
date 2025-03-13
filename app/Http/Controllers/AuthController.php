<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'User registered successfully!']);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && $user->blocked_until && Carbon::now()->lt($user->blocked_until)) {
            return response()->json(['message' => 'User is blocked for 24 hours.'], 429);
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            if ($user) {
                $user->increment('failed_attempts');
                if ($user->failed_attempts >= 3) {
                    $user->blocked_until = Carbon::now()->addDay();
                    $user->save();
                }
            }
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Reset attempts on success
        $user->update(['failed_attempts' => 0, 'blocked_until' => null]);

        return response()->json(['token' => $user->createToken('authToken')->plainTextToken]);
    }

    public function logout()
    {
        Auth::user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
