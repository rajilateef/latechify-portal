<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdvertResource\Pages;
use App\Models\Advert;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AdvertResource extends Resource
{
    protected static ?string $model = Advert::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'Home Page';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Adverts & Fliers';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::live()->count() ?: null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Flier')
                ->description('Upload a promotional flier or advert. It can appear as a card in the home-page promotions section and/or as a pop-up when visitors land on the site.')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255),
                    \App\Filament\Forms\Components\MediaPicker::make('image')
                        ->label('Flier image')
                        ->required()
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('link_url')
                        ->label('Link (where the flier sends visitors)')
                        ->helperText('An internal path like /apply or a full URL like https://…')
                        ->maxLength(255),
                    Forms\Components\Textarea::make('description')
                        ->rows(2)
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Placement & Schedule')
                ->schema([
                    Forms\Components\Select::make('placement')
                        ->options([
                            'section' => 'Home promotions section only',
                            'popup'   => 'Pop-up on first visit only',
                            'both'    => 'Both section and pop-up',
                        ])
                        ->default('section')
                        ->required(),
                    Forms\Components\Toggle::make('show_floating')
                        ->label('Show as floating card')
                        ->helperText('Also float this advert as a dismissible card near the navbar on every page.')
                        ->default(false),
                    Forms\Components\TextInput::make('sort_order')
                        ->numeric()
                        ->default(0),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),
                    Forms\Components\DateTimePicker::make('starts_at')
                        ->label('Show from')
                        ->helperText('Leave empty to show immediately.'),
                    Forms\Components\DateTimePicker::make('ends_at')
                        ->label('Show until')
                        ->helperText('Leave empty to show indefinitely.'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('image')->getStateUsing(fn ($record) => media_url($record->image))->height(50),
                Tables\Columns\TextColumn::make('title')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('placement')
                    ->badge()
                    ->colors(['info' => 'section', 'warning' => 'popup', 'success' => 'both']),
                Tables\Columns\TextColumn::make('link_url')->limit(30)->toggleable(),
                Tables\Columns\ToggleColumn::make('is_active')->label('Active'),
                Tables\Columns\TextColumn::make('starts_at')->dateTime('M j, Y')->placeholder('—')->toggleable(),
                Tables\Columns\TextColumn::make('ends_at')->dateTime('M j, Y')->placeholder('—')->toggleable(),
                Tables\Columns\TextColumn::make('clicks')->numeric()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('placement')
                    ->options(['section' => 'Section', 'popup' => 'Pop-up', 'both' => 'Both']),
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
            ])
            ->actions([
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
            'index'  => Pages\ListAdverts::route('/'),
            'create' => Pages\CreateAdvert::route('/create'),
            'edit'   => Pages\EditAdvert::route('/{record}/edit'),
        ];
    }
}
