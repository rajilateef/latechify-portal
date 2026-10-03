<?php

namespace App\Filament\Resources\TrainingCourseResource\Pages;

use App\Filament\Resources\TrainingCourseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTrainingCourses extends ListRecords
{
    protected static string $resource = TrainingCourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
