<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Set;
use Illuminate\Support\Facades\Storage;
use TomatoPHP\FilamentMediaManager\Models\Folder;
use TomatoPHP\FilamentMediaManager\Models\Media;

/**
 * Reusable image field backed by the central Media Library. Admins either pick
 * existing library images or upload new ones straight into the library. Stores a
 * plain root-relative URL string (single) or array of strings (multiple), so it
 * renders anywhere via media_url() and preserves existing values.
 */
class MediaPicker extends Field
{
    protected string $view = 'filament.forms.components.media-picker';

    protected bool $multiple = false;

    public function multiple(bool $condition = true): static
    {
        $this->multiple = $condition;

        return $this;
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->default(fn () => $this->isMultiple() ? [] : null);

        $this->registerActions([
            fn (MediaPicker $component): Action => Action::make('browse')
                ->label($component->isMultiple() ? 'Add from Library' : 'Browse Library')
                ->icon('heroicon-m-photo')
                ->color('gray')
                ->size('sm')
                ->modalHeading('Media Library')
                ->modalWidth('lg')
                ->modalSubmitActionLabel('Add')
                ->form([
                    Select::make('media')
                        ->label($component->isMultiple() ? 'Choose images' : 'Choose an image')
                        ->options(fn (): array => static::libraryOptions())
                        ->searchable()
                        ->allowHtml()
                        ->native(false)
                        ->multiple($component->isMultiple())
                        ->required(),
                ])
                ->action(fn (array $data, Set $set) => static::append($component, $set, $data['media'])),

            fn (MediaPicker $component): Action => Action::make('upload')
                ->label('Upload')
                ->icon('heroicon-m-arrow-up-tray')
                ->color('gray')
                ->size('sm')
                ->modalHeading('Upload to Media Library')
                ->modalSubmitActionLabel('Upload')
                ->form([
                    FileUpload::make('file')
                        ->label($component->isMultiple() ? 'Images' : 'Image')
                        ->image()
                        ->disk('public')
                        ->directory('library-tmp')
                        ->multiple($component->isMultiple())
                        ->required(),
                ])
                ->action(function (array $data, Set $set) use ($component) {
                    $files = $component->isMultiple() ? ($data['file'] ?? []) : [$data['file']];
                    $urls = collect($files)->filter()->map(fn ($f) => static::addToLibrary($f))->filter()->all();
                    static::append($component, $set, $urls);
                }),

            fn (MediaPicker $component): Action => Action::make('clear')
                ->label($component->isMultiple() ? 'Clear all' : 'Clear')
                ->icon('heroicon-m-x-mark')
                ->color('danger')
                ->size('sm')
                ->link()
                ->visible(fn (): bool => filled($component->getState()))
                ->requiresConfirmation()
                ->action(fn (Set $set) => $set($component->getName(), $component->isMultiple() ? [] : null)),
        ]);
    }

    protected static function append(MediaPicker $component, Set $set, string|array $new): void
    {
        $new = array_values(array_filter((array) $new));

        if ($component->isMultiple()) {
            $current = array_values(array_filter((array) $component->getState()));
            $set($component->getName(), array_values(array_unique([...$current, ...$new])));
        } else {
            $set($component->getName(), $new[0] ?? null);
        }
    }

    /** Library images as [url => thumbnail-html] for the searchable picker. */
    public static function libraryOptions(): array
    {
        return Media::query()->latest()->limit(300)->get()
            ->filter(fn (Media $m) => str_contains((string) $m->mime_type, 'image'))
            ->mapWithKeys(fn (Media $m) => [
                $m->getUrl() => '<span class="flex items-center gap-2"><img src="'.e($m->getUrl())
                    .'" class="h-8 w-8 rounded object-cover border border-gray-200" /> '.e($m->name).'</span>',
            ])->toArray();
    }

    /** Move an uploaded temp file into the General library folder; return its URL. */
    protected static function addToLibrary(string $tmpPath): ?string
    {
        $folder = Folder::firstOrCreate(
            ['collection' => 'general'],
            ['name' => 'General', 'description' => 'Uploaded media', 'icon' => 'heroicon-o-folder', 'is_public' => true],
        );

        $absolute = Storage::disk('public')->path($tmpPath);

        if (! is_file($absolute)) {
            return null;
        }

        return $folder->addMedia($absolute)->toMediaCollection($folder->collection)->getUrl();
    }
}
