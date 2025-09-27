<?php

namespace App\Filament\Resources\ScheduleResource\Pages;

use App\Filament\Resources\ScheduleResource;
use App\Imports\ScheduleImport;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ManageSchedules extends ListRecords
{
    protected static string $resource = ScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Action::make('downloadTemplate')
                ->label('Download Template')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $fileName = 'schedules-template.xlsx';
                    $filePath = 'templates/' . $fileName;

                    if (!Storage::exists($filePath)) {
                        Notification::make()
                            ->title('Template tidak ditemukan')
                            ->danger()
                            ->send();
                        return;
                    }

                    return response()->download(Storage::path($filePath), $fileName);
                }),
            Action::make('import')
                ->label('Import')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    FileUpload::make('file')
                        ->label('File')
                        ->required()
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ]),
                ])
                ->action(function (array $data) {
                    $file = public_path('storage/' . $data['file']);

                    Excel::import(new ScheduleImport(), $file);
                    Notification::make()
                            ->title('Import Jadwal')
                            ->body('Jadwal berhasil diimport')
                            ->success()
                            ->send();
                })
                ->modalHeading('Import Jadwal')
                ->modalDescription('Import jadwal dari file Excel'),
        ];
    }
}
