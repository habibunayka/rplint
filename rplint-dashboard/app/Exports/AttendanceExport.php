<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AttendanceExport implements WithMultipleSheets
{
    use Exportable;

    protected $classes;
    protected $month;

    public function __construct($classes, $month)
    {
        $this->classes = $classes;
        $this->month = $month;
    }
    public function sheets(): array
    {
        $sheets = [];
        foreach ($this->classes as $class) {
            $sheets[] = new AttendanceClassSheet($class, $this->month);
        }

        return $sheets;
    }
}
