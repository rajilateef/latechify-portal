<?php

namespace App\Filament\Resources\CheckoutOrderResource\Pages;

use App\Filament\Resources\CheckoutOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCheckoutOrder extends EditRecord
{
    protected static string $resource = CheckoutOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
