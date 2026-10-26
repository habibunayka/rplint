<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Models\GenerationClass;
use App\Models\Student;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;
use Rawilk\FilamentPasswordInput\Password;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Manajemen Kelas';
    protected static ?string $navigationLabel = 'Siswa';
    protected static ?string $modelLabel = 'Siswa';
    protected static ?string $pluralModelLabel = 'Siswa';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make([
                    TextInput::make('user.name')
                        ->label('Nama Lengkap')
                        ->autocomplete('off')
                        ->required(),
                    TextInput::make('user.email')
                        ->label('Email')
                        ->autocomplete('off')
                        ->nullable()
                        ->email()
                        ->unique(User::class, 'email', fn($record) => $record?->user),

                    Password::make('user.password')
                        ->label('Password')
                        ->autocomplete('off')
                        ->required(fn(string $context) => $context === 'create'),
                ])->columns(1)->columnSpanFull(),

                Select::make('generation_class_id')
                    ->label('Nama Angkatan')
                    ->options(fn() => static::getClassOptions('generation_name'))
                    ->relationship(
                        'generationClass',
                        'generation_name',
                        fn(Builder $query) => static::scopeClassesForUser($query)
                    )
                    ->required()
                    ->disabled(fn() => Auth::check() && Auth::user()->role === 'homeroom_teacher')
                    ->columnSpanFull(),


                TextInput::make('nis')
                    ->label('NIS')
                    ->required()
                    ->unique(Student::class, 'nis', fn($record) => $record),

                TextInput::make('nisn')
                    ->label('NISN')
                    ->required()
                    ->unique(Student::class, 'nisn', fn($record) => $record),

                TextInput::make('absence')
                    ->label('Nomor Absen')
                    ->numeric()
                    ->required(),

                TextInput::make('phone_number')
                    ->label('Nomor Telepon'),

                TextInput::make('address')
                    ->label('Alamat')
                    ->columnSpanFull(),

                FileUpload::make('profile_photo')
                    ->label('Foto Profil')
                    ->image()
                    ->disk('public')
                    ->directory('profile_photos')
                    ->imageEditor()
                    ->imageCropAspectRatio('1:1')
                    ->preserveFilenames()
                    ->columnSpanFull(),
            ]);
    }

    public static function afterCreate(Student $record, array $data): Student
    {
        if ($record->user_id) {
            return $record;
        }

        if (! isset($data['user'])) {
            return $record;
        }

        $user = User::create([
            'name' => $data['user']['name'],
            'email' => $data['user']['email'],
            'password' => $data['user']['password'],
            'role' => 'student',
        ]);

        $record->user()->associate($user);
        $record->save();

        return $record;
    }

    public static function afterUpdate(Student $record, array $data): Student
    {
        $user = $record->user;

        if (! $user) {
            return $record;
        }

        if (isset($data['user']['password']) && $data['user']['password']) {
            $user->password = $data['user']['password'];
        }

        if (isset($data['user']['name'])) {
            $user->name = $data['user']['name'];
        }

        if (isset($data['user']['email'])) {
            $user->email = $data['user']['email'];
        }

        $user->role = 'student';
        $user->save();

        return $record;
    }

    public static function table(Table $table): Table
    {
        $isAdmin = Auth::check() && Auth::user()->role === 'admin';

        return $table
            ->columns([
                ImageColumn::make('profile_photo')
                    ->label('Foto Profil')
                    ->disk('public')
                    ->width(50)
                    ->height(50),

                TextColumn::make('user.name')
                    ->label('Nama Lengkap')
                    ->searchable(),

                TextColumn::make('generationClass.class_name')
                    ->label('Kelas'),

                TextColumn::make('nis')
                    ->label('NIS'),

                TextColumn::make('nisn')
                    ->label('NISN'),
            ])
            ->filters([
                SelectFilter::make('generation_class_id')
                    ->label('Nama Kelas')
                    ->relationship(
                        'generationClass',
                        'class_name',
                        fn(Builder $query) => static::scopeClassesForUser($query)
                    )
                    ->options(fn() => static::getClassOptions('class_name'))
            ])
            ->actions([
                ...array_filter([
                    $isAdmin ? Tables\Actions\EditAction::make() : null,
                    $isAdmin ? Tables\Actions\DeleteAction::make() : null,
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with('user');

        if (Auth::check() && Auth::user()->role === 'homeroom_teacher') {
            $classIds = GenerationClass::query()
                ->where('homeroom_teacher_id', Auth::id())
                ->pluck('id');

            if ($classIds->isEmpty()) {
                $query->whereRaw('0 = 1');
            } else {
                $query->whereIn('generation_class_id', $classIds);
            }
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
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

    public static function shouldRegisterNavigation(): bool
    {
        if (app()->runningInConsole()) {
            return true;
        }

        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return in_array($user->role, ['admin', 'homeroom_teacher']);
    }

    protected static function scopeClassesForUser(Builder $query): Builder
    {
        if (Auth::check() && Auth::user()->role === 'homeroom_teacher') {
            $query->where('homeroom_teacher_id', Auth::id());
        }

        return $query;
    }

    protected static function getClassOptions(string $column): array
    {
        return static::scopeClassesForUser(GenerationClass::query())
            ->pluck($column, 'id')
            ->toArray();
    }
}
