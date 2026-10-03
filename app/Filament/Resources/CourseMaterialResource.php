<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseMaterialResource\Pages;
use App\Models\CourseMaterial;
use App\Models\MaterialFile;
use App\Models\TrainingCourse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CourseMaterialResource extends Resource
{
    protected static ?string $model = CourseMaterial::class;

    protected static ?string $navigationGroup = 'Training Portal';

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $navigationLabel = 'Course materials';

    protected static ?string $modelLabel = 'material';

    protected static ?int $navigationSort = 6;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count() ?: null;
    }

    /** Shared form used by this resource and the course relation manager. */
    public static function formSchema(bool $withCourse = true): array
    {
        return array_values(array_filter([
            Forms\Components\Section::make('Material')->schema(array_values(array_filter([
                $withCourse ? Forms\Components\Select::make('training_course_id')->label('Course')
                    ->relationship('course', 'name')
                    ->getOptionLabelFromRecordUsing(fn (TrainingCourse $c) => ($c->program?->name ? $c->program->name.' · ' : '').$c->name)
                    ->searchable()->preload()->required() : null,
                // Choose an existing library file OR upload a new one (into a folder) right here.
                Forms\Components\Select::make('material_file_id')
                    ->label('Media')
                    ->relationship('materialFile', 'title')
                    ->getOptionLabelFromRecordUsing(fn (MaterialFile $f) => ($f->folder?->name ? $f->folder->name.' / ' : '')
                        .$f->title.' · '.($f->isFileKind() ? $f->extension() : ucfirst($f->kind)))
                    ->searchable()->preload()->required()
                    ->createOptionForm(MaterialFileResource::uploadSchema())
                    ->createOptionModalHeading('Upload a new file to the Media Library')
                    ->helperText('Choose an existing file/link, or upload a new one — it is saved to your Media Library and can be reused.')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('title')->label('Display title')->required()->maxLength(255)
                    ->helperText('What trainees see for this material in this course.'),
                Forms\Components\TextInput::make('category')->datalist(['Slides', 'Notes', 'Reading', 'Brief', 'Cheatsheet', 'Recording'])
                    ->placeholder('e.g. Slides, Notes'),
                Forms\Components\Textarea::make('description')->rows(2)->columnSpanFull(),
            ])))->columns(2),

            Forms\Components\Section::make('Access & visibility')->schema([
                Forms\Components\Radio::make('access')->options([
                    'enrolled' => 'All enrolled trainees in this program',
                    'selected' => 'Only specific trainees I choose',
                ])->default('enrolled')->required()->live(),
                Forms\Components\Select::make('allowedUsers')
                    ->label('Allowed trainees')
                    ->relationship('allowedUsers', 'name', fn ($query) => $query->where('is_student', true))
                    ->multiple()->searchable()->preload()
                    ->visible(fn (Forms\Get $get) => $get('access') === 'selected')
                    ->required(fn (Forms\Get $get) => $get('access') === 'selected')
                    ->helperText('Only these trainees (and still only if enrolled) can open this material.'),
                Forms\Components\Toggle::make('is_published')->default(true)
                    ->helperText('Hidden from trainees when off.'),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                Forms\Components\Hidden::make('uploaded_by')->default(fn () => auth()->id()),
            ])->columns(2),
        ]));
    }

    public static function form(Form $form): Form
    {
        return $form->schema(static::formSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->wrap()
                    ->description(fn (CourseMaterial $r) => $r->category),
                Tables\Columns\TextColumn::make('course.name')->label('Course')->badge()->color('gray')
                    ->description(fn (CourseMaterial $r) => $r->course?->program?->name),
                Tables\Columns\TextColumn::make('type')->badge()->color(fn ($state) => match ($state) {
                    'file' => 'success', 'image' => 'info', 'video' => 'warning', default => 'gray',
                })->formatStateUsing(fn (CourseMaterial $r) => $r->isFile() ? $r->extension() : ucfirst($r->type)),
                Tables\Columns\TextColumn::make('file_size')->label('Size')
                    ->state(fn (CourseMaterial $r) => $r->humanSize() ?? '—'),
                Tables\Columns\TextColumn::make('access')->badge()
                    ->color(fn ($state) => $state === 'selected' ? 'warning' : 'success')
                    ->formatStateUsing(fn ($state, CourseMaterial $r) => $state === 'selected'
                        ? 'Restricted ('.$r->allowedUsers()->count().')' : 'All enrolled'),
                Tables\Columns\IconColumn::make('is_published')->boolean()->label('Live'),
                Tables\Columns\TextColumn::make('created_at')->since()->label('Added')->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('training_course_id')->label('Course')->relationship('course', 'name')->searchable(),
                Tables\Filters\SelectFilter::make('type')->options([
                    'file' => 'File', 'image' => 'Image', 'link' => 'Link', 'video' => 'Video',
                ]),
                Tables\Filters\SelectFilter::make('access')->options(['enrolled' => 'All enrolled', 'selected' => 'Restricted']),
                Tables\Filters\TernaryFilter::make('is_published')->label('Published'),
            ])
            ->actions([
                Tables\Actions\Action::make('download')->icon('heroicon-o-arrow-down-tray')->color('gray')
                    ->visible(fn (CourseMaterial $r) => $r->isFile())
                    ->url(fn (CourseMaterial $r) => route('admin.materials.download', $r), true),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('publish')->label('Publish')->icon('heroicon-o-eye')->color('success')
                        ->action(fn ($records) => $records->each->update(['is_published' => true]))->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('unpublish')->label('Unpublish')->icon('heroicon-o-eye-slash')->color('gray')
                        ->action(fn ($records) => $records->each->update(['is_published' => false]))->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No materials yet')
            ->emptyStateDescription('Upload documents, slides and media, then choose who can access them.')
            ->emptyStateIcon('heroicon-o-folder-open');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCourseMaterials::route('/'),
            'create' => Pages\CreateCourseMaterial::route('/create'),
            'edit'   => Pages\EditCourseMaterial::route('/{record}/edit'),
        ];
    }
}
