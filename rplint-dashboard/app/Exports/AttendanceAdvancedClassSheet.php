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

class AttendanceAdvancedClassSheet implements FromArray, WithHeadings, WithTitle, WithStyles
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
            $headings[] = "Tanggal {$i}";
            $headings[] = "Tanggal {$i}";
        }

        $checkInOutHeaders = [];
        for ($i = 1; $i <= $daysInMonth + 2; $i++) {
            $checkInOutHeaders[] = "Check In";
            $checkInOutHeaders[] = "Check Out";
        }

        return [$headings, $checkInOutHeaders];
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
                $checkIn = $attendance ? Carbon::parse($attendance->first()->check_in_time)->format('H:i:s') : '-';
                $checkOut = $attendance ? Carbon::parse($attendance->last()->check_out_time)->format('H:i:s') : '-';

                $row[] = $checkIn;
                $row[] = $checkOut;
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
        $sheet->getStyle('A1:BN1')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2:BN2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A1:BN1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFCC00');
        $sheet->getStyle('A2:BN2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFCC00');

        $sheet->getStyle('A2:BN2')->getFont()->setBold(true)->setSize(10);

        $sheet->mergeCells('A1:A2');
        $sheet->mergeCells('B1:B2');
        $sheet->mergeCells('C1:C2');
        $sheet->mergeCells('D1:D2');

        $daysInMonth = Carbon::parse($this->month)->daysInMonth;
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $sheet->mergeCellsByColumnAndRow($i * 2 + 3, 1, $i * 2 + 4, 1);
        }

        $sheet->getStyle('C3:C' . (count($this->array()) + 2))
            ->getNumberFormat()
            ->setFormatCode('@');

        $sheet->getStyle('D3:D' . (count($this->array()) + 2))
            ->getNumberFormat()
            ->setFormatCode('@');


        $sheet->getStyle('A1:BN41')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $sheet->getStyle('A1:BN41')->getAlignment()->setHorizontal('center')->setVertical('center');

        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);

        for ($col = 'E'; $col <= 'BN'; $col++) {
            $sheet->getColumnDimension($col)->setWidth(35);
        }
    }
}
