<?php

namespace App\Filament\Resources\EnrollmentResource\RelationManagers;

use App\Notifications\PortalAlert;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Fee payments';

    protected static ?string $icon = 'heroicon-o-banknotes';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('amount')->numeric()->required()->prefix('₦')->minValue(1),
            Forms\Components\Select::make('method')->options([
                'cash' => 'Cash', 'bank_transfer' => 'Bank transfer', 'paystack' => 'Paystack',
                'monnify' => 'Monnify', 'card' => 'Card', 'other' => 'Other',
            ])->default('bank_transfer')->required(),
            Forms\Components\DateTimePicker::make('paid_at')->default(now())->native(false),
            Forms\Components\TextInput::make('reference')->label('Reference (optional)'),
            Forms\Components\TextInput::make('note')->label('Note')->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reference')
            ->defaultSort('paid_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('amount')->money('NGN')->sortable(),
                Tables\Columns\TextColumn::make('method')->badge(),
                Tables\Columns\TextColumn::make('paid_at')->dateTime('M j, Y')->placeholder('—'),
                Tables\Columns\TextColumn::make('receipt.receipt_number')->label('Receipt')->badge()->color('gray')->placeholder('—'),
                Tables\Columns\TextColumn::make('reference')->placeholder('—')->toggleable(),
                Tables\Columns\TextColumn::make('recordedBy.name')->label('Recorded by')->toggleable()->placeholder('—'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Record payment')
                    ->mutateFormDataUsing(function (array $data) {
                        $enrollment = $this->getOwnerRecord();
                        $data['user_id'] = $enrollment->user_id;
                        $data['recorded_by'] = auth()->id();
                        $data['paid_at'] ??= now();

                        return $data;
                    })
                    ->after(function ($record) {
                        $enrollment = $this->getOwnerRecord()->fresh();
                        $enrollment->user?->notify(new PortalAlert(
                            title: 'Payment received',
                            body: '₦'.number_format($record->amount).' recorded for '.$enrollment->program->name
                                .'. Balance: ₦'.number_format($enrollment->outstanding()).'.',
                            url: route('portal.fees'),
                            icon: 'Wallet',
                            color: 'success',
                        ));
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('receipt')->label('Receipt')->icon('heroicon-o-arrow-down-tray')->color('gray')
                    ->visible(fn ($record) => (bool) $record->receipt)
                    ->url(fn ($record) => $record->receipt ? route('receipts.download', $record->receipt) : null, true),
                Tables\Actions\Action::make('emailReceipt')->label('Email receipt')->icon('heroicon-o-paper-airplane')->color('success')
                    ->visible(fn ($record) => $record->receipt && filled($record->receipt->payer_email))
                    ->requiresConfirmation()
                    ->modalDescription(fn ($record) => 'Email receipt '.$record->receipt?->receipt_number.' to '.$record->receipt?->payer_email.'?')
                    ->action(fn ($record) => \App\Filament\Resources\ReceiptResource::emailReceipt($record->receipt)),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
