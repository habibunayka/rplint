<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $userData = $data['user'];
        unset($data['user']);

        $user = $this->record->user;
        $user->update([
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);

        if (!empty($userData['password'])) {
            $user->update([
                'password' => Hash::make($userData['password']),
            ]);
        }

        return $data;
    }

    protected function fillForm(): void
    {
        $data = $this->record->toArray();
        $data['user'] = $this->record->user->toArray();

        $this->form->fill($data);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
