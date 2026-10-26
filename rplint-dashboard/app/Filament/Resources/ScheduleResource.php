<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleResource\Pages;
use App\Filament\Resources\ScheduleResource\RelationManagers;
use App\Models\GenerationClass;
use App\Models\Schedule;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Manajemen Mata Pelajaran';
    protected static ?string $navigationLabel = 'Jadwal Pelajaran';
    protected static ?string $pluralModelLabel = 'Jadwal';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('generation_class_id')
                    ->label('Nama Angkatan')
                    ->options(fn() => static::getClassOptions('generation_name'))
                    ->relationship('generationClass', 'generation_name')
                    ->placeholder('Pilih Angkatan')
                    ->columnSpanFull()
                    ->required(),
                Forms\Components\Select::make('type')
                    ->required()
                    ->options([
                        'productive' => 'Produktif',
                        'theory' => 'Teori',
                    ])
                    ->label('Tipe Mata Pelajaran')
                    ->placeholder('Pilih Tipe Mata Pelajaran')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('teacher')
                    ->required()
                    ->maxLength(255)
                    ->label('Guru Mata Pelajaran'),
                Forms\Components\TextInput::make('subject')
                    ->required()
                    ->maxLength(255)
                    ->label('Mata Pelajaran'),
                Forms\Components\Select::make('day')
                    ->required()
                    ->options([
                        'monday' => 'Senin',
                        'tuesday' => 'Selasa',
                        'wednesday' => 'Rabu',
                        'thursday' => 'Kamis',
                        'friday' => 'Jumat',
                    ]),
                Forms\Components\TimePicker::make('start_time')
                    ->required()
                    ->label('Waktu Mulai'),
                Forms\Components\TimePicker::make('end_time')
                    ->required()
                    ->label('Waktu Selesai'),
            ]);
    }

    public static function table(Table $table): Table
    {
        $isAdmin = Auth::check() && Auth::user()->role === 'admin';
        $isHomeroom = Auth::check() && Auth::user()->role === 'homeroom_teacher';

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('generationClass.class_name')
                ->label('Kelas'),
                Tables\Columns\TextColumn::make('teacher')
                    ->label('Guru')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject')
                    ->label('Mata Pelajaran')
                    ->searchable(),
                Tables\Columns\TextColumn::make('day')
                    ->label('Hari')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'monday' => 'Senin',
                        'tuesday' => 'Selasa',
                        'wednesday' => 'Rabu',
                        'thursday' => 'Kamis',
                        'friday' => 'Jumat',
                        default => $state,
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'productive' => 'Produktif',
                        'theory' => 'Teori',
                        default => $state,
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Waktu Mulai'),
                Tables\Columns\TextColumn::make('end_time')
                    ->label('Waktu Selesai'),
            ])
            ->filters([
                SelectFilter::make('generation_class_id')
                    ->label('Angkatan')
                    ->options(fn() => static::getClassOptions('class_name')),
            ])
            ->actions([
                ...array_filter([
                    ($isAdmin || $isHomeroom)
                        ? Tables\Actions\EditAction::make()
                            ->visible(fn(Schedule $record) => static::userCanManageRecord($record))
                        : null,
                    $isAdmin
                        ? Tables\Actions\DeleteAction::make()
                        : ($isHomeroom
                            ? Tables\Actions\DeleteAction::make()
                                ->visible(fn(Schedule $record) => static::userCanManageRecord($record))
                            : null),
                ]),
            ])
            ->bulkActions([
                ...(
                    $isAdmin
                        ? [
                            Tables\Actions\BulkActionGroup::make([
                                Tables\Actions\DeleteBulkAction::make(),
                            ]),
                        ]
                        : []
                ),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSchedules::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (Auth::check() && Auth::user()->role === 'homeroom_teacher') {
            $query->whereIn('generation_class_id', static::getAccessibleClassIds());
        }

        return $query;
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();

        return $user && in_array($user->role, ['admin', 'homeroom_teacher']);
    }

    public static function canEdit($record): bool
    {
        if (! $record instanceof Schedule) {
            return false;
        }

        return static::userCanManageRecord($record);
    }

    public static function canDelete($record): bool
    {
        if (! $record instanceof Schedule) {
            return false;
        }

        $user = Auth::user();

        if ($user && $user->role === 'admin') {
            return true;
        }

        if ($user && $user->role === 'homeroom_teacher') {
            return static::getAccessibleClassIds()->contains($record->generation_class_id);
        }

        return false;
    }

    public static function canDeleteAny(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    public static function shouldRegisterNavigation(): bool
    {
        if (app()->runningInConsole()) {
            return true;
        }

        return Auth::user() && in_array(Auth::user()->role, ['admin', 'homeroom_teacher']);
    }

    protected static function getClassOptions(string $column): array
    {
        return static::scopeClassesForUser(GenerationClass::query())
            ->where('class_name', '!=', 'Graduate')
            ->pluck($column, 'id')
            ->toArray();
    }

    protected static function getAccessibleClassIds()
    {
        return static::scopeClassesForUser(GenerationClass::query())->pluck('id');
    }

    protected static function scopeClassesForUser(Builder $query): Builder
    {
        if (Auth::check() && Auth::user()->role === 'homeroom_teacher') {
            $query->where('homeroom_teacher_id', Auth::id());
        }

        return $query;
    }

    protected static function userCanManageRecord(?Schedule $record = null): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role !== 'homeroom_teacher') {
            return false;
        }

        if ($record === null) {
            return static::getAccessibleClassIds()->isNotEmpty();
        }

        return static::getAccessibleClassIds()->contains($record->generation_class_id);
    }
}
