<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrainingCourseResource\Pages;
use App\Filament\Resources\TrainingCourseResource\RelationManagers\ClassesRelationManager;
use App\Filament\Resources\TrainingCourseResource\RelationManagers\MaterialsRelationManager;
use App\Models\TrainingCourse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class TrainingCourseResource extends Resource
{
    protected static ?string $model = TrainingCourse::class;

    protected static ?string $navigationGroup = 'Training Portal';

    /** Managed by drilling into a Program → Courses; not a top-level nav item. */
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('training_program_id')->relationship('program', 'name')->required()->label('Program'),
            Forms\Components\TextInput::make('name')->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state))),
            Forms\Components\TextInput::make('slug')->required(),
            Forms\Components\TextInput::make('icon')->helperText('Lucide icon name.'),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
            Forms\Components\Textarea::make('description')->rows(2)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('program.name')->badge()->color('info'),
                Tables\Columns\TextColumn::make('classes_count')->counts('classes')->label('Classes')->badge(),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getRelations(): array
    {
        return [ClassesRelationManager::class, MaterialsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTrainingCourses::route('/'),
            'create' => Pages\CreateTrainingCourse::route('/create'),
            'edit'   => Pages\EditTrainingCourse::route('/{record}/edit'),
        ];
    }
}
