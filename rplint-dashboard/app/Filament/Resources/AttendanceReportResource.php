<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceReportResource\Pages;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Storage;

class AttendanceReportResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Manajemen Kehadiran';
    protected static ?string $navigationLabel = 'Laporan Kehadiran';

    protected static ?string $modelLabel = 'Laporan Kehadiran';
    protected static ?string $pluralModelLabel = 'Laporan Kehadiran';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Form fields bisa ditambahkan di sini jika perlu
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('file_name')
                    ->label('Nama File')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
            ])
            ->actions([
                Action::make('download')
                    ->label('Download Excel')
                    ->action(function ($record) {
                        $fileName = $record->file_name;
                        $filePath = 'attendances/' . $fileName;

                        if (Storage::exists($filePath)) {
                            return response()->download(Storage::path($filePath), $fileName);
                        }

                        return $this->fail('File tidak tersedia untuk diunduh.');
                    })
                    ->color('primary'),
                Action::make('delete')
                    ->label('Hapus')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $filePath = 'attendances/' . $record->file_name;

                        if (Storage::exists($filePath)) {
                            Storage::delete($filePath);
                        }

                        $record->delete();
                    })
                    ->color('danger'),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAttendanceReports::route('/'),
        ];
    }
}
