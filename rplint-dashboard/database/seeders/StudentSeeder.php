<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\GenerationClass;
use App\Models\User;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run()
    {
        $faker = fake();
        $classes = GenerationClass::with('homeroomTeacher')->get();

        foreach ($classes as $class) {
            $faker->unique(true);

            for ($i = 0; $i < 5; $i++) {
                $user = User::create([
                    'name' => sprintf('Siswa %s %d', $class->class_name, $i + 1),
                    'email' => sprintf('student%02d-class%d@example.com', $i + 1, $class->id),
                    'password' => '12345678',
                    'role' => 'student',
                ]);

                $student = Student::create([
                    'user_id' => $user->id,
                    'generation_class_id' => $class->id,
                    'absence' => $i + 1,
                    'nis' => $faker->unique()->numberBetween(100000, 999999),
                    'nisn' => $faker->unique()->numerify('############'),
                    'profile_photo' => null,
                    'address' => 'Alamat ' . ($i + 1) . ' ' . $class->class_name,
                    'phone_number' => $faker->phoneNumber(),
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
