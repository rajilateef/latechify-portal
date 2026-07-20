<?php

namespace App\Filament\Resources\CoreValueResource\Pages;

use App\Filament\Resources\CoreValueResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCoreValues extends ListRecords
{
    protected static string $resource = CoreValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
