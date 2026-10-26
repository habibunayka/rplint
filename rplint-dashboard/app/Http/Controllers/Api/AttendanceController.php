<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        $statusMap = [
            'ontime'  => 'Tepat Waktu',
            'late'    => 'Terlambat',
            'permit'  => 'Izin',
            'sick'    => 'Sakit',
            'absent'  => 'Tidak Hadir',
        ];

        $attendances = Attendance::with('student.user:id,name')
            ->orderByDesc('updated_at')
            ->orderByDesc('check_in_time')
            ->whereDate('check_in_time', Carbon::today())
            ->get()
            ->map(function ($item) use ($statusMap) {
                return [
                    'id' => $item->student_id,
                    'full_name' => $item->student->user->name ?? '',
                    'class_name' => $item->student->generationClass->class_name ?? '',
                    'status' => $statusMap[$item->status] ?? strtoupper($item->status),
                    'check_in_time' => $item->check_in_time->format('H:i:s'),
                    'check_out_time' => $item->check_out_time ? $item->check_out_time->format('H:i:s') : null,
                ];
            });

        return response()->json($attendances);
    }


    public function getFingerprints()
    {
        $students = Student::with('user:id,name')->get()->map(function ($student) {
            return [
                'id' => $student->id,
                'full_name' => $student->user->name ?? '',
                'fingerprint_template1' => $student->fingerprint_template1,
                'fingerprint_template2' => $student->fingerprint_template2,
                'fingerprint_template3' => $student->fingerprint_template3,
            ];
        });

        return response()->json($students);
    }


    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'scanned_at' => 'required|date'
        ]);

        $now = Carbon::parse($request->scanned_at);
        $dateNow = $now->format('Y-m-d');

        $attendance = Attendance::where('student_id', $request->student_id)
            ->whereDate('check_in_time', $dateNow)
            ->first();

        if (!$attendance) {
            $status = $now->toTimeString() <= '07:15:59' ? 'ontime' : 'late';

            Attendance::create([
                'student_id' => $request->student_id,
                'status' => $status,
                'check_in_time' => $now,
                'check_out_time' => null,
            ]);

            return response()->json([
                'message' => 'Check in berhasil!',
                'status' => $status
            ], 201);
        } else {
            if ($attendance->check_out_time !== null) {
                return response()->json([
                    'message' => 'Kamu sudah check in hari ini.'
                ], 400);
            }

            $checkInTime = Carbon::parse($attendance->check_in_time);
            $diffMinutes = $now->diffInMinutes($checkInTime);

            if ($diffMinutes < 60) {
                return response()->json([
                    'message' => 'Tunggu 1 jam lagi sebelum check out.'
                ], 400);
            }

            $attendance->update([
                'check_out_time' => $now,
            ]);

            return response()->json([
                'message' => 'Check out berhasil!'
            ], 200);
        }
    }
}
