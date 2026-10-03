<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CheckoutOrderResource;
use App\Models\CheckoutOrder;
use App\Services\PortalProvisioner;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Paid website checkouts waiting on a super admin. Confirming here creates the
 * trainee's portal, banks the fee and emails their login details.
 */
class PendingCheckoutsWidget extends BaseWidget
{
    protected static ?string $heading = 'Paid checkouts awaiting confirmation';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->baseQuery())
            ->emptyStateHeading('No checkouts awaiting confirmation')
            ->emptyStateDescription('Paid website enrolments land here for you to approve.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('full_name')->label('Applicant')->searchable()
                    ->description(fn (CheckoutOrder $r) => $r->email),
                Tables\Columns\TextColumn::make('program_name')->label('Program')->badge()->color('info')
                    ->state(fn (CheckoutOrder $r) => $r->itemName())
                    ->description(fn (CheckoutOrder $r) => $r->formatLabel().' · '.$r->programName()),
                Tables\Columns\TextColumn::make('amount')->money('NGN')->color('success')->weight('bold'),
                Tables\Columns\TextColumn::make('payment_method')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('payment_reference')->label('Reference')->fontFamily('mono')->size('xs')->copyable(),
                Tables\Columns\TextColumn::make('paid_at')->since()->label('Paid')->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('confirm')
                    ->label('Confirm & create portal')->icon('heroicon-o-rocket-launch')->color('success')
                    ->requiresConfirmation()
                    ->modalDescription(fn (CheckoutOrder $record) => 'Creates '.$record->full_name
                        .'\'s trainee account, activates their enrolment, banks the ₦'.number_format($record->amount)
                        .' paid and emails their login details.')
                    ->action(function (CheckoutOrder $record) {
                        try {
                            $result = app(PortalProvisioner::class)->confirm($record, auth()->user());
                        } catch (\Throwable $e) {
                            report($e);
                            Notification::make()->title('Could not create the portal')->body($e->getMessage())->danger()->send();

                            return;
                        }

                        Notification::make()
                            ->title('Portal created for '.$result['user']->displayName())
                            ->body($result['password']
                                ? 'Login details emailed to '.$result['user']->email.'.'
                                : 'Linked to their existing account — they keep their current password.')
                            ->success()->send();
                    }),
                Tables\Actions\Action::make('view')
                    ->label('Open')->icon('heroicon-o-arrow-top-right-on-square')->color('gray')
                    ->url(fn (CheckoutOrder $record) => CheckoutOrderResource::getUrl('edit', ['record' => $record])),
            ]);
    }

    protected function baseQuery(): Builder
    {
        return CheckoutOrder::query()->where('kind', 'enrolment')->where('status', 'paid')->latest('paid_at');
    }

    protected function getTableQuery(): ?Builder
    {
        return $this->baseQuery();
    }
}
