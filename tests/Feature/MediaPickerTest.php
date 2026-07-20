<?php

namespace Tests\Feature;

use App\Filament\Resources\AdvertResource\Pages\CreateAdvert;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;
use TomatoPHP\FilamentMediaManager\Models\Folder;

class MediaPickerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_browse_action_binds_selected_library_image_to_the_field(): void
    {
        $admin = User::where('is_admin', true)->firstOrFail();

        // Put a real image into the media library so it's a valid picker option.
        $folder = Folder::firstOrCreate(['collection' => 'general'], ['name' => 'General', 'is_public' => true]);
        $media = $folder->addMedia(public_path('assets/imgs/banner.jpg'))
            ->preservingOriginal()
            ->toMediaCollection('general');
        $url = $media->getUrl();

        Livewire::actingAs($admin)
            ->test(CreateAdvert::class)
            ->callFormComponentAction('image', 'browse', data: ['media' => $url])
            ->assertHasNoFormComponentActionErrors()
            ->assertFormSet(['image' => $url]);

        Storage::disk('public')->delete($media->id.'/'.$media->file_name);
    }
}
