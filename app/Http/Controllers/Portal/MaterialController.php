<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ClassResource;
use App\Models\CourseMaterial;
use App\Models\TrainingCourse;
use App\Models\TrainingProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    /**
     * Every slide, document and link the trainee can reach, in one searchable place.
     * Course documents and per-class slides are normalised into a single list.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $programIds = $user->accessibleProgramIds();

        $search = trim((string) $request->query('q'));
        $kind = $request->query('kind');              // files | links
        $programId = (int) $request->query('program') ?: null;

        $scopedProgramIds = $programId && in_array($programId, $programIds, true)
            ? [$programId]
            : $programIds;

        $courses = TrainingCourse::whereIn('training_program_id', $scopedProgramIds)
            ->with('program')
            ->orderBy('training_program_id')->orderBy('sort_order')
            ->get();

        $courseIds = $courses->pluck('id')->all();

        // Course-level documents (already permission-aware per material).
        $documents = CourseMaterial::published()
            ->whereIn('training_course_id', $courseIds)
            ->with('course.program', 'materialFile')
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (CourseMaterial $m) => $m->canBeAccessedBy($user))
            ->map(fn (CourseMaterial $m) => $this->normaliseMaterial($m));

        // Class-level slides and links.
        $slides = ClassResource::published()
            ->whereHas('trainingClass', fn ($q) => $q->whereIn('training_course_id', $courseIds))
            ->with('trainingClass.course.program')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (ClassResource $r) => $this->normaliseResource($r));

        $items = $documents->concat($slides)
            ->when($kind === 'files', fn ($c) => $c->where('is_file', true))
            ->when($kind === 'links', fn ($c) => $c->where('is_file', false))
            ->when($search !== '', fn ($c) => $c->filter(
                fn (array $i) => str_contains(mb_strtolower($i['title'].' '.$i['description'].' '.$i['course'].' '.$i['context']), mb_strtolower($search))
            ))
            ->sortBy([['program', 'asc'], ['course', 'asc'], ['title', 'asc']])
            ->values();

        return view('portal.materials', [
            'items'    => $items,
            'groups'   => $items->groupBy('course'),
            'programs' => TrainingProgram::whereIn('id', $programIds)->orderBy('name')->get(),
            'filters'  => ['q' => $search, 'kind' => $kind, 'program' => $programId],
            'counts'   => [
                'all'   => $items->count(),
                'files' => $items->where('is_file', true)->count(),
                'links' => $items->where('is_file', false)->count(),
            ],
        ]);
    }

    /** Stream a permission-gated course document to an authorised trainee. */
    public function download(CourseMaterial $material)
    {
        $material->loadMissing('course', 'materialFile');

        abort_unless($material->canBeAccessedBy(auth()->user()), 403, 'You do not have access to this material.');

        // External link/video → just redirect out.
        if (! $material->isFile()) {
            $url = $material->url ?: $material->materialFile?->url;
            abort_unless((bool) $url, 404);

            return redirect()->away($url);
        }

        $disk = Storage::disk($material->effectiveDisk());
        $path = $material->effectivePath();
        abort_unless($path && $disk->exists($path), 404, 'File not found.');

        return $disk->download($path, $material->effectiveFileName());
    }

    /** Stream a permission-gated class slide/resource to an authorised trainee. */
    public function openResource(ClassResource $resource)
    {
        $resource->loadMissing('trainingClass.course');

        abort_unless($resource->canBeAccessedBy(auth()->user()), 403, 'You do not have access to this material.');

        if (! $resource->isFile()) {
            abort_unless((bool) $resource->url, 404);

            return redirect()->away($resource->url);
        }

        $disk = Storage::disk($resource->disk ?: 'public');
        abort_unless($disk->exists($resource->file_path), 404, 'File not found.');

        return $disk->download($resource->file_path, $resource->file_name ?: basename($resource->file_path));
    }

    /* ── Normalisers: one shape for both sources so the view stays simple ── */

    protected function normaliseMaterial(CourseMaterial $m): array
    {
        return [
            'title'       => $m->title,
            'description' => (string) $m->description,
            'category'    => $m->category ?: 'Course document',
            'context'     => 'Course document',
            'course'      => $m->course?->name ?: '—',
            'course_id'   => $m->training_course_id,
            'program'     => $m->course?->program?->name ?: '—',
            'url'         => $m->accessUrl(),
            'is_file'     => $m->isFile(),
            'icon'        => $m->icon(),
            'meta'        => $m->isFile()
                ? trim($m->extension().($m->humanSize() ? ' · '.$m->humanSize() : ''))
                : 'External '.$m->type,
            'restricted'  => $m->access === 'selected',
            'link'        => $m->course ? route('portal.course', $m->course) : null,
        ];
    }

    protected function normaliseResource(ClassResource $r): array
    {
        $class = $r->trainingClass;

        return [
            'title'       => $r->title,
            'description' => (string) $r->description,
            'category'    => $r->category ?: 'Class material',
            'context'     => $class?->title ?: 'Class material',
            'course'      => $class?->course?->name ?: '—',
            'course_id'   => $class?->training_course_id,
            'program'     => $class?->course?->program?->name ?: '—',
            'url'         => $r->accessUrl(),
            'is_file'     => $r->isFile(),
            'icon'        => $r->icon(),
            'meta'        => $r->isFile()
                ? trim($r->extension().($r->humanSize() ? ' · '.$r->humanSize() : ''))
                : $r->actionLabel(),
            'restricted'  => false,
            'link'        => $class ? route('portal.class', $class) : null,
        ];
    }
}
