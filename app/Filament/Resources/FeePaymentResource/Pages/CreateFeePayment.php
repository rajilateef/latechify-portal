<?php

namespace App\Filament\Resources\FeePaymentResource\Pages;

use App\Filament\Resources\FeePaymentResource;
use App\Models\Enrollment;
use App\Notifications\PortalAlert;
use Filament\Resources\Pages\CreateRecord;

class CreateFeePayment extends CreateRecord
{
    protected static string $resource = FeePaymentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] ??= Enrollment::find($data['enrollment_id'])?->user_id;
        $data['recorded_by'] = auth()->id();
        $data['paid_at'] ??= now();

        return $data;
    }

    /** Let the trainee know the moment their payment lands. */
    protected function afterCreate(): void
    {
        $enrollment = $this->record->enrollment?->fresh();

        $this->record->user?->notify(new PortalAlert(
            title: 'Payment received',
            body: '₦'.number_format($this->record->amount).' recorded for '.$enrollment?->program?->name
                .'. Balance: ₦'.number_format($enrollment?->outstanding() ?? 0).'.',
            url: route('portal.fees'),
            icon: 'Wallet',
            color: 'success',
        ));
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
