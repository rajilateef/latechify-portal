<?php

namespace App\Filament\Resources\MaterialFolderResource\Pages;

use App\Filament\Resources\MaterialFolderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMaterialFolder extends EditRecord
{
    protected static string $resource = MaterialFolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
