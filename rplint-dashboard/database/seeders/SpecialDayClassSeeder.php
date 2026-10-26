<?php

namespace Database\Seeders;

use App\Models\GenerationClass;
use App\Models\SpecialDay;
use App\Models\SpecialDayClass;
use Illuminate\Database\Seeder;

class SpecialDayClassSeeder extends Seeder
{
    public function run(): void
    {
        $specialDays = SpecialDay::all();
        $classes = GenerationClass::pluck('id');

        if ($specialDays->isEmpty() || $classes->isEmpty()) {
            $this->command->warn('Special day atau kelas belum tersedia untuk relasi.');
            return;
        }

        foreach ($specialDays as $specialDay) {
            $classIds = collect($classes->random(min(2, $classes->count())))->all();

            foreach ($classIds as $classId) {
                SpecialDayClass::updateOrCreate(
                    [
                        'special_day_id' => $specialDay->id,
                        'generation_class_id' => $classId,
                    ]
                );
            }
        }
    }
}
