<?php

namespace Database\Seeders;

use App\Models\AttendanceReport;
use Illuminate\Database\Seeder;

class AttendanceReportSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            AttendanceReport::updateOrCreate(
                ['file_name' => "laporan-kehadiran-{$i}.pdf"],
                [
                    'file_path' => "attendance/reports/laporan-kehadiran-{$i}.pdf",
                ]
            );
        }
    }
}
