<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Course::class);
        $user = $request->user();
        abort_unless($user instanceof User, 403);
        $courses = Course::query()->with(['categories:id,name,slug', 'instructor:id,name'])
            ->when(! $user->isAdmin(), fn (Builder $query) => $query->where(fn (Builder $query) => $query->where('status', 'published')->orWhere('instructor_id', $user->id)))
            ->orderByDesc('id')->paginate(12);
        $courses->through(fn (Course $course): array => [
            ...$course->only(['id', 'title', 'slug', 'summary', 'level', 'status']),
            'categories' => $course->categories->map->only(['id', 'name']),
            'instructor' => $course->instructor->only(['name']),
            'can_edit' => $user->can('update', $course),
        ]);

        return Inertia::render('Catalog/Courses', ['courses' => $courses]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Course::class);

        return Inertia::render('Catalog/CourseForm', [
            'course' => null,
            'categories' => Category::query()->orderBy('name')->orderBy('id')->get(['id', 'name']),
        ]);
    }

    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);
        $course = DB::transaction(function () use ($request, $user): Course {
            $course = $user->taughtCourses()->create([
                ...$request->safe()->only(['title', 'summary', 'description', 'level', 'status']),
                'published_at' => $request->input('status') === 'published' ? now() : null,
            ]);
            $course->categories()->sync($request->validated('category_ids'));

            return $course;
        });

        return redirect()->route('courses.edit', $course)->with('success', 'Curso creado.');
    }

    public function edit(Course $course): Response
    {
        Gate::authorize('update', $course);

        return Inertia::render('Catalog/CourseForm', [
            'course' => [...$course->only(['id', 'title', 'summary', 'description', 'level', 'status']), 'category_ids' => $course->categories()->pluck('categories.id')],
            'categories' => Category::query()->orderBy('name')->orderBy('id')->get(['id', 'name']),
        ]);
    }

    public function update(StoreCourseRequest $request, Course $course): RedirectResponse
    {
        DB::transaction(function () use ($request, $course): void {
            $course->update([
                ...$request->safe()->only(['title', 'summary', 'description', 'level', 'status']),
                'published_at' => $request->input('status') === 'published' ? ($course->published_at ?? now()) : null,
            ]);
            $course->categories()->sync($request->validated('category_ids'));
        });

        return redirect()->route('courses.edit', $course)->with('success', 'Curso actualizado.');
    }
}
