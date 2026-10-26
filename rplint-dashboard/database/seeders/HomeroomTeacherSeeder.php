<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class HomeroomTeacherSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = [
            ['name' => 'Dewi Lestari', 'email' => 'dewi.lestari@example.com'],
            ['name' => 'Ahmad Pratama', 'email' => 'ahmad.pratama@example.com'],
            ['name' => 'Siti Rahmawati', 'email' => 'siti.rahmawati@example.com'],
            ['name' => 'Bambang Saputra', 'email' => 'bambang.saputra@example.com'],
        ];

        foreach ($teachers as $teacher) {
            User::updateOrCreate(
                ['email' => $teacher['email']],
                [
                    'name' => $teacher['name'],
                    'password' => Hash::make('password123'),
                    'role' => 'homeroom_teacher',
                ]
            );
        }
    }
}
