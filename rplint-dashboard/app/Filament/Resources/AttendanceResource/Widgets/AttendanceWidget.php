<?php

namespace App\Filament\Resources\AttendanceResource\Widgets;

use App\Models\Attendance;
use App\Models\GenerationClass;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceWidget extends ChartWidget
{
    public $classId;

    protected static ?string $heading = 'Chart';

    protected static ?string $maxHeight = '300px';

    public function __construct($classId = null)
    {
        $this->classId = $classId;
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $today = now()->toDateString();

        if (!$this->classId) {
            return [
                'labels' => [],
                'datasets' => [],
            ];
        }

        $user = Auth::user();

        if ($user && $user->role === 'homeroom_teacher') {
            $allowedClassIds = GenerationClass::where('homeroom_teacher_id', $user->id)->pluck('id');

            if ($allowedClassIds->isEmpty() || ! $allowedClassIds->contains($this->classId)) {
                return [
                    'labels' => [],
                    'datasets' => [],
                ];
            }
        }

        $class = GenerationClass::find($this->classId);

        if (!$class) {
            return [
                'labels' => [],
                'datasets' => [],
            ];
        }

        self::$heading = 'Kelas ' . $class->class_name;

        $totalStudents = DB::table('students')->where('generation_class_id', $class->id)->count();

        $ontime = Attendance::whereDate('check_in_time', $today)
            ->where('status', 'ontime')
            ->whereHas('student', function ($query) use ($class) {
                $query->where('generation_class_id', $class->id);
            })->count();

        $late = Attendance::whereDate('check_in_time', $today)
            ->where('status', 'late')
            ->whereHas('student', function ($query) use ($class) {
                $query->where('generation_class_id', $class->id);
            })->count();

        $permit = Attendance::whereDate('check_in_time', $today)
            ->where('status', 'permit')
            ->whereHas('student', function ($query) use ($class) {
                $query->where('generation_class_id', $class->id);
            })->count();

        $sick = Attendance::whereDate('check_in_time', $today)
            ->where('status', 'sick')
            ->whereHas('student', function ($query) use ($class) {
                $query->where('generation_class_id', $class->id);
            })->count();

        $belumMasuk = $totalStudents - ($ontime + $late + $permit + $sick);

        if ($belumMasuk < 0) {
            $belumMasuk = 0;
        }

        $datasets = [
            [
                'label' => $class->class_name,
                'data' => [
                    $ontime,
                    $late,
                    $permit,
                    $sick,
                    $belumMasuk,
                ],

                'backgroundColor' => ['#4CAF50', '#FFC107', '#FF9800', '#F44336', '#BDBDBD'],
            ]
        ];



        return [
            'labels' => ['Tepat Waktu', 'Telat', 'Izin', 'Sakit', 'Belum Masuk'],
            'datasets' => $datasets,
        ];
    }

    public function getHeading(): string
    {
        return self::$heading ?? 'Attendance Chart';
    }
}
