<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaterialFileResource\Pages;
use App\Models\MaterialFile;
use App\Models\MaterialFolder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class MaterialFileResource extends Resource
{
    protected static ?string $model = MaterialFile::class;

    protected static ?string $navigationGroup = 'Training Portal';

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

    protected static ?string $navigationLabel = 'Media Library';

    protected static ?string $modelLabel = 'file';

    protected static ?string $pluralModelLabel = 'media library';

    protected static ?int $navigationSort = 6;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count() ?: null;
    }

    /** A folder <select> that can also create a new folder inline. */
    public static function folderField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('material_folder_id')
            ->label('Folder')
            ->relationship('folder', 'name')
            ->getOptionLabelFromRecordUsing(fn (MaterialFolder $f) => $f->path())
            ->searchable()->preload()->placeholder('No folder (top level)')
            ->createOptionForm([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\Select::make('parent_id')->label('Inside folder')
                    ->relationship('parent', 'name')->searchable()->placeholder('Top level'),
            ])
            ->createOptionModalHeading('New folder');
    }

    /** Folder <select> for standalone action forms (no relationship context); can create a folder inline. */
    public static function actionFolderField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('material_folder_id')
            ->label('Folder')
            ->options(fn () => MaterialFolder::orderBy('name')->get()->mapWithKeys(fn (MaterialFolder $f) => [$f->id => $f->path()])->all())
            ->searchable()->placeholder('No folder (top level)')
            ->createOptionForm([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\Select::make('parent_id')->label('Inside folder')
                    ->options(fn () => MaterialFolder::orderBy('name')->pluck('name', 'id'))->placeholder('Top level'),
            ])
            ->createOptionUsing(fn (array $data) => MaterialFolder::create($data)->getKey())
            ->createOptionModalHeading('New folder');
    }

    /** Shared upload/new-file schema — reused by the course-material "create new" picker. */
    public static function uploadSchema(): array
    {
        return [
            static::folderField(),
            Forms\Components\TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
            Forms\Components\Select::make('kind')->options([
                'file' => 'Document / file', 'image' => 'Image', 'link' => 'External link', 'video' => 'Video link',
            ])->default('file')->required()->live(),
            Forms\Components\FileUpload::make('file_path')->label('File')
                ->disk('local')->directory('material-library')->visibility('private')
                ->preserveFilenames()->maxSize(102400) // 100 MB
                ->downloadable()
                ->visible(fn (Forms\Get $get) => in_array($get('kind'), ['file', 'image']))
                ->requiredIf('kind', ['file', 'image'])
                ->helperText('Stored privately — shared only with trainees you grant access to.')
                ->columnSpanFull(),
            Forms\Components\TextInput::make('url')->label('Link (Drive, YouTube, Slides…)')->url()
                ->visible(fn (Forms\Get $get) => in_array($get('kind'), ['link', 'video']))
                ->requiredIf('kind', ['link', 'video'])
                ->columnSpanFull(),
            Forms\Components\Hidden::make('uploaded_by')->default(fn () => auth()->id()),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                static::folderField(),
                Forms\Components\Select::make('kind')->options([
                    'file' => 'Document / file', 'image' => 'Image', 'link' => 'External link', 'video' => 'Video link',
                ])->default('file')->required()->live(),

                // CREATE — upload one OR many files at once (each becomes its own library file).
                Forms\Components\FileUpload::make('files')
                    ->label('Files')
                    ->multiple()->reorderable()->appendFiles()
                    ->disk('local')->directory('material-library')->visibility('private')
                    ->preserveFilenames()->maxSize(102400)
                    ->visible(fn (string $operation, Forms\Get $get) => $operation === 'create' && in_array($get('kind'), ['file', 'image']))
                    ->required(fn (string $operation, Forms\Get $get) => $operation === 'create' && in_array($get('kind'), ['file', 'image']))
                    ->helperText('Drag & drop one or more files — each becomes a library file named after the file.')
                    ->columnSpanFull(),

                // EDIT — replace this single file.
                Forms\Components\FileUpload::make('file_path')
                    ->label('File')
                    ->disk('local')->directory('material-library')->visibility('private')
                    ->preserveFilenames()->maxSize(102400)->downloadable()
                    ->visible(fn (string $operation, Forms\Get $get) => $operation === 'edit' && in_array($get('kind'), ['file', 'image']))
                    ->columnSpanFull(),

                // Title — always on edit; on create only for links/videos (files are named from the filename).
                Forms\Components\TextInput::make('title')->required()->maxLength(255)
                    ->visible(fn (string $operation, Forms\Get $get) => $operation === 'edit' || in_array($get('kind'), ['link', 'video']))
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('url')->label('Link (Drive, YouTube, Slides…)')->url()
                    ->visible(fn (Forms\Get $get) => in_array($get('kind'), ['link', 'video']))
                    ->requiredIf('kind', ['link', 'video'])
                    ->columnSpanFull(),

                Forms\Components\Hidden::make('uploaded_by')->default(fn () => auth()->id()),
            ])->columns(2),
        ]);
    }

    /** Turn an uploaded file path into a MaterialFile payload (name + kind from the file). */
    public static function payloadForUpload(string $path, ?int $folderId): array
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $imageExt = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'avif'];

        return [
            'material_folder_id' => $folderId,
            'title'       => Str::title(trim(str_replace(['-', '_'], ' ', pathinfo($path, PATHINFO_FILENAME)))) ?: 'Untitled',
            'kind'        => in_array($ext, $imageExt) ? 'image' : 'file',
            'disk'        => 'local',
            'file_path'   => $path,
            'file_name'   => basename($path),
            'uploaded_by' => auth()->id(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->wrap()
                    ->icon(fn (MaterialFile $r) => match ($r->kind) {
                        'image' => 'heroicon-o-photo', 'video' => 'heroicon-o-video-camera',
                        'link' => 'heroicon-o-link', default => 'heroicon-o-document',
                    }),
                Tables\Columns\TextColumn::make('folder.name')->label('Folder')->badge()->color('gray')
                    ->placeholder('Top level')->description(fn (MaterialFile $r) => $r->folder?->parent?->name),
                Tables\Columns\TextColumn::make('kind')->badge()
                    ->formatStateUsing(fn (MaterialFile $r) => $r->isFileKind() ? $r->extension() : ucfirst($r->kind))
                    ->color(fn ($state) => match ($state) {
                        'file' => 'success', 'image' => 'info', 'video' => 'warning', default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('file_size')->label('Size')->state(fn (MaterialFile $r) => $r->humanSize() ?? '—'),
                Tables\Columns\TextColumn::make('course_materials_count')->counts('courseMaterials')->label('Used by')->badge()->color('primary'),
                Tables\Columns\TextColumn::make('created_at')->since()->label('Added')->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('material_folder_id')->label('Folder')->relationship('folder', 'name')->searchable(),
                Tables\Filters\SelectFilter::make('kind')->options([
                    'file' => 'File', 'image' => 'Image', 'link' => 'Link', 'video' => 'Video',
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('bulkUpload')
                    ->label('Upload files')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->modalHeading('Upload files to the library')
                    ->modalSubmitActionLabel('Upload')
                    ->form([
                        static::actionFolderField(),
                        Forms\Components\FileUpload::make('files')
                            ->label('Files')
                            ->multiple()->reorderable()->appendFiles()
                            ->disk('local')->directory('material-library')->visibility('private')
                            ->preserveFilenames()
                            ->maxSize(102400) // 100 MB each
                            ->required()
                            ->helperText('Drag & drop several documents, slides or images at once — each becomes a library file named after the file.')
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data) {
                        $folderId = $data['material_folder_id'] ?? null;
                        $count = 0;

                        foreach (array_filter((array) ($data['files'] ?? [])) as $path) {
                            MaterialFile::create(static::payloadForUpload($path, $folderId));
                            $count++;
                        }

                        Notification::make()
                            ->title($count.' '.Str::plural('file', $count).' uploaded to the library')
                            ->success()->send();
                    }),
                Tables\Actions\CreateAction::make()->label('New file')->icon('heroicon-o-plus'),
            ])
            ->actions([
                Tables\Actions\Action::make('download')->icon('heroicon-o-arrow-down-tray')->color('gray')
                    ->visible(fn (MaterialFile $r) => $r->isFileKind())
                    ->url(fn (MaterialFile $r) => route('admin.library.download', $r), true),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->emptyStateHeading('Your media library is empty')
            ->emptyStateDescription('Upload documents, slides, images and links, organise them in folders, then attach them to courses.')
            ->emptyStateIcon('heroicon-o-folder-open')
            ->emptyStateActions([Tables\Actions\CreateAction::make()->label('Upload file')]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMaterialFiles::route('/'),
            'create' => Pages\CreateMaterialFile::route('/create'),
            'edit'   => Pages\EditMaterialFile::route('/{record}/edit'),
        ];
    }
}
