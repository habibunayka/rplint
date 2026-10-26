<?php

namespace App\Filament\Resources\HomeroomTeacherResource\Pages;

use App\Filament\Resources\HomeroomTeacherResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListHomeroomTeachers extends ListRecords
{
    protected static string $resource = HomeroomTeacherResource::class;

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
