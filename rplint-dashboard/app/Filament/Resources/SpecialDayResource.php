<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SpecialDayResource\Pages;
use App\Filament\Resources\SpecialDayResource\RelationManagers;
use App\Models\GenerationClass;
use App\Models\SpecialDay;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
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
                    ->options(fn() => static::getStudentTypeOptions())
                    ->default(fn() => static::getDefaultStudentType())
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
                    ->relationship(
                        'students',
                        'user.name',
                        fn(Builder $query) => static::scopeStudentsForUser($query)
                    )
                    ->visible(fn(callable $get) => $get('student_type') === 'selected')
                    ->searchable()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn(Student $record) => $record->user->name ?? 'Tanpa Nama')

                    ->loadStateFromRelationshipsUsing(function ($component, $state) {
                        $component->state(
                            $component->getRecord()->students->pluck('id')->toArray()
                        );
                    }),

                Forms\Components\Select::make('classes')
                    ->label('Pilih Kelas')
                    ->multiple()
                    ->relationship(
                        'classes',
                        'class_name',
                        fn(Builder $query) => static::getAccessibleClassesQuery($query)
                    )
                    ->visible(fn(callable $get) => $get('student_type') === 'class')
                    ->searchable()
                    ->preload()
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
                ...array_filter([
                    Auth::check() && Auth::user()->role === 'admin' ? Tables\Actions\EditAction::make() : null,
                    Auth::check() && Auth::user()->role === 'admin' ? Tables\Actions\DeleteAction::make() : null,
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function getStudentTypeOptions(): array
    {
        if (! Auth::check()) {
            return [
                'all' => 'Semua Siswa',
                'selected' => 'Siswa Tertentu',
                'class' => 'Kelas Tertentu',
            ];
        }

        if (Auth::user()->role === 'homeroom_teacher') {
            return [
                'selected' => 'Siswa Tertentu',
                'class' => 'Kelas Tertentu',
            ];
        }

        return [
            'all' => 'Semua Siswa',
            'selected' => 'Siswa Tertentu',
            'class' => 'Kelas Tertentu',
        ];
    }

    protected static function getDefaultStudentType(): string
    {
        if (Auth::check() && Auth::user()->role === 'homeroom_teacher') {
            return 'selected';
        }

        return 'all';
    }

    protected static function scopeStudentsForUser(Builder $query): Builder
    {
        $query->with('user');

        if (Auth::check() && Auth::user()->role === 'homeroom_teacher') {
            $classIds = static::getAccessibleClassesQuery()->pluck('id');

            if ($classIds->isEmpty()) {
                return $query->whereRaw('0 = 1');
            }

            $query->whereIn('generation_class_id', $classIds);
        }

        return $query;
    }

    protected static function getAccessibleClassesQuery(?Builder $query = null): Builder
    {
        $query ??= GenerationClass::query();

        if (Auth::check() && Auth::user()->role === 'homeroom_teacher') {
            $query->where('homeroom_teacher_id', Auth::id());
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSpecialDays::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (Auth::check() && Auth::user()->role === 'homeroom_teacher') {
            $classIds = GenerationClass::query()
                ->where('homeroom_teacher_id', Auth::id())
                ->pluck('id');

            $query->where(function (Builder $inner) use ($classIds) {
                $inner->where('student_type', 'all')
                    ->orWhereHas('classes', fn(Builder $q) => $q->whereIn('generation_classes.id', $classIds))
                    ->orWhereHas('students', function (Builder $q) use ($classIds) {
                        $q->whereIn('generation_class_id', $classIds);
                    });
            });
        }

        return $query;
    }

    public static function canCreate(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    public static function canEdit($record): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    public static function canDelete($record): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    public static function canDeleteAny(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    public static function canViewAny(): bool
    {
        return Auth::check() && in_array(Auth::user()->role, ['admin', 'homeroom_teacher']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        if (app()->runningInConsole()) {
            return true;
        }

        return Auth::user() && in_array(Auth::user()->role, ['admin', 'homeroom_teacher']);
    }
}
