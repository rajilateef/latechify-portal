<?php

namespace App\Filament\Resources\CohortActivityResource\Pages;

use App\Filament\Resources\CohortActivityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCohortActivity extends EditRecord
{
    protected static string $resource = CohortActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
