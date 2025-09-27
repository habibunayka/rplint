<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Models\GenerationClass;

class AttendanceAdvancedExport implements WithMultipleSheets
{
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
            $sheets[] = new AttendanceAdvancedClassSheet($class, $this->month);
        }

        return $sheets;
    }
}
