<?php

namespace App\Filament\Resources\TrainingCourseResource\Pages;

use App\Filament\Resources\TrainingCourseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTrainingCourse extends EditRecord
{
    protected static string $resource = TrainingCourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
