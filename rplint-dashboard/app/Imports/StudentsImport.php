<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\User;
use App\Models\GenerationClass;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;

class StudentsImport implements ToModel, WithHeadingRow
{
    use Importable;

    public function model(array $row)
    {
        if (empty($row['full_name'])) {
            Log::warning('Nama siswa kosong: ' . json_encode($row));
            return null;
        }

        if (empty($row['absence'])) {
            Log::warning('Nomor absen kosong: ' . json_encode($row));
            return null;
        }

        $generationClass = GenerationClass::where('class_name', $row['class_name'])->first();
        if (!$generationClass) {
            Log::warning('Angkatan/Kelas tidak ditemukan: ' . json_encode($row));
            return null;
        }

        $user = User::create([
            'name' => $row['full_name'],
            'email' => $row['email'] ?? uniqid().'@mail.com',
            'password' => Hash::make($row['password'] ?? 'password123'),
        ]);

        return new Student([
            'user_id' => $user->id,
            'generation_class_id' => $generationClass->id,
            'absence' => $row['absence'],
            'nis' => $row['nis'],
            'nisn' => $row['nisn'],
            'address' => $row['address'],
            'phone_number' => $row['phone_number'],
        ]);
    }
}
