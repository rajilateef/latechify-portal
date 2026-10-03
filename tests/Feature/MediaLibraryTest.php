<?php

namespace Tests\Feature;

use App\Filament\Resources\MaterialFileResource;
use App\Models\MaterialFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_payload_names_and_types_files_from_the_filename(): void
    {
        $pdf = MaterialFileResource::payloadForUpload('material-library/intro-to-css.pdf', 5);
        $this->assertSame('Intro To Css', $pdf['title']);
        $this->assertSame('file', $pdf['kind']);
        $this->assertSame(5, $pdf['material_folder_id']);

        $img = MaterialFileResource::payloadForUpload('material-library/hero_banner.PNG', null);
        $this->assertSame('Hero Banner', $img['title']);
        $this->assertSame('image', $img['kind']);
    }

    public function test_multiple_files_fan_out_into_separate_library_entries(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('material-library/intro-to-css.pdf', 'x');
        Storage::disk('local')->put('material-library/hero_banner.png', 'yy');

        $before = MaterialFile::count();

        // What the create page / bulk-upload action does for each uploaded file.
        foreach (['material-library/intro-to-css.pdf', 'material-library/hero_banner.png'] as $path) {
            MaterialFile::create(MaterialFileResource::payloadForUpload($path, null));
        }

        $this->assertSame($before + 2, MaterialFile::count());
        $this->assertDatabaseHas('material_files', ['title' => 'Intro To Css', 'kind' => 'file']);
        $this->assertDatabaseHas('material_files', ['title' => 'Hero Banner', 'kind' => 'image']);

        // Metadata (size) captured by the model's saving hook.
        $this->assertSame(2, MaterialFile::where('title', 'Hero Banner')->value('file_size'));
    }
}
