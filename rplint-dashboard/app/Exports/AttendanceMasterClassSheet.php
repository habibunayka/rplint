<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use App\Models\Attendance;
use App\Models\Student;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class AttendanceMasterClassSheet implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnFormatting
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

        $headingsRow1 = ['No', 'Nama Lengkap', 'NIS', 'NISN'];
        $headingsRow2 = ['', '', '', ''];

        $year = Carbon::parse($this->month)->year - 1;
        $monthNames = [
            1  => "Januari",
            2  => "Februari",
            3  => "Maret",
            4  => "April",
            5  => "Mei",
            6  => "Juni",
            7  => "Juli",
            8  => "Agustus",
            9  => "September",
            10 => "Oktober",
            11 => "November",
            12 => "Desember"
        ];

        for ($m = 1; $m <= 12; $m++) {
            $headingsRow1[] = $monthNames[$m];
            $headingsRow1[] = '';
            $headingsRow1[] = '';

            $headingsRow2[] = 'Hadir';
            $headingsRow2[] = 'Izin';
            $headingsRow2[] = 'Absent';
        }

        $headingsRow1[] = 'Total';
        $headingsRow1[] = '';
        $headingsRow1[] = '';

        $headingsRow2[] = 'Hadir';
        $headingsRow2[] = 'Izin';
        $headingsRow2[] = 'Sakit';

        return [$headingsRow1, $headingsRow2];
    }

    public function array(): array
    {
        $data = [];
        $students = Student::where('generation_class_id', $this->class->id)
            ->orderBy('absence')
            ->get();

        $tahunAcuan = Carbon::parse($this->month)->year - 1;
        $startDate = Carbon::create($tahunAcuan, 1, 1)->toDateString();
        $endDate = Carbon::create($tahunAcuan, 12, 31)->toDateString();

        $attendances = Attendance::whereIn('student_id', $students->pluck('id'))
            ->whereBetween('check_in_time', [$startDate, $endDate])
            ->get()
            ->groupBy(function ($attendance) {
                return $attendance->student_id . '-' . Carbon::parse($attendance->check_in_time)->toDateString();
            });

        foreach ($students as $student) {
            $row = [
                $student->absence,
                $student->user->name,
                "'" . $student->nis,
                "'" . $student->nisn,
            ];

            $hadirTotal = 0;
            $izinTotal = 0;
            $sakitTotal = 0;

            for ($m = 1; $m <= 12; $m++) {
                $monthCarbon = Carbon::create($tahunAcuan, $m, 1);
                $daysInMonth = $monthCarbon->daysInMonth;
                $hadir = 0;
                $izin  = 0;
                $absent = 0;

                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $date = Carbon::create($tahunAcuan, $m, $day)->toDateString();
                    $key = $student->id . '-' . $date;
                    if ($attendances->has($key)) {
                        $attendanceGroup = $attendances->get($key);
                        $dailyStatus = $attendanceGroup->first()->status;
                        if ($dailyStatus === 'permit') {
                            $izin++;
                            $izinTotal++;
                        } else if ($dailyStatus === 'sick') {
                            $sakitTotal++;
                            $izin++;
                        } else if ($dailyStatus === 'ontime' || $dailyStatus === 'late') {
                            $hadir++;
                            $hadirTotal++;
                        } else if ($dailyStatus === 'absent') {
                            $absent++;
                        }
                    }
                }
                $row[] = $hadir === 0 ? '-' : $hadir;
                $row[] = $izin === 0 ? '-' : $izin;
                $row[] = $absent === 0 ? '-' : $absent;
            }

            $row[] = $hadirTotal === 0 ? '-' : $hadirTotal;
            $row[] = $izinTotal === 0 ? '-' : $izinTotal;
            $row[] = $sakitTotal === 0 ? '-' : $sakitTotal;

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
        $monthCount = 12;
        $staticColumns = 4;
        $columnsPerMonth = 3;
        $totalColumns = $staticColumns + ($monthCount * $columnsPerMonth) + 3;
        $lastColumn = Coordinate::stringFromColumnIndex($totalColumns);

        $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("A2:{$lastColumn}2")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("A1:{$lastColumn}1")->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFCC00');
        $sheet->getStyle("A2:{$lastColumn}2")->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFCC00');

        $sheet->mergeCells('A1:A2');
        $sheet->mergeCells('B1:B2');
        $sheet->mergeCells('C1:C2');
        $sheet->mergeCells('D1:D2');

        for ($m = 1; $m <= $monthCount; $m++) {
            $startCol = 5 + (($m - 1) * 3);
            $endCol = $startCol + 2;
            $sheet->mergeCellsByColumnAndRow($startCol, 1, $endCol, 1);
        }

        $totalStartCol = 5 + ($monthCount * $columnsPerMonth);
        $totalEndCol = $totalStartCol + 2;
        $sheet->mergeCellsByColumnAndRow($totalStartCol, 1, $totalEndCol, 1);

        $dataCount = count($this->array()) + 2;
        $sheet->getStyle("A1:{$lastColumn}{$dataCount}")
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A1:{$lastColumn}{$dataCount}")
            ->getAlignment()->setHorizontal('center')->setVertical('center');

        $sheet->getStyle("A1:{$lastColumn}2")
            ->getAlignment()
            ->setHorizontal('center')
            ->setVertical('center');

        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);

        for ($colIndex = 5; $colIndex <= $totalColumns; $colIndex++) {
            $column = Coordinate::stringFromColumnIndex($colIndex);
            $sheet->getColumnDimension($column)->setWidth(15);
        }
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
        ];
    }
}
