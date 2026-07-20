<?php

namespace App\Filament\Resources\ApplicationResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $recordTitleAttribute = 'reference';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('method')
                ->options(['paystack' => 'Paystack', 'bank_transfer' => 'Bank Transfer'])
                ->default('paystack')->required(),
            Forms\Components\TextInput::make('amount')->numeric()->prefix('₦')->required(),
            Forms\Components\TextInput::make('currency')->default('NGN'),
            Forms\Components\TextInput::make('reference'),
            Forms\Components\Select::make('status')->options([
                'pending' => 'Pending', 'success' => 'Success', 'failed' => 'Failed', 'verified' => 'Verified', 'rejected' => 'Rejected',
            ])->default('pending')->required(),
            Forms\Components\TextInput::make('bank_name'),
            Forms\Components\TextInput::make('account_name'),
            Forms\Components\TextInput::make('account_number'),
            Forms\Components\TextInput::make('transaction_date'),
            Forms\Components\FileUpload::make('receipt')->disk('public')->directory('receipts')->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reference')
            ->columns([
                Tables\Columns\TextColumn::make('method')->badge(),
                Tables\Columns\TextColumn::make('amount')->money('NGN'),
                Tables\Columns\TextColumn::make('reference')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('status')->badge()->colors([
                    'warning' => 'pending', 'success' => fn ($state) => in_array($state, ['success', 'verified']), 'danger' => fn ($state) => in_array($state, ['failed', 'rejected']),
                ]),
                Tables\Columns\ImageColumn::make('receipt')->disk('public')->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime('M j, Y H:i'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('verify')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn ($record) => ! in_array($record->status, ['verified', 'success']))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['status' => 'verified', 'verified_at' => now()]);
                        $record->application->update(['status' => 'paid', 'paid_at' => now()]);
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
