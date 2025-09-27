<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use App\Models\Attendance;
use App\Models\Student;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AttendanceClassSheet implements FromArray, WithHeadings, WithTitle, WithStyles
{
    protected $class;
    protected $month;

    public function __construct($class, $month)
    {
        $this->class = $class;
        $this->month = $month;
    }

    public function headings(): array
    {
        $headings = ['No', 'Nama Lengkap', 'NIS', 'NISN'];
        $daysInMonth = Carbon::parse($this->month)->daysInMonth;

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $headings[] = "{$i}";
        }

        return $headings;
    }

    public function array(): array
    {
        $students = Student::where('generation_class_id', $this->class->id)
            ->orderBy('absence')
            ->get();

        $month = Carbon::parse($this->month);
        $daysInMonth = $month->daysInMonth;

        $attendances = Attendance::whereIn('student_id', $students->pluck('id'))
            ->whereMonth('check_in_time', $month->month)
            ->whereYear('check_in_time', $month->year)
            ->get()
            ->groupBy(function ($attendance) {
                return $attendance->student_id . '-' . Carbon::parse($attendance->check_in_time)->toDateString();
            });

        $data = [];

        foreach ($students as $student) {
            $row = [
                $student->absence,
                $student->user->name,
                "'" . $student->nis,
                "'" . $student->nisn,
            ];

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = $month->copy()->day($day)->toDateString();
                $key = $student->id . '-' . $date;

                $attendance = $attendances->get($key);

                $row[] = $attendance ? match ($attendance->first()->status) {
                    'ontime' => 'H',
                    'late' => 'H',
                    'sick' => 'S',
                    'permit' => 'I',
                    'absent' => 'A',
                    default => 'Libur',
                } : 'Libur';
            }

            $data[] = $row;
        }

        return $data;
    }

    public function title(): string
    {
        return $this->class->class_name;
    }

    public function styles($sheet)
    {
        $sheet->getStyle('A1:AI1')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A1:AI1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFCC00');

        $sheet->getStyle('A1:AI41')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $sheet->getStyle('C1:AI41')->getAlignment()->setHorizontal('center')->setVertical('center');

        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);

        for ($col = 'E'; $col <= 'AI'; $col++) {
            $sheet->getColumnDimension($col)->setWidth(2);
        }
    }
}
