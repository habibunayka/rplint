<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\Attendance;
use App\Models\Student;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class AttendanceAdvancedClassSheet implements
    FromArray,
    WithHeadings,
    WithTitle,
    WithStyles,
    WithColumnFormatting,
    WithCustomStartCell
{
    protected $class;
    protected $month;

    public function __construct($class, $month)
    {
        $this->class = $class;
        $this->month = $month;
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function headings(): array
    {
        $daysInMonth = Carbon::parse($this->month)->daysInMonth;


        $row1 = ['No', 'Nama Lengkap', 'NIS', 'NISN'];

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $row1[] = "Tanggal {$i}";
            $row1[] = "";
        }

        $row1[] = "Total";
        $row1[] = "";
        $row1[] = "";


        $row2 = ["", "", "", ""];

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $row2[] = "Check In";
            $row2[] = "Check Out";
        }

        $row2[] = "Hadir";
        $row2[] = "Izin";
        $row2[] = "Sakit";

        return [$row1, $row2];
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

            $hadirCount = 0;
            $sakitCount = 0;
            $izinCount = 0;

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = $month->copy()->day($day)->toDateString();
                $key = $student->id . '-' . $date;

                $attendance = $attendances->get($key);
                $dailyStatus = $attendance ? $attendance->first()->status : null;

                $checkIn = '-';
                $checkOut = '-';

                if ($attendance) {
                    $firstRecord = $attendance->first();
                    $lastRecord = $attendance->last();

                    $checkInTime = $firstRecord->check_in_time;
                    $checkOutTime = $lastRecord->check_out_time;

                    $checkIn = $checkInTime ? Carbon::parse($checkInTime)->format('H:i:s') : '-';
                    $checkOut = $checkOutTime ? Carbon::parse($checkOutTime)->format('H:i:s') : '-';
                }

                switch ($dailyStatus) {
                    case 'ontime':
                    case 'late':
                        $hadirCount++;
                        break;
                    case 'sick':
                        $sakitCount++;
                        break;
                    case 'permit':
                        $izinCount++;
                        break;
                    default:

                        break;
                }

                $row[] = $checkIn;
                $row[] = $checkOut;
            }


            $row[] = $hadirCount;
            $row[] = $izinCount;
            $row[] = $sakitCount;

            $data[] = $row;
        }

        return $data;
    }


    public function title(): string
    {
        return $this->class->class_name;
    }

    public function styles(Worksheet $sheet)
    {
        $daysInMonth = Carbon::parse($this->month)->daysInMonth;

        $totalStart = ($daysInMonth * 2) + 5;
        $totalEnd = $totalStart + 2;

        $endColumn = Coordinate::stringFromColumnIndex($totalEnd);


        $sheet->getStyle("A1:{$endColumn}2")->getFont()->setBold(true);
        $sheet->getStyle("A1:{$endColumn}2")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFCC00');


        foreach (['A', 'B', 'C', 'D'] as $col) {
            $sheet->mergeCells("{$col}1:{$col}2");
        }


        $colIndex = 5;
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $col1 = Coordinate::stringFromColumnIndex($colIndex);
            $col2 = Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->mergeCells("{$col1}1:{$col2}1");
            $colIndex += 2;
        }


        $sheet->mergeCells(
            Coordinate::stringFromColumnIndex($totalStart)
                . "1:" .
                Coordinate::stringFromColumnIndex($totalEnd)
                . "1"
        );

        $sheet->getStyle("A1:{$endColumn}2")
            ->getAlignment()
            ->setHorizontal('center')
            ->setVertical('center');


        $sheet->getStyle("A1:{$endColumn}500")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        return [];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
        ];
    }
}
