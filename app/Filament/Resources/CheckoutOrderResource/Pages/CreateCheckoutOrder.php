<?php

namespace App\Filament\Resources\CheckoutOrderResource\Pages;

use App\Filament\Resources\CheckoutOrderResource;
use App\Models\TrainingProgram;
use App\Services\Monnify;
use Filament\Resources\Pages\CreateRecord;

class CreateCheckoutOrder extends CreateRecord
{
    protected static string $resource = CheckoutOrderResource::class;

    /** Snapshot the program name and mint a LATECHIFY reference for manually-entered orders. */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['program_name'] ??= TrainingProgram::find($data['training_program_id'] ?? null)?->name;
        $data['payment_reference'] = $data['payment_reference'] ?: Monnify::reference('CHK', uniqid());

        return $data;
    }
}
