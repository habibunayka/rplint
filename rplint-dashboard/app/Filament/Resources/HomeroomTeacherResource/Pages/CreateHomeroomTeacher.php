<?php

namespace App\Filament\Resources\HomeroomTeacherResource\Pages;

use App\Filament\Resources\HomeroomTeacherResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHomeroomTeacher extends CreateRecord
{
    protected static string $resource = HomeroomTeacherResource::class;
    protected ?int $classId = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->classId = isset($data['class_id']) ? (int) $data['class_id'] : null;

        unset($data['class_id']);
        $data['role'] = 'homeroom_teacher';

        return $data;
    }

    protected function afterCreate(): void
    {
        HomeroomTeacherResource::syncHomeroomClass($this->record, $this->classId);
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
