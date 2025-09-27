<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Manajemen Kehadiran';
    protected static ?string $navigationLabel = 'Kehadiran';

    protected static ?string $modelLabel = 'Kehadiran';
    protected static ?string $pluralModelLabel = 'Kehadiran';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('student_name')
                    ->label('Nama Siswa')
                    ->disabled()
                    ->dehydrated(false)
                    ->afterStateHydrated(
                        fn($set, $record) =>
                        $set('student_name', $record?->student?->user?->name)
                    ),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'ontime' => 'Tepat Waktu',
                        'late' => 'Terlambat',
                        'permit' => 'Izin',
                        'sick' => 'Sakit',
                        'absent' => 'Tidak Hadir'
                    ])
                    ->required(),

                Forms\Components\DateTimePicker::make('check_in_time')
                    ->label('Waktu Masuk')
                    ->nullable()
                    ->suffixAction(
                        Forms\Components\Actions\Action::make('clear_check_in')
                            ->icon('heroicon-o-x-mark')
                            ->tooltip('Kosongkan Waktu Masuk')
                            ->action(fn($set) => $set('check_in_time', null))
                    ),

                Forms\Components\DateTimePicker::make('check_out_time')
                    ->label('Waktu Keluar')
                    ->nullable()
                    ->suffixAction(
                        Forms\Components\Actions\Action::make('clear_check_out')
                            ->icon('heroicon-o-x-mark')
                            ->tooltip('Kosongkan Waktu Keluar')
                            ->action(fn($set) => $set('check_out_time', null))
                    ),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.user.name')
                    ->label('Nama Siswa')
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.generationClass.class_name')
                    ->label('Kelas')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'ontime' => 'success',
                        'late' => 'warning',
                        'permit' => 'info',
                        'sick' => 'danger',
                        'absent' => 'danger',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'ontime' => 'Tepat Waktu',
                        'late' => 'Terlambat',
                        'permit' => 'Izin',
                        'sick' => 'Sakit',
                        'absent' => 'Tidak Hadir',
                    }),
                Tables\Columns\TextColumn::make('check_in_time')
                    ->label('Waktu Masuk')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('check_out_time')
                    ->label('Waktu Keluar')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('generationClass')
                    ->label('Nama Kelas')
                    ->relationship('generationClass', 'class_name'),
                \Filament\Tables\Filters\Filter::make('check_in_time_range')
                    ->label('Filter Tanggal Masuk')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn($query, $date) => $query->whereDate('check_in_time', '>=', $date))
                            ->when($data['until'], fn($query, $date) => $query->whereDate('check_in_time', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Ubah'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAttendances::route('/'),
        ];
    }
}
