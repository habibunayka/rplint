<?php

namespace App\Http\Controllers\Api;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController
{
    public function index(Request $request)
    {
        if ($request->bearerToken() !== config('custom.api_token')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $class = $request->query('class', '');
        $students = Student::with('generationClass')
            ->whereHas('generationClass', function ($query) use ($class) {
                $query->where('class_name', 'like', '%' . $class . '%');
            })
            ->get()
            ->map(function ($student) {
                return [
                    'ID' => $student->id,
                    'Absensi' => $student->absence,
                    'Nama lengkap' => $student->user->name,
                    'Sidik Jari 1' => $student->fingerprint_template1,
                    'Sidik Jari 2' => $student->fingerprint_template2,
                    'Sidik Jari 3' => $student->fingerprint_template3,
                    'Angkatan' => $student->generationClass->generation_name,
                    'Kelas' => $student->generationClass->class_name
                ];
            });

        return response()->json($students);
    }

    public function update(Request $request, $id)
    {
        if ($request->bearerToken() !== config('custom.api_token')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'column' => 'required|in:fingerprint_template1,fingerprint_template2,fingerprint_template3',
            'value' => 'required|string'
        ]);

        $student = Student::findOrFail($id);
        $student->{$request->column} = $request->value;
        $student->save();

        return response()->json(['message' => 'Fingerprint updated']);
    }
}
