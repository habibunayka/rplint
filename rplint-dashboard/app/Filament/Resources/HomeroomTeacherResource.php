<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomeroomTeacherResource\Pages;
use App\Models\GenerationClass;
use App\Models\User;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Rawilk\FilamentPasswordInput\Password;

class HomeroomTeacherResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationGroup = 'Manajemen Kelas';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Wali Kelas';
    protected static ?string $modelLabel = 'Wali Kelas';
    protected static ?string $pluralModelLabel = 'Wali Kelas';
    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('role')
                    ->default('homeroom_teacher')
                    ->dehydrated(true),
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required(),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(User::class, 'email', ignoreRecord: true),
                Password::make('password')
                    ->label('Password')
                    ->autocomplete('new-password')
                    ->required(fn(string $context): bool => $context === 'create')
                    ->dehydrateStateUsing(fn(?string $state) => $state ? Hash::make($state) : null)
                    ->dehydrated(fn(?string $state) => filled($state)),
                Select::make('class_id')
                    ->label('Kelas yang Diampu')
                    ->options(fn(?User $record) => static::getClassOptions($record))
                    ->default(fn(?User $record) => $record?->homeroomClasses()->value('id'))
                    ->searchable()
                    ->preload()
                    ->placeholder('Pilih kelas')
                    ->columnSpanFull()
                    ->helperText('Pilih satu kelas yang diampu oleh wali kelas ini.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        $isAdmin = Auth::check() && Auth::user()->role === 'admin';

        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Lengkap')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('homeroomClasses.class_name')
                    ->label('Kelas yang Diampu')
                    ->badge()
                    ->separator(', '),
            ])
            ->filters([])
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHomeroomTeachers::route('/'),
            'create' => Pages\CreateHomeroomTeacher::route('/create'),
            'edit' => Pages\EditHomeroomTeacher::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'homeroom_teacher');
    }

    public static function canCreate(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    public static function canViewAny(): bool
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

    public static function syncHomeroomClass(User $record, ?int $classId): void
    {
        GenerationClass::query()
            ->where('homeroom_teacher_id', $record->id)
            ->when($classId !== null, fn($query) => $query->where('id', '!=', $classId))
            ->update(['homeroom_teacher_id' => null]);

        if ($classId !== null) {
            GenerationClass::query()
                ->where('id', $classId)
                ->update(['homeroom_teacher_id' => $record->id]);
        }
    }

    protected static function getClassOptions(?User $record = null): array
    {
        return GenerationClass::query()
            ->where(function ($query) use ($record) {
                $query->whereNull('homeroom_teacher_id');

                if ($record) {
                    $query->orWhere('homeroom_teacher_id', $record->id);
                }
            })
            ->orderBy('generation_name')
            ->orderBy('class_name')
            ->pluck('class_name', 'id')
            ->toArray();
    }

    public static function shouldRegisterNavigation(): bool
    {
        if (app()->runningInConsole()) {
            return true;
        }

        $user = Auth::user();

        return $user?->role === 'admin';
    }
}
