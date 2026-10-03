<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CheckoutOrderResource\Pages;
use App\Models\CheckoutOrder;
use App\Services\PortalProvisioner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CheckoutOrderResource extends Resource
{
    protected static ?string $model = CheckoutOrder::class;

    protected static ?string $navigationGroup = 'Training Portal';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Checkout orders';

    protected static ?string $modelLabel = 'checkout order';

    protected static ?int $navigationSort = 1;

    /** Website enrolment purchases only — balance top-ups live under Fee payments. */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('kind', 'enrolment');
    }

    /** Badge = paid orders still waiting on a super admin. */
    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('kind', 'enrolment')->where('status', 'paid')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Applicant')->schema([
                Forms\Components\TextInput::make('full_name')->required(),
                Forms\Components\TextInput::make('email')->email()->required(),
                Forms\Components\TextInput::make('phone')->tel()->required(),
                Forms\Components\Textarea::make('note')->label('Applicant note')->rows(2)->columnSpanFull(),
            ])->columns(3),

            Forms\Components\Section::make('Order')->schema([
                Forms\Components\Select::make('course_id')->label('Course')->relationship('course', 'title')->searchable()->preload(),
                Forms\Components\Select::make('training_program_id')->label('Portal program')->relationship('program', 'name')->required()
                    ->helperText('The program the trainee is enrolled into on confirmation.'),
                Forms\Components\Select::make('format')->label('Class format')
                    ->options(['online' => 'Online', 'physical' => 'On-campus'])->default('online')->required(),
                Forms\Components\Select::make('type')->label('Enrolment type')
                    ->options(['full_time' => 'Full-time', 'it_siwes' => 'IT / SIWES'])->default('full_time')->required(),
                Forms\Components\TextInput::make('amount')->label('Amount (₦)')->numeric()->prefix('₦')->required(),
                Forms\Components\Select::make('payment_method')->options(['monnify' => 'Monnify (online)', 'transfer' => 'Bank transfer'])->required(),
                Forms\Components\Select::make('status')->options([
                    'pending'   => 'Awaiting payment',
                    'paid'      => 'Paid — awaiting confirmation',
                    'confirmed' => 'Confirmed — portal active',
                    'rejected'  => 'Rejected',
                ])->required()
                    ->helperText('Set to "Paid" for a verified bank transfer, then use "Confirm & create portal".'),
                Forms\Components\DateTimePicker::make('paid_at')->native(false),
                Forms\Components\TextInput::make('payment_reference')->label('Payment reference')
                    ->helperText('Always LATECHIFY-prefixed. Leave as generated.')->columnSpanFull(),
                Forms\Components\TextInput::make('admin_note')->label('Admin note')->columnSpanFull(),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('full_name')->label('Applicant')->searchable()->sortable()
                    ->description(fn (CheckoutOrder $r) => $r->email),
                Tables\Columns\TextColumn::make('course_name')->label('Course')->badge()->color('info')
                    ->state(fn (CheckoutOrder $r) => $r->itemName())->searchable()
                    ->description(fn (CheckoutOrder $r) => 'enrols into '.$r->programName()),
                Tables\Columns\TextColumn::make('format')->label('Format')->badge()
                    ->formatStateUsing(fn (CheckoutOrder $r) => $r->formatLabel())
                    ->color(fn ($state) => $state === 'physical' ? 'warning' : 'gray'),
                Tables\Columns\TextColumn::make('amount')->money('NGN')->sortable(),
                Tables\Columns\TextColumn::make('payment_method')->badge()->color('gray')->toggleable(),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->formatStateUsing(fn (CheckoutOrder $r) => $r->statusLabel())
                    ->color(fn ($state) => match ($state) {
                        'confirmed' => 'success', 'paid' => 'warning', 'rejected' => 'danger', default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('payment_reference')->label('Reference')->copyable()->fontFamily('mono')
                    ->size('xs')->placeholder('—')->toggleable()->searchable(),
                Tables\Columns\TextColumn::make('paid_at')->dateTime('M j, Y H:i')->placeholder('—')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->since()->label('Started')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'pending'   => 'Awaiting payment',
                    'paid'      => 'Paid — awaiting confirmation',
                    'confirmed' => 'Confirmed',
                    'rejected'  => 'Rejected',
                ]),
                Tables\Filters\SelectFilter::make('training_program_id')->label('Program')->relationship('program', 'name'),
                Tables\Filters\SelectFilter::make('payment_method')->options(['monnify' => 'Monnify', 'transfer' => 'Bank transfer']),
            ])
            ->actions([
                Tables\Actions\Action::make('confirm')
                    ->label('Confirm & create portal')
                    ->icon('heroicon-o-rocket-launch')->color('success')
                    ->visible(fn (CheckoutOrder $record) => $record->status === 'paid')
                    ->requiresConfirmation()
                    ->modalHeading('Confirm payment and open the portal')
                    ->modalDescription(fn (CheckoutOrder $record) => 'This creates '.$record->full_name
                        .'\'s trainee account, activates their enrolment on '.$record->programName()
                        .', banks the ₦'.number_format($record->amount).' paid, and emails their login details.')
                    ->modalSubmitActionLabel('Confirm & create')
                    ->action(fn (CheckoutOrder $record) => static::confirm($record)),

                Tables\Actions\Action::make('markPaid')
                    ->label('Mark as paid')
                    ->icon('heroicon-o-banknotes')->color('warning')
                    ->visible(fn (CheckoutOrder $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalDescription('Use this once you have verified a bank transfer for this order.')
                    ->action(function (CheckoutOrder $record) {
                        $record->markPaid();
                        Notification::make()->title('Marked as paid')
                            ->body('Now use "Confirm & create portal" to provision the trainee.')->success()->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')->icon('heroicon-o-x-circle')->color('danger')
                    ->visible(fn (CheckoutOrder $record) => ! $record->isConfirmed() && $record->status !== 'rejected')
                    ->requiresConfirmation()
                    ->form([Forms\Components\TextInput::make('admin_note')->label('Reason (optional)')])
                    ->action(fn (CheckoutOrder $record, array $data) => $record->update([
                        'status'     => 'rejected',
                        'admin_note' => $data['admin_note'] ?? $record->admin_note,
                    ])),

                Tables\Actions\Action::make('openTrainee')
                    ->label('Open trainee')->icon('heroicon-o-arrow-top-right-on-square')->color('gray')
                    ->visible(fn (CheckoutOrder $record) => (bool) $record->user_id)
                    ->url(fn (CheckoutOrder $record) => StudentResource::getUrl('edit', ['record' => $record->user_id])),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('confirm')
                        ->label('Confirm & create portals')->icon('heroicon-o-rocket-launch')->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records
                            ->filter(fn (CheckoutOrder $o) => $o->status === 'paid')
                            ->each(fn (CheckoutOrder $o) => static::confirm($o, notify: false)))
                        ->deselectRecordsAfterCompletion()
                        ->after(fn () => Notification::make()->title('Portals created')->success()->send()),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /** Provision the portal for a paid order and report the outcome to the admin. */
    protected static function confirm(CheckoutOrder $order, bool $notify = true): void
    {
        try {
            $result = app(PortalProvisioner::class)->confirm($order, auth()->user());
        } catch (\Throwable $e) {
            report($e);

            Notification::make()->title('Could not create the portal')
                ->body($e->getMessage())->danger()->persistent()->send();

            return;
        }

        if (! $notify) {
            return;
        }

        Notification::make()
            ->title('Portal created for '.$result['user']->displayName())
            ->body($result['password']
                ? 'Login details emailed to '.$result['user']->email.'.'
                : 'Linked to their existing account ('.$result['user']->email.') — they keep their current password.')
            ->success()->persistent()->send();
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCheckoutOrders::route('/'),
            'create' => Pages\CreateCheckoutOrder::route('/create'),
            'edit'   => Pages\EditCheckoutOrder::route('/{record}/edit'),
        ];
    }
}
