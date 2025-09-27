<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\Attendance;
use Carbon\Carbon;

class AutoCheckIn extends Command
{
    protected $signature = 'attendance:auto-check-in';
    protected $description = 'Automatically mark students as absent if no attendance is recorded';

    public function handle()
    {
        $missedDates = collect();
        $startDate = Carbon::now()->subDays(1);
        $endDate = Carbon::now();

        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            if ($date->isWeekend()) {
                $this->info("Melewati hari weekend: " . $date->toDateString());
                continue;
            }

            $specialDays = $this->getSpecialDays($date);
            if ($specialDays->isNotEmpty()) {
                foreach ($specialDays as $specialDay) {
                    $this->handleSpecialDay($date, $specialDay);
                }
                continue;
            }

            $students = Student::whereDoesntHave('attendances', function ($query) use ($date) {
                $query->whereDate('check_in_time', $date);
            })
                ->whereDoesntHave('specialDays', function ($query) use ($date) {
                    $query->whereDate('date_start', '<=', $date)
                        ->whereDate('date_end', '>=', $date);
                })
                ->get();

            if ($students->isEmpty()) {
                $this->info("Tidak ada siswa yang perlu diupdate untuk tanggal: " . $date->toDateString());
                continue;
            }

            foreach ($students as $student) {
                $existingAttendance = Attendance::where('student_id', $student->id)
                    ->whereDate('check_in_time', $date)
                    ->first();

                if (!$existingAttendance) {
                    Attendance::updateOrCreate(
                        ['student_id' => $student->id, 'check_in_time' => $date],
                        ['status' => 'absent']
                    );
                }
            }

            $missedDates->push([
                'date' => $date->toDateString(),
                'count' => $students->count()
            ]);
        }

        if ($missedDates->isEmpty()) {
            $this->info('Tidak ada absensi yang perlu diupdate.');
        } else {
            $this->info('Ringkasan Update Absensi:');
            $missedDates->each(function ($item) {
                $this->line("Tanggal: {$item['date']} - {$item['count']} siswa ditandai absent");
            });
        }
    }

    private function getSpecialDays($date)
    {
        return \App\Models\SpecialDay::whereDate('date_start', '<=', $date)
            ->whereDate('date_end', '>=', $date)
            ->get();
    }

    private function handleSpecialDay($date, $specialDay)
    {
        $this->info("Menangani hari khusus pada tanggal " . $date->toDateString() . ": " . $specialDay->description);

        $students = collect();

        if ($specialDay->student_type === 'all') {
            $students = Student::all();
        } elseif ($specialDay->student_type === 'class') {
            $students = Student::whereHas('generationClass', function ($query) use ($specialDay) {
                $query->whereIn('generation_classes.id', $specialDay->classes->pluck('id'));
            })->get();
        } elseif ($specialDay->student_type === 'selected') {
            $students = Student::whereHas('specialDays', function ($query) use ($specialDay) {
                $query->where('special_days.id', $specialDay->id);
            })->get();
        }

        foreach ($students as $student) {
            if ($specialDay->status === 'holiday') {
                continue;
            } else {
                $existingAttendance = Attendance::where('student_id', $student->id)
                    ->whereDate('check_in_time', $date)
                    ->first();

                if (!$existingAttendance) {
                    Attendance::updateOrCreate(
                        ['student_id' => $student->id, 'check_in_time' => $date],
                        ['status' => $specialDay->status]
                    );
                }
            }
        }

        $this->info("Pada tanggal ini: {$date->toDateString()}, " . $students->count() . " siswa ditandai {$specialDay->status}");

        $otherStudents = Student::whereDoesntHave('attendances', function ($query) use ($date) {
            $query->whereDate('check_in_time', $date);
        })
            ->whereDoesntHave('specialDays', function ($query) use ($specialDay, $date) {
                $query->where('special_days.id', $specialDay->id)
                    ->whereDate('date_start', '<=', $date)
                    ->whereDate('date_end', '>=', $date);
            })
            ->whereNotIn('students.id', $students->pluck('id'))
            ->get();

        foreach ($otherStudents as $student) {
            $existingAttendance = Attendance::where('student_id', $student->id)
                ->whereDate('check_in_time', $date)
                ->first();

            if (!$existingAttendance) {
                Attendance::updateOrCreate(
                    ['student_id' => $student->id, 'check_in_time' => $date],
                    ['status' => 'absent']
                );
            }
        }

        $this->info("Dan " . $otherStudents->count() . " siswa lainnya ditandai absent");
    }
}
