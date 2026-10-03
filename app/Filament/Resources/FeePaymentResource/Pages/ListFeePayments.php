<?php

namespace App\Filament\Resources\FeePaymentResource\Pages;

use App\Filament\Resources\FeePaymentResource;
use App\Filament\Resources\FeePaymentResource\Widgets\FeeCollectionOverview;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFeePayments extends ListRecords
{
    protected static string $resource = FeePaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Record payment'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            FeeCollectionOverview::class,
        ];
    }
}
