<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $userData = $data['user'];
        unset($data['user']);

        $user = User::create([
            'name' => $userData['name'] ?? '-',
            'email' => $userData['email'] ?? uniqid() . '@example.com',
            'password' => Hash::make($userData['password'] ?? 'password123'),
        ]);

        $data['user_id'] = $user->id;

        return $data;
    }
}
