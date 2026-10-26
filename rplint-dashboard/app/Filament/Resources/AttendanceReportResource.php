<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceReportResource\Pages;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

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
                    ->action(function ($record, Action $action) {
                        $filePath = $record->file_path ?? ('attendances/' . $record->file_name);

                        if (! Storage::disk('local')->exists($filePath)) {
                            $action->failure('File tidak tersedia untuk diunduh.');
                            return null;
                        }

                        return response()->download(
                            Storage::disk('local')->path($filePath),
                            $record->file_name
                        );
                    })
                    ->color('primary'),
                Action::make('delete')
                    ->label('Hapus')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $filePath = $record->file_path ?? ('attendances/' . $record->file_name);

                        if (Storage::disk('local')->exists($filePath)) {
                            Storage::disk('local')->delete($filePath);
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

    public static function canViewAny(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    public static function shouldRegisterNavigation(): bool
    {
        if (app()->runningInConsole()) {
            return true;
        }

        return Auth::user()?->role === 'admin';
    }
}
