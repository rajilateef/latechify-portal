<?php

namespace App\Filament\Resources\TrainingClassResource\RelationManagers;

use App\Models\ClassResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ResourcesRelationManager extends RelationManager
{
    protected static string $relationship = 'resources';

    protected static ?string $title = 'Slides, materials & links';

    protected static ?string $modelLabel = 'resource';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required()->columnSpanFull(),

            Forms\Components\Select::make('type')
                ->options([
                    'file'  => 'Uploaded file (slides, PDF, notes…)',
                    'link'  => 'External link resource',
                    'video' => 'Video link',
                ])
                ->default('file')->required()->live(),

            Forms\Components\TextInput::make('category')
                ->label('Category')
                ->datalist(['Slides', 'Notes', 'Reading', 'Exercise', 'Reference', 'Cheatsheet', 'Tool'])
                ->placeholder('Slides')
                ->helperText('Shown as a tag in the portal. Optional.'),

            Forms\Components\Textarea::make('description')
                ->rows(2)->maxLength(500)
                ->helperText('A line of context for the trainee — what this is and why it matters.')
                ->columnSpanFull(),

            Forms\Components\Select::make('disk')
                ->label('Storage')
                ->options(['public' => 'Public (direct link)', 'local' => 'Private (portal-gated download)'])
                ->default('local')
                ->required()
                ->visible(fn (Forms\Get $get) => $get('type') === 'file')
                ->helperText('Private keeps the file off the public web — only enrolled trainees can download it.'),

            Forms\Components\FileUpload::make('file_path')
                ->label('File (PDF, PPT, DOCX, image…)')
                ->disk(fn (Forms\Get $get) => $get('disk') ?: 'local')
                ->directory('training/resources')
                ->preserveFilenames()
                ->visible(fn (Forms\Get $get) => $get('type') === 'file')
                ->requiredIf('type', 'file')
                ->columnSpanFull(),

            Forms\Components\TextInput::make('url')
                ->label('Link (Google Slides, Drive, YouTube, docs…)')
                ->url()
                ->visible(fn (Forms\Get $get) => in_array($get('type'), ['link', 'video']))
                ->requiredIf('type', ['link', 'video'])
                ->columnSpanFull(),

            Forms\Components\Toggle::make('is_published')
                ->label('Visible to trainees')->default(true),

            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()
                    ->description(fn (ClassResource $r) => $r->description ? \Illuminate\Support\Str::limit($r->description, 60) : null),
                Tables\Columns\TextColumn::make('type')->badge()
                    ->color(fn ($state) => match ($state) { 'file' => 'success', 'video' => 'warning', default => 'info' }),
                Tables\Columns\TextColumn::make('category')->badge()->color('gray')->placeholder('—')->toggleable(),
                Tables\Columns\TextColumn::make('access')->label('Access')
                    ->state(fn (ClassResource $r) => $r->isFile() ? (($r->disk ?: 'public') === 'public' ? 'Public file' : 'Gated file') : 'External link')
                    ->badge()
                    ->color(fn (ClassResource $r) => $r->isFile() && ($r->disk ?: 'public') !== 'public' ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('file_size')->label('Size')
                    ->state(fn (ClassResource $r) => $r->humanSize() ?: '—')->toggleable(),
                Tables\Columns\TextColumn::make('url')->limit(40)->toggleable()->url(fn ($record) => $record->url, true)->placeholder('—'),
                Tables\Columns\IconColumn::make('is_published')->label('Visible')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->options([
                    'file' => 'Uploaded file', 'link' => 'External link', 'video' => 'Video link',
                ]),
                Tables\Filters\TernaryFilter::make('is_published')->label('Visible to trainees'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Add resource'),
            ])
            ->actions([
                Tables\Actions\Action::make('open')->label('Open')->icon('heroicon-o-arrow-top-right-on-square')->color('gray')
                    ->url(fn (ClassResource $record) => $record->isFile()
                        ? route('admin.class-resources.download', $record)
                        : $record->url, true)
                    ->visible(fn (ClassResource $record) => (bool) ($record->url || $record->file_path)),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('publish')->label('Make visible')->icon('heroicon-o-eye')->color('success')
                        ->action(fn ($records) => $records->each->update(['is_published' => true]))->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('unpublish')->label('Hide')->icon('heroicon-o-eye-slash')->color('warning')
                        ->action(fn ($records) => $records->each->update(['is_published' => false]))->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
