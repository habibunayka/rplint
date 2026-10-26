<?php

namespace Database\Seeders;

use App\Models\SpecialDay;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SpecialDaySeeder extends Seeder
{
    public function run(): void
    {
        $specialDays = [
            [
                'description' => 'Latihan Praktek Kerja Industri',
                'status' => 'holiday',
                'student_type' => 'all',
                'date_start' => Carbon::now()->addDays(7)->toDateString(),
                'date_end' => Carbon::now()->addDays(10)->toDateString(),
            ],
            [
                'description' => 'Ujian Tengah Semester',
                'status' => 'holiday',
                'student_type' => 'all',
                'date_start' => Carbon::now()->addDays(30)->toDateString(),
                'date_end' => Carbon::now()->addDays(35)->toDateString(),
            ],
        ];

        foreach ($specialDays as $data) {
            SpecialDay::updateOrCreate(
                ['description' => $data['description']],
                $data
            );
        }
    }
}
