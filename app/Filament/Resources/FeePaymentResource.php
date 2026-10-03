<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeePaymentResource\Pages;
use App\Models\Enrollment;
use App\Models\FeePayment;
use App\Models\TrainingProgram;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;

/**
 * Every fee payment across every trainee — the single ledger the finance side works
 * from. Recording one here issues its receipt automatically.
 */
class FeePaymentResource extends Resource
{
    protected static ?string $model = FeePayment::class;

    protected static ?string $navigationGroup = 'Training Portal';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Fee payments';

    protected static ?string $modelLabel = 'fee payment';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('enrollment_id')
                ->label('Enrolment')
                ->options(fn () => Enrollment::with('user', 'program')->get()
                    ->mapWithKeys(fn (Enrollment $e) => [
                        $e->id => ($e->user?->displayName() ?? 'Trainee').' — '.($e->program?->name ?? 'Program')
                            .' (owes ₦'.number_format($e->outstanding()).')',
                    ]))
                ->searchable()->required()->live()
                ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('user_id', Enrollment::find($state)?->user_id)),
            Forms\Components\Hidden::make('user_id'),
            Forms\Components\TextInput::make('amount')->label('Amount (₦)')->numeric()->prefix('₦')->minValue(1)->required()
                ->helperText(fn (Forms\Get $get) => ($e = Enrollment::find($get('enrollment_id')))
                    ? 'Outstanding on this enrolment: ₦'.number_format($e->outstanding())
                    : null),
            Forms\Components\Select::make('method')->options([
                'cash' => 'Cash', 'bank_transfer' => 'Bank transfer', 'paystack' => 'Paystack',
                'monnify' => 'Monnify', 'card' => 'Card', 'other' => 'Other',
            ])->default('bank_transfer')->required(),
            Forms\Components\DateTimePicker::make('paid_at')->default(now())->native(false)->required(),
            Forms\Components\TextInput::make('reference')->label('Reference (optional)'),
            Forms\Components\TextInput::make('note')->label('Note')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('paid_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('paid_at')->label('Paid')->dateTime('M j, Y')->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Trainee')->searchable()->sortable()
                    ->description(fn (FeePayment $r) => $r->enrollment?->program?->name),
                Tables\Columns\TextColumn::make('amount')->money('NGN')->sortable()->weight('bold')->color('success')
                    ->summarize(Sum::make()->money('NGN')->label('Total')),
                Tables\Columns\TextColumn::make('method')->badge()
                    ->color(fn ($state) => in_array($state, ['monnify', 'paystack', 'card']) ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('outstanding')->label('Balance after')->money('NGN')
                    ->state(fn (FeePayment $r) => $r->enrollment?->outstanding() ?? 0)
                    ->color(fn (FeePayment $r) => ($r->enrollment?->outstanding() ?? 0) > 0 ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('receipt.receipt_number')->label('Receipt')->badge()->color('gray')->placeholder('—')->searchable(),
                Tables\Columns\TextColumn::make('reference')->placeholder('—')->fontFamily('mono')->size('xs')->toggleable()->searchable(),
                Tables\Columns\TextColumn::make('recordedBy.name')->label('Recorded by')->placeholder('Online')->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('method')->options([
                    'cash' => 'Cash', 'bank_transfer' => 'Bank transfer', 'paystack' => 'Paystack',
                    'monnify' => 'Monnify', 'card' => 'Card', 'other' => 'Other',
                ]),
                Tables\Filters\SelectFilter::make('program')->label('Program')
                    ->options(fn () => TrainingProgram::orderBy('name')->pluck('name', 'id'))
                    ->query(fn ($query, array $data) => $data['value']
                        ? $query->whereHas('enrollment', fn ($q) => $q->where('training_program_id', $data['value']))
                        : $query),
                Tables\Filters\SelectFilter::make('user_id')->label('Trainee')
                    ->options(fn () => User::where('is_student', true)->orderBy('name')->pluck('name', 'id'))
                    ->searchable(),
                Tables\Filters\Filter::make('paid_between')
                    ->form([
                        Forms\Components\DatePicker::make('from')->native(false),
                        Forms\Components\DatePicker::make('until')->native(false),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('paid_at', '>=', $d))
                        ->when($data['until'] ?? null, fn ($q, $d) => $q->whereDate('paid_at', '<=', $d)))
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators[] = 'From '.$data['from'];
                        }
                        if ($data['until'] ?? null) {
                            $indicators[] = 'Until '.$data['until'];
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('receipt')->label('Receipt')->icon('heroicon-o-arrow-down-tray')->color('gray')
                    ->visible(fn (FeePayment $record) => (bool) $record->receipt)
                    ->url(fn (FeePayment $record) => route('receipts.download', $record->receipt), true),
                Tables\Actions\Action::make('emailReceipt')->label('Email receipt')->icon('heroicon-o-paper-airplane')->color('success')
                    ->visible(fn (FeePayment $record) => $record->receipt && filled($record->receipt->payer_email))
                    ->requiresConfirmation()
                    ->modalDescription(fn (FeePayment $record) => 'Email receipt '.$record->receipt?->receipt_number.' to '.$record->receipt?->payer_email.'?')
                    ->action(fn (FeePayment $record) => ReceiptResource::emailReceipt($record->receipt)),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListFeePayments::route('/'),
            'create' => Pages\CreateFeePayment::route('/create'),
            'edit'   => Pages\EditFeePayment::route('/{record}/edit'),
        ];
    }
}
