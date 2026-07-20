<?php

namespace App\Filament\Resources\CampRegistrationResource\Pages;

use App\Filament\Resources\CampRegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCampRegistrations extends ListRecords
{
    protected static string $resource = CampRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
