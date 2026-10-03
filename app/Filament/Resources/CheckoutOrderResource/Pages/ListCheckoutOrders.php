<?php

namespace App\Filament\Resources\CheckoutOrderResource\Pages;

use App\Filament\Resources\CheckoutOrderResource;
use App\Models\CheckoutOrder;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCheckoutOrders extends ListRecords
{
    protected static string $resource = CheckoutOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Record an order'),
        ];
    }

    public function getTabs(): array
    {
        $count = fn (string $status) => CheckoutOrder::where('kind', 'enrolment')->where('status', $status)->count();

        return [
            'awaiting'  => Tab::make('Awaiting confirmation')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'paid'))
                ->badge($count('paid') ?: null)
                ->badgeColor('warning'),
            'confirmed' => Tab::make('Confirmed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'confirmed'))
                ->badge($count('confirmed') ?: null),
            'pending'   => Tab::make('Awaiting payment')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge($count('pending') ?: null),
            'all'       => Tab::make('All'),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'awaiting';
    }
}
