<?php

namespace App\Console\Commands;

use App\Exports\AttendanceAdvancedExport;
use Illuminate\Console\Command;
use App\Exports\AttendanceExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Models\GenerationClass;
use App\Models\AttendanceReport;
use Carbon\Carbon;

class ExportAttendance extends Command
{
    protected $signature = 'attendance:export';
    protected $description = 'Export attendance data for the previous month';

    public function handle()
    {
        $lastMonth = Carbon::now()->subMonth();
        $monthString = $lastMonth->format('Y-m');

        $classes = GenerationClass::where('class_name', '!=', 'Graduate')->get();
        $fileName = "Attendance-{$monthString}.xlsx";
        $fileNameAdvanced = "Attendance-Advanced-{$monthString}.xlsx";
        $filePath = "attendances/{$fileName}";
        $filePathAdvanced = "attendances/{$fileNameAdvanced}";

        Excel::store(new AttendanceExport($classes, $monthString), $filePath);
        Excel::store(new AttendanceAdvancedExport($classes, $monthString), $filePathAdvanced);

        AttendanceReport::create([
            'file_name' => $fileName,
            'file_path' => $filePath
        ]);

        AttendanceReport::create([
            'file_name' => $fileNameAdvanced,
            'file_path' => $filePathAdvanced
        ]);

        $this->info("Data kehadiran untuk {$monthString} berhasil diekspor sebagai {$fileName}.");
    }
}

