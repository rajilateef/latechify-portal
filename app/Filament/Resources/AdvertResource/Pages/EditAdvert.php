<?php

namespace App\Filament\Resources\AdvertResource\Pages;

use App\Filament\Resources\AdvertResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAdvert extends EditRecord
{
    protected static string $resource = AdvertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
