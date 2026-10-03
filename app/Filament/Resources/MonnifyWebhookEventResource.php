<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MonnifyWebhookEventResource\Pages;
use App\Models\MonnifyWebhookEvent;
use App\Services\PaymentConfirmer;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Read-only log of what Monnify has sent us. This is the first place to look when
 * a payment did not confirm: it shows whether Monnify called at all, whether the
 * signature passed, and which record the call matched.
 */
class MonnifyWebhookEventResource extends Resource
{
    protected static ?string $model = MonnifyWebhookEvent::class;

    protected static ?string $navigationGroup = 'Training Portal';

    protected static ?string $navigationIcon = 'heroicon-o-signal';

    protected static ?string $navigationLabel = 'Payment webhooks';

    protected static ?string $modelLabel = 'webhook delivery';

    protected static ?int $navigationSort = 6;

    public static function canCreate(): bool
    {
        return false;
    }

    /** Badge = deliveries that did not result in a confirmed payment. */
    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::failed()
            ->where('created_at', '>=', now()->subDays(7))
            ->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('received_at', 'desc')
            ->poll('30s')
            ->emptyStateHeading('No webhooks received yet')
            ->emptyStateDescription('Register https://'.request()->getHost().'/webhooks/monnify in your Monnify dashboard.')
            ->emptyStateIcon('heroicon-o-signal-slash')
            ->columns([
                Tables\Columns\TextColumn::make('received_at')->label('Received')->dateTime('M j, H:i:s')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->formatStateUsing(fn (MonnifyWebhookEvent $r) => $r->statusLabel())
                    ->color(fn (MonnifyWebhookEvent $r) => $r->statusColor()),
                Tables\Columns\TextColumn::make('event_type')->label('Event')->badge()->color('gray')->placeholder('—')->toggleable(),
                Tables\Columns\TextColumn::make('payment_reference')->label('Reference')->fontFamily('mono')->size('xs')
                    ->copyable()->searchable()->placeholder('—'),
                Tables\Columns\TextColumn::make('amount_paid')->label('Amount')->money('NGN')->placeholder('—'),
                Tables\Columns\TextColumn::make('handler')->label('Matched')->badge()->color('info')
                    ->formatStateUsing(fn ($state, MonnifyWebhookEvent $r) => $state
                        ? str_replace('_', ' ', $state).' #'.$r->handled_id
                        : '—')
                    ->placeholder('—'),
                // Advisory only — deliveries are accepted either way, because every
                // payment is re-verified with Monnify before it is marked paid.
                Tables\Columns\IconColumn::make('signature_valid')->label('Signed')->boolean()
                    ->tooltip('Whether the monnify-signature header matched. Not enforced.')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('message')->label('Detail')->wrap()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'handled'   => 'Handled',
                    'ignored'   => 'Ignored',
                    'unmatched' => 'No matching record',
                    'failed'    => 'Verification failed',
                ]),
                Tables\Filters\Filter::make('problems')->label('Problems only')
                    ->query(fn ($query) => $query->failed()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // A delivery can fail because Monnify was briefly unreachable — let an
                // admin re-run the same verification without waiting for a retry.
                Tables\Actions\Action::make('retry')
                    ->label('Retry')->icon('heroicon-o-arrow-path')->color('warning')
                    ->visible(fn (MonnifyWebhookEvent $record) => $record->status === 'failed' && $record->handler)
                    ->requiresConfirmation()
                    ->modalDescription('Re-verifies this payment with Monnify and confirms it if it has since gone through.')
                    ->action(fn (MonnifyWebhookEvent $record) => static::retry($record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Delivery')->schema([
                Infolists\Components\TextEntry::make('received_at')->dateTime(),
                Infolists\Components\TextEntry::make('event_type')->placeholder('—'),
                Infolists\Components\TextEntry::make('status')->badge()
                    ->formatStateUsing(fn (MonnifyWebhookEvent $r) => $r->statusLabel())
                    ->color(fn (MonnifyWebhookEvent $r) => $r->statusColor()),
                Infolists\Components\IconEntry::make('signature_valid')->boolean()->label('Signature matched')
                    ->helperText('Recorded for information — not enforced.'),
                Infolists\Components\TextEntry::make('payment_reference')->placeholder('—')->copyable(),
                Infolists\Components\TextEntry::make('transaction_reference')->placeholder('—')->copyable(),
                Infolists\Components\TextEntry::make('payment_status')->placeholder('—'),
                Infolists\Components\TextEntry::make('amount_paid')->money('NGN')->placeholder('—'),
                Infolists\Components\TextEntry::make('message')->label('Detail')->columnSpanFull()->placeholder('—'),
            ])->columns(3),

            Infolists\Components\Section::make('Raw payload')->collapsed()->schema([
                Infolists\Components\TextEntry::make('payload')
                    ->label('')
                    ->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
                    ->fontFamily('mono')->size('xs')->columnSpanFull(),
            ]),
        ]);
    }

    protected static function retry(MonnifyWebhookEvent $event): void
    {
        $confirmer = app(PaymentConfirmer::class);

        $ok = match ($event->handler) {
            'checkout_order'    => ($o = \App\Models\CheckoutOrder::find($event->handled_id)) && $confirmer->confirmOrder($o),
            'camp_registration' => ($r = \App\Models\CampRegistration::find($event->handled_id)) && $confirmer->confirmCamp($r),
            default             => false,
        };

        $event->update([
            'status'  => $ok ? 'handled' : 'failed',
            'message' => $ok
                ? 'Confirmed on manual retry at '.now()->toDateTimeString().'.'
                : 'Manual retry at '.now()->toDateTimeString().' still could not verify the payment.',
        ]);

        Notification::make()
            ->title($ok ? 'Payment confirmed' : 'Still not verified')
            ->body($event->message)
            ->{$ok ? 'success' : 'warning'}()
            ->send();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMonnifyWebhookEvents::route('/'),
        ];
    }
}
