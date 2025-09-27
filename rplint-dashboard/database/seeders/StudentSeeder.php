<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\GenerationClass;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    public function run()
    {
        $classes = GenerationClass::all();

        foreach ($classes as $class) {
            for ($i = 0; $i < 5; $i++) {
                $user = User::create([
                    'name' => 'Student ' . ($i + 1) . ' ' . $class->class_name,
                    'email' => 'student' . ($i + 1) . $class->id . '@example.com',
                    'password' => Hash::make('12345678'),
                ]);
                $student = Student::create([
                    'user_id' => $user->id,
                    'generation_class_id' => $class->id,
                    'absence' => 1,
                    'nis' => rand(100000, 999999),
                    'nisn' => rand(100000000000, 999999999999),
                    'profile_photo' => null,
                    'address' => 'Address ' . ($i + 1),
                    'phone_number' => '08123456789',
                    'fingerprint_template1' => 'dummy-fingerprint-template1',
                    'fingerprint_template2' => 'dummy-fingerprint-template2',
                    'fingerprint_template3' => 'dummy-fingerprint-template3',
                ]);
                $user->student_id = $student->id;
                $user->save();
            }
        }
    }
}
