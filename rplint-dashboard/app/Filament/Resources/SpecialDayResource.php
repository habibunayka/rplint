<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SpecialDayResource\Pages;
use App\Filament\Resources\SpecialDayResource\RelationManagers;
use App\Models\SpecialDay;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SpecialDayResource extends Resource
{
    protected static ?string $model = SpecialDay::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Hari Khusus';
    protected static ?string $navigationGroup = 'Manajemen Kehadiran';
    protected static ?string $modelLabel = 'Hari Khusus';
    protected static ?string $pluralModelLabel = 'Hari Khusus';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('description')
                    ->label('Deskripsi')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'ontime' => 'Hadir',
                        'permit' => 'Izin',
                        'sick' => 'Sakit',
                        'holiday' => 'Libur'
                    ])
                    ->required(),

                Forms\Components\DatePicker::make('date_start')
                    ->label('Tanggal Mulai')
                    ->required(),

                Forms\Components\DatePicker::make('date_end')
                    ->label('Tanggal Selesai')
                    ->required()
                    ->afterOrEqual('date_start'),

                Forms\Components\Select::make('student_type')
                    ->label('Tipe Siswa')
                    ->options([
                        'all' => 'Semua Siswa',
                        'selected' => 'Siswa Tertentu',
                        'class' => 'Kelas Tertentu'
                    ])
                    ->default('all')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state === 'all') {
                            $set('students', []);
                            $set('classes', []);
                        }
                    }),

                Forms\Components\Select::make('students')
                    ->label('Pilih Siswa')
                    ->multiple()
                    ->relationship('students', 'user.name')
                    ->visible(fn(callable $get) => $get('student_type') === 'selected')
                    ->searchable()
                    ->preload()
                    ->options(function () {
                        return \App\Models\Student::with('user')
                            ->get()
                            ->mapWithKeys(fn($student) => [$student->id => $student->user->name ?? 'Tanpa Nama']);
                    })

                    ->loadStateFromRelationshipsUsing(function ($component, $state) {
                        $component->state(
                            $component->getRecord()->students->pluck('id')->toArray()
                        );
                    }),

                Forms\Components\Select::make('classes')
                    ->label('Pilih Kelas')
                    ->multiple()
                    ->relationship('classes', 'class_name')
                    ->visible(fn(callable $get) => $get('student_type') === 'class')
                    ->searchable()
                    ->preload()
                    ->options(function (callable $get) {
                        return \App\Models\GenerationClass::pluck('class_name', 'id');
                    })
                    ->loadStateFromRelationshipsUsing(function ($component, $state) {
                        $component->state(
                            $component->getRecord()->classes->pluck('id')->toArray()
                        );
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'ontime' => 'Hadir',
                        'permit' => 'Izin',
                        'sick' => 'Sakit',
                        'holiday' => 'Libur'
                    })
                    ->colors([
                        'success' => fn ($state) => $state === 'ontime',
                        'warning' => fn ($state) => $state === 'permit',
                        'danger' => fn ($state) => $state === 'sick',
                        'primary' => fn ($state) => $state === 'holiday',
                    ]),
                Tables\Columns\TextColumn::make('student_type')
                    ->label('Tipe Siswa')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'all' => 'Semua Siswa',
                        'selected' => 'Siswa Tertentu',
                        'class' => 'Kelas Tertentu'
                    }),
                Tables\Columns\TextColumn::make('date_start')
                    ->label('Tanggal Mulai')
                    ->date(),
                Tables\Columns\TextColumn::make('date_end')
                    ->label('Tanggal Selesai')
                    ->date(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ManageSpecialDays::route('/'),
        ];
    }
}
