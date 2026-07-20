<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampRegistrationResource\Pages;
use App\Models\CampRegistration;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CampRegistrationResource extends Resource
{
    protected static ?string $model = CampRegistration::class;

    protected static ?string $navigationIcon = 'heroicon-o-sun';

    protected static ?string $navigationGroup = 'Submissions';

    protected static ?string $navigationLabel = 'Summer Camp';

    protected static ?string $modelLabel = 'camp registration';

    protected static ?int $navigationSort = 5;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Registrant')->schema([
                Forms\Components\TextInput::make('full_name')->required(),
                Forms\Components\TextInput::make('email')->email()->required(),
                Forms\Components\TextInput::make('phone')->required(),
                Forms\Components\TextInput::make('age_group'),
                Forms\Components\TextInput::make('track'),
                Forms\Components\Select::make('mode')->options([
                    'physical' => 'Physical (on-site)',
                    'virtual'  => 'Virtual (online)',
                ]),
                Forms\Components\TextInput::make('experience'),
                Forms\Components\Textarea::make('note')->rows(2)->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Payment')->schema([
                Forms\Components\TextInput::make('amount')->numeric()->prefix('₦'),
                Forms\Components\Select::make('payment_method')->options([
                    'monnify' => 'Online (Monnify)',
                    'manual'  => 'Manual / Bank transfer',
                ]),
                Forms\Components\Select::make('status')->options([
                    'pending'   => 'Pending',
                    'paid'      => 'Paid',
                    'cancelled' => 'Cancelled',
                ])->required(),
                Forms\Components\DateTimePicker::make('paid_at'),
                Forms\Components\TextInput::make('payment_reference')->disabled(),
                Forms\Components\TextInput::make('transaction_reference')->disabled(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('full_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('phone')->toggleable(),
                Tables\Columns\TextColumn::make('track')->badge()->color('info'),
                Tables\Columns\TextColumn::make('mode')->badge()->color(fn ($state) => $state === 'physical' ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('age_group')->label('Age')->toggleable(),
                Tables\Columns\TextColumn::make('amount')->money('NGN')->sortable(),
                Tables\Columns\TextColumn::make('payment_method')->badge()->color(fn ($state) => $state === 'monnify' ? 'primary' : 'gray'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'paid'      => 'success',
                    'cancelled' => 'danger',
                    default     => 'warning',
                }),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->label('Registered'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'pending'   => 'Pending',
                    'paid'      => 'Paid',
                    'cancelled' => 'Cancelled',
                ]),
                Tables\Filters\SelectFilter::make('payment_method')->options([
                    'monnify' => 'Online (Monnify)',
                    'manual'  => 'Manual / Bank transfer',
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('markPaid')
                    ->label('Mark paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (CampRegistration $record) => $record->status !== 'paid')
                    ->requiresConfirmation()
                    ->action(fn (CampRegistration $record) => $record->update(['status' => 'paid', 'paid_at' => now()])),
                Tables\Actions\EditAction::make(),
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
            'index'  => Pages\ListCampRegistrations::route('/'),
            'create' => Pages\CreateCampRegistration::route('/create'),
            'edit'   => Pages\EditCampRegistration::route('/{record}/edit'),
        ];
    }
}
