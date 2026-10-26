<?php

namespace Database\Seeders;

use App\Models\GenerationClass;
use App\Models\User;
use Illuminate\Database\Seeder;

class GenerationClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $homeroomTeachers = User::where('role', 'homeroom_teacher')->pluck('id')->values();
        $teacherCount = $homeroomTeachers->count();

        if ($teacherCount === 0) {
            $this->command->warn('Tidak ada data wali kelas. Jalankan HomeroomTeacherSeeder terlebih dahulu.');
        }

        $generations = [
            ['generation_name' => 'Angkatan 24A', 'class_name' => '12 RPL 1'],
            ['generation_name' => 'Angkatan 24B', 'class_name' => '12 RPL 2'],
            ['generation_name' => 'Angkatan 25A', 'class_name' => '11 RPL 1'],
            ['generation_name' => 'Angkatan 25B', 'class_name' => '11 RPL 2'],
            ['generation_name' => 'Angkatan 26A', 'class_name' => '10 RPL 1'],
            ['generation_name' => 'Angkatan 26B', 'class_name' => '10 RPL 2'],
        ];

        foreach ($generations as $index => $generation) {
            $teacherId = $homeroomTeachers[$index] ?? null;

            GenerationClass::create([
                ...$generation,
                'homeroom_teacher_id' => $teacherId,
            ]);
        }
    }
}
