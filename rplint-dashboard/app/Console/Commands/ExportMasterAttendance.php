<?php

namespace App\Console\Commands;

use App\Exports\AttendanceAdvancedExport;
use Illuminate\Console\Command;
use App\Exports\AttendanceExport;
use App\Exports\AttendanceMasterExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Models\GenerationClass;
use App\Models\AttendanceReport;
use Carbon\Carbon;

class ExportMasterAttendance extends Command
{
    protected $signature = 'attendance:export-master';
    protected $description = 'Export attendance data for the previous years';

    public function handle()
    {
        $lastYear = Carbon::now()->subYear();
        $yearString = $lastYear->format('Y');

        $classes = GenerationClass::where('class_name', '!=', 'Graduate')->get();
        $fileName = "Attendance-{$yearString}.xlsx";
        $filePath = "attendances/{$fileName}";

        Excel::store(new AttendanceMasterExport($classes, $yearString), $filePath);

        AttendanceReport::create([
            'file_name' => $fileName,
            'file_path' => $filePath
        ]);

        $this->info("Data kehadiran untuk {$yearString} berhasil diekspor sebagai {$fileName}.");
    }
}

