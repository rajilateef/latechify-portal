<?php

namespace App\Filament\Resources\MonnifyWebhookEventResource\Pages;

use App\Filament\Resources\MonnifyWebhookEventResource;
use Filament\Resources\Pages\ListRecords;

class ListMonnifyWebhookEvents extends ListRecords
{
    protected static string $resource = MonnifyWebhookEventResource::class;

    public function getSubheading(): ?string
    {
        return 'Register this URL in Monnify → Settings → API Keys & Webhooks: '
            .url('/webhooks/monnify');
    }
}
