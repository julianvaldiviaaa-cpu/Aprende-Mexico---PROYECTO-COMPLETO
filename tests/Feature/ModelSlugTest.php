<?php

use App\Models\Category;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Institution;
use App\Models\InstructorProfile;
use App\Models\Lesson;

test('creates and updates unique slugs from the configured name or title', function (string $modelClass, string $source) {
    $first = $modelClass::factory()->create([$source => 'Álgebra para México']);
    $second = $modelClass::factory()->create([$source => 'Álgebra para México']);

    expect($first->slug)->toBe('algebra-para-mexico');
    expect($second->slug)->toBe('algebra-para-mexico-2');

    $second->update([$source => 'Física básica']);

    $this->assertDatabaseHas($second->getTable(), ['id' => $second->id, 'slug' => 'fisica-basica']);
})->with([
    'category' => [Category::class, 'name'],
    'instructor' => [InstructorProfile::class, 'display_name'],
    'institution' => [Institution::class, 'name'],
    'course' => [Course::class, 'title'],
    'lesson' => [Lesson::class, 'title'],
    'classroom' => [Classroom::class, 'name'],
]);

test('keeps the slug when unrelated attributes change', function () {
    $course = Course::factory()->create(['title' => 'Cálculo']);

    $course->update(['summary' => 'Nueva descripción']);

    $this->assertDatabaseHas('courses', ['id' => $course->id, 'slug' => 'calculo']);
});

test('reserves slugs belonging to soft deleted records', function () {
    $course = Course::factory()->create(['title' => 'Química']);
    $course->delete();

    $newCourse = Course::factory()->create(['title' => 'Química']);

    expect($newCourse->slug)->toBe('quimica-2');
});

test('regenerates an empty slug while excluding the current model', function () {
    $course = Course::factory()->create(['title' => 'Biología']);
    $course->slug = '';

    $course->save();

    $this->assertDatabaseHas('courses', ['id' => $course->id, 'slug' => 'biologia']);
});

test('uses a fallback when the source has no slug characters', function () {
    $category = Category::factory()->create(['name' => '!!!']);

    expect($category->slug)->toBe('category');
});

test('supports long names without exceeding the slug column length', function () {
    $course = Course::factory()->create(['title' => str_repeat('a', 255)]);
    $second = Course::factory()->create(['title' => str_repeat('a', 255)]);

    expect(mb_strlen($course->slug))->toBe(180);
    expect(mb_strlen($second->slug))->toBe(182);
});

test('updates a slug through repeated collisions and preserves the other records', function () {
    Course::factory()->create(['title' => 'Historia']);
    Course::factory()->create(['title' => 'Historia']);
    $course = Course::factory()->create(['title' => 'Geografía']);

    $course->update(['title' => 'Historia']);

    expect($course->fresh()->slug)->toBe('historia-3');
});
