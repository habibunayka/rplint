<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $students = Student::all();

        foreach ($students as $student) {
            $statusOptions = ['ontime', 'late', 'permit', 'sick'];

            $status = $statusOptions[array_rand($statusOptions)];
            $date = Carbon::today(); // hari ini

            $checkIn = null;
            $checkOut = null;

            if ($status === 'ontime') {
                // ≤ 07:30
                $hour = rand(6, 7);
                $minute = ($hour === 7) ? rand(0, 30) : rand(0, 59);
                $checkIn = $date->copy()->setTime($hour, $minute);
                $checkOut = $date->copy()->setTime(rand(14, 18), rand(0, 59));
            } elseif ($status === 'late') {
                // > 07:30
                $hour = rand(7, 10);
                $minute = ($hour === 7) ? rand(31, 59) : rand(0, 59);
                $checkIn = $date->copy()->setTime($hour, $minute);
                $checkOut = $date->copy()->setTime(rand(14, 18), rand(0, 59));
            }

            Attendance::create([
                'student_id' => $student->id,
                'status' => $status,
                'check_in_time' => $checkIn,
                'check_out_time' => $checkOut,
            ]);
        }
    }
}
