<?php

namespace Database\Seeders;

use App\Models\GenerationClass;
use App\Models\Schedule;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            'Pemrograman Web',
            'Basis Data',
            'Matematika',
            'Bahasa Inggris',
            'PPL',
            'Kewirausahaan',
        ];

        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $types = ['teori', 'praktik'];

        GenerationClass::all()->each(function (GenerationClass $class) use ($subjects, $days, $types) {
            $selectedDays = collect($days)->shuffle()->take(3)->values();
            $selectedSubjects = collect($subjects)->shuffle()->take(3)->values();

            foreach ($selectedDays as $index => $day) {
                $subject = $selectedSubjects[$index] ?? $selectedSubjects->random();
                $startHour = fake()->numberBetween(7, 13);
                $duration = fake()->numberBetween(1, 3);

                Schedule::updateOrCreate(
                    [
                        'generation_class_id' => $class->id,
                        'day' => $day,
                        'subject' => $subject,
                    ],
                    [
                        'teacher' => optional($class->homeroomTeacher)->name ?? fake()->name(),
                        'type' => fake()->randomElement($types),
                        'start_time' => sprintf('%02d:00:00', $startHour),
                        'end_time' => sprintf('%02d:00:00', $startHour + $duration),
                    ]
                );
            }
        });
    }
}
