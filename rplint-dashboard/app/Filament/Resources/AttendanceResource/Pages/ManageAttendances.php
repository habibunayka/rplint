<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageAttendances extends ManageRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Kehadiran')
                ->modalHeading('Tambah Kehadiran')
                ->authorize(fn() => \App\Filament\Resources\AttendanceResource::canCreate())
                ->visible(fn() => \App\Filament\Resources\AttendanceResource::canCreate())
                ->createAnother(false),
        ];
    }
}
