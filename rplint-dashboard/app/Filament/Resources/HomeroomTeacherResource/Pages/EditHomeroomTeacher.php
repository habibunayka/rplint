<?php

namespace App\Filament\Resources\HomeroomTeacherResource\Pages;

use App\Filament\Resources\HomeroomTeacherResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHomeroomTeacher extends EditRecord
{
    protected static string $resource = HomeroomTeacherResource::class;
    protected ?int $classId = null;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->classId = isset($data['class_id']) ? (int) $data['class_id'] : null;

        unset($data['class_id']);
        $data['role'] = 'homeroom_teacher';

        return $data;
    }

    protected function afterSave(): void
    {
        HomeroomTeacherResource::syncHomeroomClass($this->record, $this->classId);
    }

    protected function getHeaderActions(): array
    {
        if (! auth()->check() || auth()->user()->role !== 'admin') {
            return [];
        }

        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function authorizeAccess(): void
    {
        parent::authorizeAccess();

        abort_unless(
            auth()->check() && auth()->user()->role === 'admin',
            403
        );
    }
}
