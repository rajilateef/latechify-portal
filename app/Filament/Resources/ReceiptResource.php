<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReceiptResource\Pages;
use App\Mail\ReceiptMail;
use App\Models\Receipt;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class ReceiptResource extends Resource
{
    protected static ?string $model = Receipt::class;

    protected static ?string $navigationGroup = 'Training Portal';

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $navigationLabel = 'Receipts';

    protected static ?int $navigationSort = 8;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('status', 'issued')->count() ?: null;
    }

    public static function form(Form $form): Form
    {
        // Receipts are issued automatically from payments — only the note is editable here.
        return $form->schema([
            Forms\Components\Placeholder::make('receipt_number')->label('Receipt no.')
                ->content(fn (?Receipt $record) => $record?->receipt_number),
            Forms\Components\Placeholder::make('payer')->label('Trainee')
                ->content(fn (?Receipt $record) => $record?->payer_name.' · '.$record?->payer_email),
            Forms\Components\Placeholder::make('amount')->label('Amount')
                ->content(fn (?Receipt $record) => '₦'.number_format((int) $record?->amount).' ('.$record?->methodLabel().')'),
            Forms\Components\Placeholder::make('balance')->label('Balance after')
                ->content(fn (?Receipt $record) => '₦'.number_format((int) $record?->balance)),
            Forms\Components\Textarea::make('notes')->rows(2)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('issued_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('receipt_number')->label('Receipt no.')->searchable()->weight('bold')->copyable(),
                Tables\Columns\TextColumn::make('payer_name')->label('Trainee')->searchable()->description(fn (Receipt $r) => $r->program_name),
                Tables\Columns\TextColumn::make('amount')->money('NGN')->sortable(),
                Tables\Columns\TextColumn::make('method')->badge()->formatStateUsing(fn (Receipt $r) => $r->methodLabel()),
                Tables\Columns\TextColumn::make('balance')->label('Balance')->money('NGN')
                    ->color(fn (Receipt $r) => $r->balance > 0 ? 'danger' : 'success'),
                Tables\Columns\IconColumn::make('emailed_at')->label('Emailed')->boolean()
                    ->trueIcon('heroicon-o-paper-airplane')->falseIcon('heroicon-o-minus')->falseColor('gray'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => $state === 'void' ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('issued_at')->dateTime('M j, Y')->label('Issued')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(['issued' => 'Issued', 'void' => 'Void']),
                Tables\Filters\Filter::make('emailed')->label('Emailed only')->query(fn ($q) => $q->whereNotNull('emailed_at')),
            ])
            ->actions([
                Tables\Actions\Action::make('view')->label('View')->icon('heroicon-o-eye')->color('gray')
                    ->url(fn (Receipt $r) => route('receipts.show', $r), true),
                Tables\Actions\Action::make('download')->label('PDF')->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Receipt $r) => route('receipts.download', $r), true),
                Tables\Actions\Action::make('email')->label('Email')->icon('heroicon-o-paper-airplane')->color('success')
                    ->requiresConfirmation()
                    ->modalDescription(fn (Receipt $r) => 'Send this receipt as a PDF to '.$r->payer_email.'?')
                    ->visible(fn (Receipt $r) => filled($r->payer_email) && ! $r->isVoid())
                    ->action(fn (Receipt $r) => static::emailReceipt($r)),
                Tables\Actions\Action::make('void')->label('Void')->icon('heroicon-o-x-circle')->color('danger')
                    ->requiresConfirmation()->visible(fn (Receipt $r) => ! $r->isVoid())
                    ->action(fn (Receipt $r) => $r->update(['status' => 'void'])),
                Tables\Actions\Action::make('restore')->label('Restore')->icon('heroicon-o-arrow-uturn-left')->color('gray')
                    ->visible(fn (Receipt $r) => $r->isVoid())
                    ->action(fn (Receipt $r) => $r->update(['status' => 'issued'])),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('email')->label('Email receipts')->icon('heroicon-o-paper-airplane')->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each(fn (Receipt $r) => static::emailReceipt($r, quiet: true)))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function emailReceipt(Receipt $receipt, bool $quiet = false): void
    {
        if (blank($receipt->payer_email) || $receipt->isVoid()) {
            return;
        }

        Mail::to($receipt->payer_email)->send(new ReceiptMail($receipt));
        $receipt->update(['emailed_at' => now()]);

        if (! $quiet) {
            Notification::make()->title('Receipt emailed to '.$receipt->payer_email)->success()->send();
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReceipts::route('/'),
            'edit'  => Pages\EditReceipt::route('/{record}/edit'),
        ];
    }
}
