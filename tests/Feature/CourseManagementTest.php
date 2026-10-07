<?php

use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use App\UserRole;
use Illuminate\Support\Facades\Gate;
use Inertia\Testing\AssertableInertia as Assert;

test('enforces the role permission matrix for courses and categories', function (string $state, bool $canCreateCourses, bool $canManageCategories) {
    $user = User::factory()->{$state}()->create();
    expect(Gate::forUser($user)->allows('create', Course::class))->toBe($canCreateCourses);
    expect(Gate::forUser($user)->allows('create', Category::class))->toBe($canManageCategories);
    expect(Gate::forUser($user)->allows('admin'))->toBe($canManageCategories);
})->with([
    'user' => ['unverified', false, false],
    'instructor' => ['instructor', true, false],
    'admin' => ['admin', true, true],
    'suspended' => ['suspended', false, false],
]);

test('requires authentication to create courses and categories', function () {
    $this->get(route('courses.create'))->assertRedirect(route('login'));
    $this->post(route('courses.store'), [])->assertRedirect(route('login'));
    $this->post(route('categories.store'), [])->assertRedirect(route('login'));
});

test('allows administrators to create categories with automatic slugs', function () {
    $this->actingAs(User::factory()->admin()->create())->post(route('categories.store'), ['name' => 'Matemáticas', 'description' => 'Números y álgebra.'])->assertRedirect(route('categories.index'));
    $category = Category::query()->sole();
    expect($category->slug)->toBe('matematicas');
    expect($category->description)->toBe('Números y álgebra.');
});

test('prevents non administrators from creating categories or seeing the administration screen', function () {
    $this->actingAs(User::factory()->instructor()->create())->post(route('categories.store'), ['name' => 'Privada'])->assertForbidden();
    $this->get(route('categories.index'))->assertForbidden();
    $this->assertDatabaseCount('categories', 0);
});

test('validates required and duplicate category names', function () {
    $this->actingAs(User::factory()->admin()->create())->post(route('categories.store'), [])->assertSessionHasErrors(['name' => 'El nombre de la categoría es obligatorio.']);
    Category::factory()->create(['name' => 'Historia']);
    $this->post(route('categories.store'), ['name' => 'Historia'])->assertSessionHasErrors(['name' => 'Ya existe una categoría con ese nombre.']);
    $this->assertDatabaseCount('categories', 1);
});

test('creates a course with several categories and assigns ownership on the server', function () {
    $this->freezeSecond();
    $teacher = User::factory()->instructor()->create();
    $other = User::factory()->create();
    $categories = Category::factory()->count(2)->create();
    $this->actingAs($teacher)->post(route('courses.store'), [
        'title' => 'Álgebra práctica', 'summary' => 'Aprende desde cero.', 'description' => 'Un primer curso.',
        'level' => 'beginner', 'status' => 'published', 'category_ids' => $categories->modelKeys(),
        'instructor_id' => $other->id, 'institution_id' => 999, 'slug' => 'fake', 'published_at' => '2000-01-01',
    ])->assertRedirect();

    $course = Course::query()->sole();
    expect($course->instructor_id)->toBe($teacher->id);
    expect($course->institution_id)->toBeNull();
    expect($course->slug)->toBe('algebra-practica');
    expect($course->published_at->equalTo(now()))->toBeTrue();
    expect($course->categories()->pluck('categories.id')->all())->toEqualCanonicalizing($categories->modelKeys());
    $this->get(route('courses.edit', $course))->assertOk();
});

test('prevents users and unapproved instructors from creating courses', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('courses.create'))->assertForbidden();
    $this->post(route('courses.store'), [])->assertForbidden();
    $this->assertDatabaseCount('courses', 0);
});

test('requires approved profile even if an account has the instructor role', function () {
    $user = User::factory()->create(['role' => UserRole::Instructor]);
    expect(Gate::forUser($user)->allows('create', Course::class))->toBeFalse();
});

test('validates the course data before saving', function () {
    $this->actingAs(User::factory()->instructor()->create())->post(route('courses.store'), [])
        ->assertSessionHasErrors(['title', 'level', 'status', 'category_ids']);
    $this->assertDatabaseCount('courses', 0);
});

test('rejects invalid category selections', function (array $categoryIds) {
    Category::factory()->create(['id' => 1]);
    $this->actingAs(User::factory()->instructor()->create())->post(route('courses.store'), [
        'title' => 'Curso', 'level' => 'beginner', 'status' => 'draft', 'category_ids' => $categoryIds,
    ])->assertSessionHasErrors();
    $this->assertDatabaseCount('courses', 0);
})->with(['empty' => [[]], 'missing' => [[999999]], 'duplicate' => [[1, 1]]]);

test('updates only an owned course and synchronizes its categories', function () {
    $teacher = User::factory()->instructor()->create();
    $course = Course::factory()->for($teacher, 'instructor')->create();
    $oldCategory = Category::factory()->create();
    $newCategory = Category::factory()->create();
    $course->categories()->attach($oldCategory);
    $this->actingAs($teacher)->put(route('courses.update', $course), [
        'title' => 'Título nuevo', 'level' => 'intermediate', 'status' => 'draft', 'category_ids' => [$newCategory->id],
    ])->assertRedirect(route('courses.edit', $course));
    expect($course->fresh()->slug)->toBe('titulo-nuevo');
    expect($course->categories()->pluck('categories.id')->all())->toBe([$newCategory->id]);
});

test('protects courses belonging to another instructor', function () {
    $course = Course::factory()->create();
    $this->actingAs(User::factory()->instructor()->create())->get(route('courses.edit', $course))->assertForbidden();
    $this->put(route('courses.update', $course), [])->assertForbidden();
});

test('shows published courses and hides other instructors drafts', function () {
    Course::factory()->create(['status' => 'published', 'title' => 'Público']);
    Course::factory()->create(['status' => 'draft', 'title' => 'Privado']);
    $this->actingAs(User::factory()->create())->get(route('courses.index'))->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Catalog/Courses')->has('courses.data', 1)->where('courses.data.0.title', 'Público')->where('courses.data.0.can_edit', false));
});
