<?php

namespace Tests\Feature;

use App\Models\ClassResource;
use App\Models\TrainingClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortalMaterialsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function student(): User
    {
        return User::where('email', 'student@latechify.test')->firstOrFail();
    }

    /** A trainee enrolled in a different program (Backend, not Frontend). */
    protected function outsider(): User
    {
        return User::create([
            'name' => 'Outsider', 'email' => 'outsider@example.com',
            'password' => bcrypt('secret123'), 'is_student' => true, 'is_active' => true,
        ]);
    }

    protected function frontendClass(): TrainingClass
    {
        return TrainingClass::whereHas('course.program', fn ($q) => $q->where('slug', 'frontend-development'))->firstOrFail();
    }

    /* ── Materials hub ── */

    public function test_the_materials_hub_lists_the_trainees_slides_and_links(): void
    {
        $this->actingAs($this->student())
            ->get(route('portal.materials'))
            ->assertOk()
            ->assertSee('Slides &amp; materials', false)
            ->assertSee('Class slides')
            ->assertSee('Reference notes');
    }

    public function test_the_materials_hub_can_be_filtered_to_link_resources(): void
    {
        $this->actingAs($this->student())
            ->get(route('portal.materials', ['kind' => 'links']))
            ->assertOk()
            ->assertSee('Class slides');
    }

    public function test_the_materials_hub_search_narrows_the_list(): void
    {
        $this->actingAs($this->student())
            ->get(route('portal.materials', ['q' => 'Reference notes']))
            ->assertOk()
            ->assertSee('Reference notes')
            ->assertDontSee('Class slides');
    }

    public function test_a_trainee_only_sees_materials_for_programs_they_can_access(): void
    {
        $response = $this->actingAs($this->student())->get(route('portal.materials'));

        // The demo student is on Frontend only — Backend courses must not leak in.
        $response->assertOk()->assertDontSee('Node.js');
    }

    public function test_the_materials_hub_needs_a_signed_in_trainee(): void
    {
        $this->get(route('portal.materials'))->assertRedirect(route('portal.login'));
    }

    /* ── Gated resource access ── */

    public function test_an_enrolled_trainee_can_open_a_class_resource(): void
    {
        $resource = $this->frontendClass()->resources()->firstOrFail();

        $this->actingAs($this->student())
            ->get(route('portal.resources.open', $resource))
            ->assertRedirect($resource->url);   // external link → redirected out
    }

    public function test_a_trainee_cannot_open_a_resource_from_another_program(): void
    {
        $resource = $this->frontendClass()->resources()->firstOrFail();

        $this->actingAs($this->outsider())
            ->get(route('portal.resources.open', $resource))
            ->assertForbidden();
    }

    public function test_an_unpublished_resource_is_not_reachable(): void
    {
        $resource = $this->frontendClass()->resources()->firstOrFail();
        $resource->update(['is_published' => false]);

        $this->actingAs($this->student())
            ->get(route('portal.resources.open', $resource))
            ->assertForbidden();
    }

    public function test_a_private_upload_is_streamed_only_to_an_enrolled_trainee(): void
    {
        Storage::fake('local');

        $resource = ClassResource::create([
            'training_class_id' => $this->frontendClass()->id,
            'title'             => 'Week 1 deck',
            'description'       => 'The slides from the live session.',
            'category'          => 'Slides',
            'type'              => 'file',
            'disk'              => 'local',
            'file_path'         => UploadedFile::fake()->create('week-1.pdf', 12)
                ->storeAs('training/resources', 'week-1.pdf', ['disk' => 'local']),
            'is_published'      => true,
        ]);

        $this->actingAs($this->student())
            ->get(route('portal.resources.open', $resource))
            ->assertOk()
            ->assertDownload('week-1.pdf');

        $this->actingAs($this->outsider())
            ->get(route('portal.resources.open', $resource))
            ->assertForbidden();
    }

    public function test_an_uploaded_resource_captures_its_size_on_save(): void
    {
        Storage::fake('local');

        // UploadedFile::fake()->create() only *reports* a size, so write real bytes here.
        $path = 'training/resources/notes.pdf';
        Storage::disk('local')->put($path, str_repeat('x', 2048));

        $resource = ClassResource::create([
            'training_class_id' => $this->frontendClass()->id,
            'title'             => 'Notes',
            'type'              => 'file',
            'disk'              => 'local',
            'file_path'         => $path,
        ]);

        $this->assertSame('notes.pdf', $resource->file_name);
        $this->assertGreaterThan(0, $resource->file_size);
        $this->assertNotNull($resource->humanSize());
        $this->assertSame('PDF', $resource->extension());
    }
}
