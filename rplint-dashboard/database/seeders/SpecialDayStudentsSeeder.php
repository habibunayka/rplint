<?php

namespace Database\Seeders;

use App\Models\SpecialDay;
use App\Models\SpecialDayStudents;
use App\Models\Student;
use Illuminate\Database\Seeder;

class SpecialDayStudentsSeeder extends Seeder
{
    public function run(): void
    {
        $specialDays = SpecialDay::all();
        $students = Student::pluck('id');

        if ($specialDays->isEmpty() || $students->isEmpty()) {
            $this->command->warn('Special day atau siswa belum tersedia untuk relasi.');
            return;
        }

        foreach ($specialDays as $specialDay) {
            $studentIds = collect($students->random(min(5, $students->count())))->all();

            foreach ($studentIds as $studentId) {
                SpecialDayStudents::updateOrCreate(
                    [
                        'special_day_id' => $specialDay->id,
                        'student_id' => $studentId,
                    ]
                );
            }
        }
    }
}
