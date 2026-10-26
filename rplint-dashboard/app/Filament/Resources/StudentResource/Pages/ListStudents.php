<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use App\Imports\StudentsImport;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        if (! Auth::check() || Auth::user()->role !== 'admin') {
            return [];
        }

        return [
            Actions\CreateAction::make(),
            Action::make('downloadTemplate')
                ->label('Download Template')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $fileName = 'students-template.xlsx';
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

                    Excel::import(new StudentsImport(), $file);
                    Notification::make()
                        ->title('Import Siswa')
                        ->body('Siswa berhasil diimport')
                        ->success()
                        ->send();
                })
                ->modalHeading('Import Siswa')
                ->modalDescription('Import siswa dari file Excel'),
        ];
    }
}
