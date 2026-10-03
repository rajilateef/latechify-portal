<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaterialFolderResource\Pages;
use App\Models\MaterialFolder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MaterialFolderResource extends Resource
{
    protected static ?string $model = MaterialFolder::class;

    protected static ?string $navigationGroup = 'Training Portal';

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $navigationLabel = 'Folders';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\Select::make('parent_id')->label('Inside folder')
                ->relationship('parent', 'name')
                ->getOptionLabelFromRecordUsing(fn (MaterialFolder $f) => $f->path())
                ->searchable()->placeholder('Top level'),
            Forms\Components\ColorPicker::make('color'),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()
                    ->icon('heroicon-o-folder')
                    ->description(fn (MaterialFolder $r) => $r->parent?->name),
                Tables\Columns\TextColumn::make('files_count')->counts('files')->label('Files')->badge(),
                Tables\Columns\ColorColumn::make('color')->toggleable(),
                Tables\Columns\TextColumn::make('sort_order')->label('Order')->toggleable(),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->emptyStateHeading('No folders yet')
            ->emptyStateDescription('Create folders to keep your media library organised.')
            ->emptyStateIcon('heroicon-o-folder-plus');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMaterialFolders::route('/'),
            'create' => Pages\CreateMaterialFolder::route('/create'),
            'edit'   => Pages\EditMaterialFolder::route('/{record}/edit'),
        ];
    }
}
