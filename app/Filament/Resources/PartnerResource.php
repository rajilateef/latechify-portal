<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartnerResource\Pages;
use App\Models\Partner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PartnerResource extends Resource
{
    protected static ?string $model = Partner::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Home Page';

    protected static ?int $navigationSort = 8;

    protected static ?string $navigationLabel = 'Partners & Logos';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\Select::make('category')
                ->options([
                    'partner'       => 'Technology / Tool',
                    'accreditation' => 'Accreditation / Award',
                    'employer'      => 'Employer / Alumni company',
                ])
                ->default('partner')->required()
                ->helperText('Controls which home-page strip this logo appears in.'),
            \App\Filament\Forms\Components\MediaPicker::make('logo')
                ->helperText('Optional — a clean logo. If omitted, the name is shown as text.')->columnSpanFull(),
            Forms\Components\TextInput::make('url')->url()->columnSpanFull(),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('logo')->getStateUsing(fn ($record) => media_url($record->logo))->height(36),
                Tables\Columns\TextColumn::make('name')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('category')->badge()
                    ->colors(['info' => 'partner', 'warning' => 'accreditation', 'success' => 'employer']),
                Tables\Columns\ToggleColumn::make('is_active'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')->options([
                    'partner' => 'Technology / Tool', 'accreditation' => 'Accreditation', 'employer' => 'Employer',
                ]),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPartners::route('/'),
            'create' => Pages\CreatePartner::route('/create'),
            'edit'   => Pages\EditPartner::route('/{record}/edit'),
        ];
    }
}
