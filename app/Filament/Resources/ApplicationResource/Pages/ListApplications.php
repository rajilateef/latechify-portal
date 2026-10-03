<?php

namespace App\Filament\Resources\ApplicationResource\Pages;

use App\Filament\Resources\ApplicationResource;
use App\Filament\Resources\CheckoutOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListApplications extends ListRecords
{
    protected static string $resource = ApplicationResource::class;

    public function getSubheading(): ?string
    {
        return 'Archive of the retired apply flow (closed 1 Oct 2026). New enrolments arrive through Checkout orders.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('checkoutOrders')
                ->label('Go to Checkout orders')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn () => CheckoutOrderResource::getUrl('index')),
        ];
    }
}
