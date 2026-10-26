<?php

namespace App\Http\Controllers\Api\App;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController
{
    public function login(Request $request)
    {
        $request->validate([
            'nis' => 'required',
            'password' => 'required',
        ]);

        $student = Student::with(['user', 'generationClass'])
            ->where('nis', $request->nis)
            ->first();

        if (! $student || ! $student->user || ! Hash::check($request->password, $student->user->password)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Nis atau Password anda salah!',
            ], 400);
        }

        $user = $student->user;

        $token = $user->createToken('RPLintApp')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'You have been succesfully log in',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'nis' => $student->nis,
                'class_name' => $student->generationClass->class_name ?? null,
                'nisn' => $student->nisn,
                'profile_photo' => $student->profile_photo,
                'address' => $student->address,
                'phone_number' => $student->phone_number,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'You just logged out'
        ], 200);
    }
}
