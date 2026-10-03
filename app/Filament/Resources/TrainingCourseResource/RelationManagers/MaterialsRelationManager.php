<?php

namespace App\Filament\Resources\TrainingCourseResource\RelationManagers;

use App\Filament\Resources\CourseMaterialResource;
use App\Models\CourseMaterial;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MaterialsRelationManager extends RelationManager
{
    protected static string $relationship = 'materials';

    protected static ?string $title = 'Documents & media';

    protected static ?string $icon = 'heroicon-o-folder';

    public function form(Form $form): Form
    {
        // Course is implied by the relation, so hide the course picker.
        return $form->schema(CourseMaterialResource::formSchema(withCourse: false));
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->description(fn (CourseMaterial $r) => $r->category),
                Tables\Columns\TextColumn::make('type')->badge()
                    ->formatStateUsing(fn (CourseMaterial $r) => $r->isFile() ? $r->extension() : ucfirst($r->type))
                    ->color(fn ($state) => $state === 'file' ? 'success' : 'info'),
                Tables\Columns\TextColumn::make('file_size')->label('Size')->state(fn (CourseMaterial $r) => $r->humanSize() ?? '—'),
                Tables\Columns\TextColumn::make('access')->badge()
                    ->color(fn ($state) => $state === 'selected' ? 'warning' : 'success')
                    ->formatStateUsing(fn ($state, CourseMaterial $r) => $state === 'selected'
                        ? 'Restricted ('.$r->allowedUsers()->count().')' : 'All enrolled'),
                Tables\Columns\IconColumn::make('is_published')->boolean()->label('Live'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Add material')
                    ->mutateFormDataUsing(function (array $data) {
                        $data['uploaded_by'] ??= auth()->id();

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('download')->icon('heroicon-o-arrow-down-tray')->color('gray')
                    ->visible(fn (CourseMaterial $r) => $r->isFile())
                    ->url(fn (CourseMaterial $r) => route('admin.materials.download', $r), true),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
