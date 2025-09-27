<?php

namespace Database\Seeders;

use App\Models\GenerationClass;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GenerationClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $generations = [
            [
                'generation_name' => "Angkatan 24A",
                'class_name' => '12 RPL 1',
                'class_teacher' => 'Pak A',
            ],
            [
                'generation_name' => "Angkatan 24B",
                'class_name' => '12 RPL 2',
                'class_teacher' => 'Pak B',
            ],
            [
                'generation_name' => "Angkatan 25A",
                'class_name' => '11 RPL 1',
                'class_teacher' => 'Pak C',
            ],
            [
                'generation_name' => "Angkatan 25B",
                'class_name' => '11 RPL 2',
                'class_teacher' => 'Pak D',
            ],
            [
                'generation_name' => "Angkatan 26A",
                'class_name' => '10 RPL 1',
                'class_teacher' => 'Pak E',
            ],
            [
                'generation_name' => "Angkatan 26B",
                'class_name' => '10 RPL 2',
                'class_teacher' => 'Pak F',
            ],
        ];


        foreach ($generations as $generation) {
            GenerationClass::create($generation);
        }
    }
}
