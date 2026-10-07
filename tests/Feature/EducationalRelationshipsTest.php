<?php

use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\Category;
use App\Models\Classroom;
use App\Models\ClassroomMember;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\CourseModule;
use App\Models\Enrollment;
use App\Models\Institution;
use App\Models\InstitutionMember;
use App\Models\InstructorProfile;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\User;
use App\UserRole;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Schema;

test('persists every retained model and resolves its documented relationships', function (string $modelClass) {
    $model = $modelClass::factory()->create();

    $this->assertModelExists($model);

    foreach ((new ReflectionClass($model))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        $returnType = $method->getReturnType();

        if ($method->getDeclaringClass()->getName() === $modelClass
            && $returnType instanceof ReflectionNamedType
            && is_a($returnType->getName(), Relation::class, true)) {
            $model->{$method->getName()}()->getResults();
        }
    }

    $documentation = (new ReflectionClass($model))->getDocComment();

    foreach (Schema::getColumnListing($model->getTable()) as $column) {
        expect($documentation)->toContain('$'.$column);
    }
})->with([
    User::class,
    InstructorProfile::class,
    Institution::class,
    InstitutionMember::class,
    Category::class,
    Course::class,
    CourseModule::class,
    Lesson::class,
    Enrollment::class,
    LessonProgress::class,
    Activity::class,
    ActivitySubmission::class,
    Quiz::class,
    Question::class,
    QuestionOption::class,
    QuizAttempt::class,
    QuizAnswer::class,
    Classroom::class,
    ClassroomMember::class,
    CourseAssignment::class,
]);

test('supports independent and institutional courses with the same simple structure', function () {
    $teacher = User::factory()->create();
    $institution = Institution::factory()->for($teacher, 'owner')->create();
    $independentCourse = Course::factory()->for($teacher, 'instructor')->create();
    $institutionalCourse = Course::factory()->institutional($institution)->for($teacher, 'instructor')->create();
    InstitutionMember::factory()->for($institution)->for($teacher)->create(['role' => 'instructor']);

    expect($independentCourse->institution)->toBeNull();
    expect($institutionalCourse->institution->is($institution))->toBeTrue();
    expect($teacher->taughtCourses->modelKeys())->toBe([$independentCourse->id, $institutionalCourse->id]);
    expect($teacher->institutions->modelKeys())->toBe([$institution->id]);
});

test('stores lesson progress and manual grades directly in the learning records', function () {
    $course = Course::factory()->create();
    $module = CourseModule::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($module, 'module')->create();
    $enrollment = Enrollment::factory()->for($course)->create();
    $progress = LessonProgress::factory()->for($lesson)->for($enrollment)->create(['is_completed' => true, 'completed_at' => now()]);
    $activity = Activity::factory()->for($lesson)->create();
    $submission = ActivitySubmission::factory()->for($activity)->for($enrollment)->create([
        'response' => 'Mi respuesta',
        'score' => 85.5,
        'feedback' => 'Buen trabajo',
        'status' => 'graded',
        'graded_at' => now(),
    ]);

    expect($course->modules->first()->lessons->modelKeys())->toBe([$lesson->id]);
    expect($progress->enrollment->is($enrollment))->toBeTrue();
    expect($progress->fresh()->is_completed)->toBeTrue();
    expect($submission->fresh()->score)->toBe('85.50');
    expect($submission->fresh()->graded_at)->toBeInstanceOf(CarbonImmutable::class);
    expect($enrollment->activitySubmissions->first()->response)->toBe('Mi respuesta');
});

test('connects quiz attempts to questions and structured answers', function () {
    $quiz = Quiz::factory()->create();
    $question = Question::factory()->for($quiz)->create();
    $option = QuestionOption::factory()->for($question)->create(['is_correct' => true]);
    $attempt = QuizAttempt::factory()->for($quiz)->create();
    $answer = QuizAnswer::factory()->for($question)->for($attempt, 'attempt')->create(['response' => ['option_id' => $option->id]]);

    expect($attempt->answers->first()->is($answer))->toBeTrue();
    expect($answer->question->quiz->is($quiz))->toBeTrue();
    expect($answer->fresh()->response)->toBe(['option_id' => $option->id]);
    expect($option->fresh()->is_correct)->toBeTrue();
    expect($option->toArray())->not->toHaveKey('is_correct');
});

test('assigns a course to a group and follows the existing student enrollment', function () {
    $classroom = Classroom::factory()->create();
    $course = Course::factory()->create();
    $student = User::factory()->create();
    $enrollment = Enrollment::factory()->for($course)->for($student)->create();
    ClassroomMember::factory()->for($classroom)->for($student)->create();
    CourseAssignment::factory()->for($classroom)->for($course)->create();

    expect($classroom->courses->modelKeys())->toBe([$course->id]);
    expect($classroom->users->first()->enrollments->first()->is($enrollment))->toBeTrue();
    expect($course->institution_id)->toBeNull();
});

test('keeps administrative user attributes outside mass assignment', function () {
    $user = User::factory()->create()->refresh();

    $user->fill(['role' => 'admin', 'status' => 'suspended']);

    expect($user->role)->toBe(UserRole::User);
    expect($user->status)->toBe('active');
    expect($user->toArray())->not->toHaveKeys(['password', 'remember_token']);
});

test('keeps unused controller templates free of declared methods', function () {
    foreach (glob(app_path('Http/Controllers/*/*Controller.php')) as $path) {
        $area = basename(dirname($path));
        $name = basename($path, '.php');
        if (in_array($area.'/'.$name, ['Identity/RegistrationController', 'Identity/SessionController', 'Identity/InstructorProfileController', 'Catalog/CategoryController', 'Catalog/CourseController'], true)) {
            continue;
        }
        $controller = new ReflectionClass('App\\Http\\Controllers\\'.$area.'\\'.$name);
        $declaredMethods = array_filter($controller->getMethods(), fn (ReflectionMethod $method): bool => $method->getDeclaringClass()->getName() === $controller->getName());

        expect($declaredMethods)->toBeEmpty();
    }
});
