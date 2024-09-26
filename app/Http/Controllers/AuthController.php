<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        if (Auth::attempt($request->validated())) {
            $user = Auth::user();
            $user->token = app()->isProduction() ? $request->session()->regenerate() : $user->createToken('authToken')->plainTextToken;

            return $user;
        }

        return response()->json(['error' => 'We apologize, but the credentials provided are not valid.'], 401);
    }

    public function me(Request $request)
    {
        try {
            $user = Auth::user();
            $user->token = $request->bearerToken();

            return UserResource::make($user);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'We apologize, but the credentials provided are not valid.'], 401);
        }
    }

    public function logout(Request $request)
    {
        app()->isProduction() ? $request->session()->invalidate() : $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'You have been successfully logged out.'], 200);
    }
}
