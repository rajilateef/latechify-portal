<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuItemResource\Pages;
use App\Models\MenuItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MenuItemResource extends Resource
{
    protected static ?string $model = MenuItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-3';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Navbar Menu';

    protected static ?string $modelLabel = 'menu item';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('label')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->options([
                        'link'     => 'Link',
                        'services' => 'Services dropdown',
                        'courses'  => 'Courses dropdown',
                    ])
                    ->default('link')
                    ->required()
                    ->native(false)
                    ->helperText('Dropdowns automatically list your active Services / Courses.'),
                Forms\Components\TextInput::make('url')
                    ->label('Link')
                    ->helperText('A path like /about or a full URL. For dropdowns this is the heading link.')
                    ->maxLength(255),
                Forms\Components\TextInput::make('icon')
                    ->label('Icon (optional)')
                    ->helperText('A Lucide icon name in PascalCase, e.g. Sun. Leave empty for none.')
                    ->maxLength(255),
                Forms\Components\Toggle::make('highlight')
                    ->label('Highlight')
                    ->helperText('Show in the brand colour (e.g. a seasonal promo).'),
                Forms\Components\Toggle::make('is_visible')
                    ->label('Show in navbar')
                    ->default(true),
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('label')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')->badge()->color(fn ($state) => $state === 'link' ? 'gray' : 'info'),
                Tables\Columns\TextColumn::make('url')->toggleable(),
                Tables\Columns\IconColumn::make('highlight')->boolean()->toggleable(),
                Tables\Columns\ToggleColumn::make('is_visible')->label('Visible'),
                Tables\Columns\TextColumn::make('sort_order')->label('Order')->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_visible')->label('Visible'),
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
            'index'  => Pages\ListMenuItems::route('/'),
            'create' => Pages\CreateMenuItem::route('/create'),
            'edit'   => Pages\EditMenuItem::route('/{record}/edit'),
        ];
    }
}
