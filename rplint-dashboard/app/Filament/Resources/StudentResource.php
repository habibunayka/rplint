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
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
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
                    ->options(GenerationClass::all()->pluck('generation_name', 'id')->toArray())
                    ->relationship('generationClass', 'generation_name')
                    ->required()
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
        $user = User::create([
            'name' => $data['user']['name'],
            'email' => $data['user']['email'],
            'password' => Hash::make($data['user']['password']),
        ]);

        $record->user()->associate($user);
        $record->save();

        return $record;
    }

    public static function afterUpdate(Student $record, array $data): Student
    {
        if (isset($data['user']['password']) && $data['user']['password']) {
            $record->user->password = Hash::make($data['user']['password']);
        }

        if (isset($data['user']['name'])) {
            $record->user->name = $data['user']['name'];
        }

        if (isset($data['user']['email'])) {
            $record->user->email = $data['user']['email'];
        }

        $record->user->save();

        return $record;
    }

    public static function table(Table $table): Table
    {
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
                    ->options(
                        GenerationClass::all()
                            ->pluck('class_name', 'id')
                            ->toArray()
                    )
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
