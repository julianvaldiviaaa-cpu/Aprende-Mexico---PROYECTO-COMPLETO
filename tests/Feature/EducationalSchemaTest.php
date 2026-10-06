<?php

use App\Models\ActivitySubmission;
use App\Models\ClassroomMember;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Enrollment;
use App\Models\InstitutionMember;
use App\Models\LessonProgress;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\User;
use Database\Seeders\EducationalPlatformSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('creates and rolls back the simplified schema on an isolated empty database', function () {
    config(['database.connections.schema_check' => [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'foreign_key_constraints' => true,
    ]]);

    $this->artisan('migrate', ['--database' => 'schema_check', '--force' => true])->assertSuccessful();

    expect(Schema::connection('schema_check')->hasTable('users'))->toBeTrue();
    expect(Schema::connection('schema_check')->hasTable('quiz_answers'))->toBeTrue();
    expect(Schema::connection('schema_check')->hasTable('course_assignments'))->toBeTrue();
    expect(Schema::connection('schema_check')->hasTable('course_versions'))->toBeFalse();

    $this->artisan('migrate:rollback', ['--database' => 'schema_check', '--force' => true])->assertSuccessful();

    expect(Schema::connection('schema_check')->hasTable('users'))->toBeFalse();
    expect(Schema::connection('schema_check')->hasTable('courses'))->toBeFalse();

    $this->artisan('migrate', ['--database' => 'schema_check', '--force' => true])->assertSuccessful();

    DB::purge('schema_check');
});

test('upgrades the original Laravel schema while preserving existing users', function () {
    config(['database.connections.schema_upgrade' => [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'foreign_key_constraints' => true,
    ]]);

    $this->artisan('migrate', [
        '--database' => 'schema_upgrade',
        '--path' => [
            'database/migrations/0001_01_01_000000_create_users_table.php',
            'database/migrations/0001_01_01_000001_create_cache_table.php',
            'database/migrations/0001_01_01_000002_create_jobs_table.php',
        ],
        '--force' => true,
    ])->assertSuccessful();

    DB::connection('schema_upgrade')->table('users')->insert(User::factory()->raw([
        'name' => 'Estudiante existente',
        'email' => 'existing@example.test',
    ]));

    $this->artisan('migrate', ['--database' => 'schema_upgrade', '--force' => true])->assertSuccessful();

    $this->assertDatabaseHas('users', [
        'email' => 'existing@example.test',
        'name' => 'Estudiante existente',
        'status' => 'active',
        'is_platform_admin' => false,
    ], 'schema_upgrade');

    $this->artisan('migrate:rollback', ['--database' => 'schema_upgrade', '--force' => true])->assertSuccessful();

    expect(Schema::connection('schema_upgrade')->hasColumn('users', 'status'))->toBeFalse();
    $this->assertDatabaseHas('users', ['email' => 'existing@example.test'], 'schema_upgrade');

    DB::purge('schema_upgrade');
});

test('rejects duplicate central records', function (string $modelClass, array $keys) {
    $record = $modelClass::factory()->create();

    expect(fn () => $modelClass::factory()->create($record->only($keys)))->toThrow(QueryException::class);
})->with([
    'enrollment' => [Enrollment::class, ['user_id', 'course_id']],
    'institution membership' => [InstitutionMember::class, ['institution_id', 'user_id']],
    'classroom membership' => [ClassroomMember::class, ['classroom_id', 'user_id']],
    'course assignment' => [CourseAssignment::class, ['classroom_id', 'course_id']],
    'lesson progress' => [LessonProgress::class, ['enrollment_id', 'lesson_id']],
    'activity submission' => [ActivitySubmission::class, ['activity_id', 'enrollment_id']],
    'quiz attempt' => [QuizAttempt::class, ['quiz_id', 'enrollment_id', 'attempt_number']],
    'quiz answer' => [QuizAnswer::class, ['quiz_attempt_id', 'question_id']],
]);

test('rejects courses referencing a missing instructor', function () {
    expect(fn () => Course::factory()->create(['instructor_id' => 999999]))->toThrow(QueryException::class);
});

test('preserves academic history when an enrolled account is deleted', function () {
    $enrollment = Enrollment::factory()->create();
    $student = $enrollment->user;

    $student->delete();

    $this->assertSoftDeleted($student);
    $this->assertModelExists($enrollment);
    expect(fn () => $student->forceDelete())->toThrow(QueryException::class);
});

test('seeds a connected course with a graded submission and school group', function () {
    $this->seed(EducationalPlatformSeeder::class);

    $course = Course::query()->where('status', 'published')->sole();
    $enrollment = $course->enrollments()->sole();

    expect($course->instructor->instructorProfile->status)->toBe('approved');
    expect($enrollment->activitySubmissions()->sole()->score)->toBe('90.00');
    expect($course->classrooms()->sole()->users()->count())->toBe(2);
});
