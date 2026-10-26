<?php

namespace App\Filament\Resources\GenerationClassResource\Pages;

use App\Filament\Resources\GenerationClassResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Auth;

class ManageGenerationClasses extends ManageRecords
{
    protected static string $resource = GenerationClassResource::class;

    protected function getHeaderActions(): array
    {
        if (! Auth::check() || Auth::user()->role !== 'admin') {
            return [];
        }

        return [
            Actions\CreateAction::make(),
        ];
    }
}
