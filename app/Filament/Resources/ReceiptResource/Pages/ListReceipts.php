<?php

namespace App\Filament\Resources\ReceiptResource\Pages;

use App\Filament\Resources\ReceiptResource;
use Filament\Resources\Pages\ListRecords;

class ListReceipts extends ListRecords
{
    protected static string $resource = ReceiptResource::class;

    // Receipts are issued automatically when a payment is recorded — no manual create.
    protected function getHeaderActions(): array
    {
        return [];
    }
}
