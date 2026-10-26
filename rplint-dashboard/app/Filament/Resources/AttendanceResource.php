<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use App\Models\GenerationClass;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Closure;


class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Manajemen Kehadiran';
    protected static ?string $navigationLabel = 'Kehadiran';

    protected static ?string $modelLabel = 'Kehadiran';
    protected static ?string $pluralModelLabel = 'Kehadiran';

    protected static ?Collection $accessibleClassIds = null;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('student_id')
                    ->label('Siswa')
                    ->visible(fn(?Attendance $record) => $record === null)
                    ->required(fn(?Attendance $record) => $record === null)
                    ->dehydrated(fn(?Attendance $record) => $record === null)
                    ->searchable()
                    ->options(fn() => static::getStudentOptions())
                    ->getSearchResultsUsing(fn(string $search) => static::getStudentOptions($search))
                    ->getOptionLabelUsing(fn($value) => static::getStudentLabel($value))
                    ->rules([
                        fn(): Closure => function (string $attribute, $value, Closure $fail) {
                            if ($value === null) {
                                return;
                            }

                            if (! Student::whereKey($value)->exists()) {
                                $fail('Siswa tidak ditemukan.');
                                return;
                            }

                            if (! static::userCanAccessStudent((int) $value)) {
                                $fail('Anda tidak memiliki akses ke siswa yang dipilih.');
                            }
                        },
                    ]),


                Forms\Components\TextInput::make('student_name')
                    ->label('Nama Siswa')
                    ->disabled()
                    ->dehydrated(false)
                    ->visible(fn(?Attendance $record) => $record !== null)
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
                    )
                    ->required(),

                Forms\Components\DateTimePicker::make('check_out_time')
                    ->label('Waktu Keluar')
                    ->nullable()
                    ->suffixAction(
                        Forms\Components\Actions\Action::make('clear_check_out')
                            ->icon('heroicon-o-x-mark')
                            ->tooltip('Kosongkan Waktu Keluar')
                            ->action(fn($set) => $set('check_out_time', null))
                    )
                    ->required(),
            ]);
    }


    public static function table(Table $table): Table
    {
        $isAdmin = Auth::check() && Auth::user()->role === 'admin';
        $isHomeroom = Auth::check() && Auth::user()->role === 'homeroom_teacher';

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
                    ->relationship(
                        'generationClass',
                        'class_name',
                        fn(Builder $query) => static::scopeClassesForUser($query)
                    )
                    ->options(fn() => static::getClassOptions()),
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
                ...array_filter([
                    ($isAdmin || $isHomeroom)
                        ? Tables\Actions\EditAction::make()
                        ->label('Ubah')
                        ->visible(fn(Attendance $record): bool => static::userCanManageRecord($record))
                        : null,
                    $isAdmin ? Tables\Actions\DeleteAction::make()->label('Hapus') : null,
                ]),
            ])
            ->bulkActions(
                $isAdmin
                    ? [
                        Tables\Actions\BulkActionGroup::make([
                            Tables\Actions\DeleteBulkAction::make(),
                        ]),
                    ]
                    : []
            );
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAttendances::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        static::$accessibleClassIds = null;

        $query = parent::getEloquentQuery();

        if (Auth::check() && Auth::user()->role === 'homeroom_teacher') {
            $classIds = static::scopeClassesForUser(GenerationClass::query())->pluck('id');

            if ($classIds->isEmpty()) {
                $query->whereRaw('0 = 1');
            } else {
                $query->whereHas('student', function (Builder $studentQuery) use ($classIds) {
                    $studentQuery->whereIn('generation_class_id', $classIds);
                });
            }
        }

        return $query->with(['student.user', 'student.generationClass']);
    }

    public static function canCreate(): bool
    {
        return static::userCanManageRecord();
    }

    public static function canEdit($record): bool
    {
        if (! $record instanceof Attendance) {
            return false;
        }

        return static::userCanManageRecord($record);
    }

    public static function canDelete($record): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
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

        $user = Auth::user();

        return $user && in_array($user->role, ['admin', 'homeroom_teacher']);
    }

    protected static function scopeClassesForUser(Builder $query): Builder
    {
        if (Auth::check() && Auth::user()->role === 'homeroom_teacher') {
            $query->where('homeroom_teacher_id', Auth::id());
        }

        return $query;
    }

    protected static function getClassOptions(): array
    {
        return static::scopeClassesForUser(GenerationClass::query())
            ->pluck('class_name', 'id')
            ->toArray();
    }

    protected static function getStudentOptions(?string $search = null): array
    {
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        $query = Student::query()->with('user');

        if ($user->role === 'homeroom_teacher') {
            $classIds = static::getAccessibleClassIds();

            if ($classIds->isEmpty()) {
                return [];
            }

            $query->whereIn('generation_class_id', $classIds->all());
        }

        if ($search) {
            $query->whereHas('user', function ($userQuery) use ($search) {
                $userQuery->where('name', 'like', "%{$search}%");
            });
        }

        return $query
            ->orderBy('id')
            ->limit(50)
            ->get()
            ->mapWithKeys(function (Student $student) {
                return [$student->id => $student->user->name ?? 'Tanpa Nama'];
            })
            ->toArray();
    }

    protected static function getStudentLabel($value): ?string
    {
        if (! $value) {
            return null;
        }

        return Student::query()
            ->with('user')
            ->find($value)?->user?->name;
    }

    protected static function userCanAccessStudent(int $studentId): bool
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

        return Student::where('id', $studentId)
            ->whereHas('generationClass', function (Builder $query) use ($user) {
                $query->where('homeroom_teacher_id', $user->id);
            })
            ->exists();
    }

    protected static function getAccessibleClassIds(): Collection
    {
        if (static::$accessibleClassIds !== null) {
            return static::$accessibleClassIds;
        }

        return static::$accessibleClassIds = static::scopeClassesForUser(GenerationClass::query())
            ->pluck('id');
    }

    protected static function userCanManageRecord(?Attendance $record = null): bool
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

        $classId = $record->student->generation_class_id ?? null;

        if ($classId === null) {
            return false;
        }

        return static::getAccessibleClassIds()->contains($classId);
    }
}
