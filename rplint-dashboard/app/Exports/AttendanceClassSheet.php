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

class AttendanceClassSheet implements
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
            $row1[] = $i;
        }


        $row1[] = 'Total';
        $row1[] = '';
        $row1[] = '';


        $row2 = ['', '', '', ''];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $row2[] = '';
        }

        $row2[] = 'Hadir';
        $row2[] = 'Izin';
        $row2[] = 'Sakit';

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
            ->groupBy(fn($a) => $a->student_id . '-' . Carbon::parse($a->check_in_time)->toDateString());

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

                switch ($dailyStatus) {
                    case 'ontime':
                    case 'late':
                        $row[] = 'H';
                        $hadirCount++;
                        break;
                    case 'sick':
                        $row[] = 'S';
                        $sakitCount++;
                        break;
                    case 'permit':
                        $row[] = 'I';
                        $izinCount++;
                        break;
                    case 'absent':
                        $row[] = 'A';
                        break;
                    default:
                        $row[] = 'Libur';
                        break;
                }
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

        $totalStartCol = $daysInMonth + 5;
        $totalEndCol   = $daysInMonth + 7;

        $startCol = 'A';
        $endCol = Coordinate::stringFromColumnIndex($totalEndCol);


        $sheet->getStyle("A1:{$endCol}2")->getFont()->setBold(true);
        $sheet->getStyle("A1:{$endCol}2")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFCC00');


        foreach (['A', 'B', 'C', 'D'] as $col) {
            $sheet->mergeCells("{$col}1:{$col}2");
        }


        for ($i = 1; $i <= $daysInMonth; $i++) {
            $col = Coordinate::stringFromColumnIndex($i + 4);
            $sheet->mergeCells("{$col}1:{$col}2");
        }


        $sheet->mergeCells(
            Coordinate::stringFromColumnIndex($totalStartCol)
                . "1:" .
                Coordinate::stringFromColumnIndex($totalEndCol)
                . "1"
        );

        $sheet->getStyle("A1:{$endCol}2")
            ->getAlignment()
            ->setHorizontal('center')
            ->setVertical('center');


        $sheet->getStyle("A1:{$endCol}500")
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
