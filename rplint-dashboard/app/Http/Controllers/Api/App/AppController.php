<?php

namespace App\Http\Controllers\Api\App;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppController
{
    public function home()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        $todayDate = Carbon::today();

        $attendanceToday = Attendance::where('student_id', $student->id)
            ->whereDate('check_in_time', $todayDate)
            ->first();

        $presentCount = Attendance::where('student_id', $student->id)
            ->whereIn('status', ['ontime', 'late'])
            ->count();

        $absentCount = Attendance::where('student_id', $student->id)
            ->where('status', 'absent')
            ->count();

        $permitCount = Attendance::where('student_id', $student->id)
            ->whereIn('status', ['permit', 'sick'])
            ->count();

        $fiveStatusRaw = Attendance::where('student_id', $student->id)
            ->select(['check_in_time', 'check_out_time', 'status'])
            ->orderBy('check_in_time', 'desc')
            ->limit(5)
            ->get();

        $fiveStatus = $fiveStatusRaw->map(function ($item) {
            return [
                'date' => Carbon::parse($item->check_in_time)->format('Y-m-d'),
                'checkIn' => Carbon::parse($item->check_in_time)->format('h:i A'),
                'checkOut' => $item->check_out_time
                    ? Carbon::parse($item->check_out_time)->format('h:i A')
                    : '-',
                'status' => $item->status
            ];
        });

        $today = strtolower(Carbon::now()->format('l'));
        $now = Carbon::now();

        $scheduleTheoryRaw = Schedule::where('day', $today)
            ->where('type', 'theory')
            ->where(function ($query) use ($now) {
                $query->where(function ($q) use ($now) {
                    $q->where('start_time', '<=', $now->format('H:i:s'))
                        ->where('end_time', '>=', $now->format('H:i:s'));
                })->orWhere('start_time', '>', $now->format('H:i:s'));
            })
            ->orderBy('start_time')
            ->limit(5)
            ->get();

        $scheduleTheory = $scheduleTheoryRaw->map(function ($item) {
            return [
                'subject' => $item->subject,
                'teacher' => $item->teacher,
                'time' => Carbon::parse($item->start_time)->format('H:i') . ' - ' .
                    Carbon::parse($item->end_time)->format('H:i'),
            ];
        });

        $scheduleProductiveRaw = Schedule::where('day', $today)
            ->where('type', 'productive')
            ->where(function ($query) use ($now) {
                $query->where(function ($q) use ($now) {
                    $q->where('start_time', '<=', $now->format('H:i:s'))
                        ->where('end_time', '>=', $now->format('H:i:s'));
                })->orWhere('start_time', '>', $now->format('H:i:s'));
            })
            ->orderBy('start_time')
            ->limit(5)
            ->get();

        $scheduleProductive = $scheduleProductiveRaw->map(function ($item) {
            return [
                'subject' => $item->subject,
                'teacher' => $item->teacher,
                'time' => Carbon::parse($item->start_time)->format('H:i') . ' - ' .
                    Carbon::parse($item->end_time)->format('H:i'),
            ];
        });

        return response()->json([
            'checkIn' => $attendanceToday && $attendanceToday->check_in_time
                ? Carbon::parse($attendanceToday->check_in_time)->format('h:i A')
                : null,
            'checkOut' => $attendanceToday && $attendanceToday->check_out_time
                ? Carbon::parse($attendanceToday->check_out_time)->format('h:i A')
                : null,
            'presentCount' => $presentCount,
            'absentCount' => $absentCount,
            'permitCount' => $permitCount,
            'fiveStatus' => $fiveStatus,
            'scheduleTheory' => $scheduleTheory,
            'scheduleProductive' => $scheduleProductive,
        ]);
    }


    public function account()
    {
        $user = Auth::user();

        $userData = Student::where('user_id', $user->id)->first();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'nis' => $userData->nis,
                'class_name' => $userData->generationClass->class_name,
                'nisn' => $userData->nisn,
                'profile_photo' => $userData->profile_photo,
                'address' => $userData->address,
                'phone_number' => $userData->phone_number,
            ],
        ]);
    }

    public function recent()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        $thirtyDaysAgo = now()->subDays(30)->startOfDay();

        $checkins = Attendance::where('student_id', $student->id)
            ->where('check_in_time', '>=', $thirtyDaysAgo)
            ->orderBy('check_in_time', 'desc')
            ->get(['check_in_time', 'check_out_time', 'status']);

        $data = $checkins->map(function ($item) {
            return [
                'check_in_time' => $item->check_in_time ? $item->check_in_time->format('Y-m-d H:i:s') : '0000-00-00 00:00:00',
                'check_out_time' => $item->check_out_time ? $item->check_out_time->format('Y-m-d H:i:s') : '0000-00-00 00:00:00',
                'status' => $item->status ?? 'unknown',
            ];
        });

        return response()->json([
            'data' => $data
        ]);
    }


    public function subject()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Student data not found for this user',
            ], 404);
        }

        $generationClassId = $student->generation_class_id;

        $daysIndo = [
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
        ];

        $schedule = [];

        foreach ($daysIndo as $dayEng => $dayIndo) {
            $general = Schedule::where('day', $dayEng)
                ->where('type', 'theory')
                ->where('generation_class_id', $generationClassId)
                ->orderBy('start_time')
                ->get()
                ->map(function ($item) {
                    return [
                        'subject' => $item->subject,
                        'time' => $item->start_time . ' - ' . $item->end_time,
                    ];
                });

            $major = Schedule::where('day', $dayEng)
                ->where('type', 'productive')
                ->where('generation_class_id', $generationClassId)
                ->orderBy('start_time')
                ->get()
                ->map(function ($item) {
                    return [
                        'subject' => $item->subject,
                        'time' => $item->start_time . ' - ' . $item->end_time,
                    ];
                });

            $schedule[] = [
                'day' => $dayIndo,
                'general' => $general,
                'major' => $major,
            ];
        }

        return response()->json([
            'status' => 'success',
            'schedule' => $schedule,
        ]);
    }
}
