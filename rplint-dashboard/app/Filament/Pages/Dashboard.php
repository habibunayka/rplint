<?php

namespace App\Filament\Pages;

use App\Filament\Resources\AttendanceResource\Widgets\AttendanceWidget;
use App\Models\GenerationClass;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\WidgetConfiguration;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    /**
     * @return array<int, WidgetConfiguration>
     */
    protected function getHeaderWidgets(): array
    {
        $query = GenerationClass::query()
            ->where('class_name', '!=', 'Graduate');

        $user = Auth::user();

        if ($user && $user->role === 'homeroom_teacher') {
            $query->where('homeroom_teacher_id', $user->id);
        }

        return $query
            ->get()
            ->map(fn (GenerationClass $class): WidgetConfiguration => AttendanceWidget::make([
                'classId' => $class->id,
            ]))
            ->all();
    }
}
