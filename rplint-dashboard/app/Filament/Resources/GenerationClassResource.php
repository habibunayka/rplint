<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GenerationClassResource\Pages;
use App\Models\GenerationClass;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class GenerationClassResource extends Resource
{
    protected static ?string $model = GenerationClass::class;
    protected static ?string $navigationGroup = 'Manajemen Kelas';
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Kelas';
    protected static ?string $modelLabel = 'Kelas';
    protected static ?string $pluralModelLabel = 'Kelas';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('generation_name')
                    ->required()
                    ->label('Nama Angkatan')
                    ->columnSpanFull(),
                Forms\Components\Select::make('class_name')
                    ->options([
                        '10 RPL 1' => '10 RPL 1',
                        '10 RPL 2' => '10 RPL 2',
                        '11 RPL 1' => '11 RPL 1',
                        '11 RPL 2' => '11 RPL 2',
                        '12 RPL 1' => '12 RPL 1',
                        '12 RPL 2' => '12 RPL 2',
                        'Graduate' => 'Alumni',
                    ])
                    ->required()
                    ->label('Nama Kelas'),
                Forms\Components\Select::make('homeroom_teacher_id')
                    ->label('Wali Kelas')
                    ->searchable()
                    ->options(fn(?GenerationClass $record) => static::getAvailableHomeroomTeachers($record))
                    ->hint('Pilih wali kelas yang telah terdaftar')
                    ->default(null)
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $isAdmin = Auth::check() && Auth::user()->role === 'admin';
        $isHomeroom = Auth::check() && Auth::user()->role === 'homeroom_teacher';

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('generation_name')
                    ->label('Nama Angkatan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('class_name')
                    ->label('Nama Kelas')
                    ->searchable(),
                Tables\Columns\TextColumn::make('homeroomTeacher.name')
                    ->label('Wali Kelas')
                    ->formatStateUsing(fn($state, GenerationClass $record) => $state ?? $record->class_teacher ?? '-')
                    ->searchable(),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('id')
                    ->label('Kelas')
                    ->options(fn() => static::getClassOptions()),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageGenerationClasses::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    public static function canViewAny(): bool
    {
        return Auth::check() && in_array(Auth::user()->role, ['admin', 'homeroom_teacher']);
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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (Auth::check() && Auth::user()->role === 'homeroom_teacher') {
            $query->where('homeroom_teacher_id', Auth::id());
        }

        return $query;
    }

    protected static function getClassOptions(): array
    {
        return static::getScopedClasses()
            ->where('class_name', '!=', 'Graduate')
            ->pluck('class_name', 'id')
            ->toArray();
    }

    protected static function getScopedClasses()
    {
        $query = GenerationClass::query();

        if (Auth::check() && Auth::user()->role === 'homeroom_teacher') {
            $query->where('homeroom_teacher_id', Auth::id());
        }

        return $query;
    }

    protected static function getAvailableHomeroomTeachers(?GenerationClass $record = null): array
    {
        return User::query()
            ->where('role', 'homeroom_teacher')
            ->where(function ($query) use ($record) {
                $query->whereDoesntHave('homeroomClasses');

                if ($record?->homeroom_teacher_id) {
                    $query->orWhere('id', $record->homeroom_teacher_id);
                }
            })
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public static function shouldRegisterNavigation(): bool
    {
        if (app()->runningInConsole()) {
            return true;
        }

        return Auth::user() && in_array(Auth::user()->role, ['admin', 'homeroom_teacher']);
    }
}
