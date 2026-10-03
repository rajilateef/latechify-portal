<?php

namespace App\Filament\Resources\MaterialFileResource\Pages;

use App\Filament\Resources\MaterialFileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMaterialFile extends EditRecord
{
    protected static string $resource = MaterialFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
