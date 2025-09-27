<?php

namespace App\Filament\Resources\GenerationClassResource\Pages;

use App\Filament\Resources\GenerationClassResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageGenerationClasses extends ManageRecords
{
    protected static string $resource = GenerationClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
