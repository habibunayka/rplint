<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GenerationClassResource\Pages;
use App\Filament\Resources\GenerationClassResource\RelationManagers;
use App\Models\GenerationClass;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                Forms\Components\TextInput::make('class_teacher')
                    ->label('Wali Kelas'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('generation_name')
                    ->label('Nama Angkatan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('class_name')
                    ->label('Nama Kelas')
                    ->searchable(),
                Tables\Columns\TextColumn::make('class_teacher')
                    ->label('Wali Kelas')
                    ->searchable(),
                
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
            'index' => Pages\ManageGenerationClasses::route('/'),
        ];
    }
}
