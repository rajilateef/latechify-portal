<?php

namespace App\Filament\Resources\MaterialFileResource\Pages;

use App\Filament\Resources\MaterialFileResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMaterialFiles extends ListRecords
{
    protected static string $resource = MaterialFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
