<?php

namespace App\Imports;

use App\Models\Schedule;
use App\Models\GenerationClass;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;

class ScheduleImport implements ToModel, WithHeadingRow
{
    use Importable;

    public function model(array $row)
    {
        $generationClass = GenerationClass::where('class_name', $row['class_name'])->first();
        $generationClassId = $generationClass ? $generationClass->id : null;

        if (empty($row['teacher'])) {
            Log::warning('Teacher missing in row: ' . json_encode($row));
            return null;
        }

        if (empty($row['subject'])) {
            Log::warning('Subject missing in row: ' . json_encode($row));
            return null;
        }
        
        if (empty($row['day'])) {
            Log::warning('Day missing in row: ' . json_encode($row));
            return null;
        }

        if ($row['day'] == 'Senin') {
            $day = 'monday';
        } else if ($row['day'] == 'Selasa') {
            $day = 'tuesday';
        } else if ($row['day'] == 'Rabu') {
            $day = 'wednesday';
        } else if ($row['day'] == 'Kamis') {
            $day = 'thursday';
        } else if ($row['day'] == 'Jumat') {
            $day = 'friday';
        } else {
            Log::warning('Day is not acceptable in row: ' . json_encode($row));
            return null;
        }

        if (empty($row['type'])) {
            Log::warning('Type missing in row: ' . json_encode($row));
            return null;
        }


        return new Schedule([
            'generation_class_id' => $generationClassId,
            'teacher'             => $row['teacher'],
            'subject'             => $row['subject'],
            'day'                 => $day,
            'type'                => $row['type'],
            'start_time'          => $row['start_time'],
            'end_time'            => $row['end_time']
        ]);
    }
}
