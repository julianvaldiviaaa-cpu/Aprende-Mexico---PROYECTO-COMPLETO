<?php

namespace Database\Seeders;

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
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\User;
use App\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EducationalPlatformSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $teacher = User::factory()->create(['role' => UserRole::Instructor]);
            $student = User::factory()->create();
            InstructorProfile::factory()->approved()->for($teacher)->create();
            $institution = Institution::factory()->for($teacher, 'owner')->create(['name' => 'Escuela de demostración']);
            InstitutionMember::factory()->for($institution)->for($teacher)->create(['role' => 'administrator']);
            $category = Category::factory()->create(['name' => 'Matemáticas']);
            $course = Course::factory()->institutional($institution)->for($teacher, 'instructor')->create([
                'title' => 'Introducción al álgebra',
                'status' => 'published',
                'published_at' => now(),
            ]);
            $course->categories()->attach($category);
            $module = CourseModule::factory()->for($course)->create(['title' => 'Conceptos básicos']);
            $lesson = Lesson::factory()->for($module, 'module')->create([
                'title' => 'Variables y expresiones',
                'content' => 'Una variable representa un valor.',
            ]);
            $activity = Activity::factory()->for($lesson)->create(['title' => 'Explica una expresión algebraica']);
            $quiz = Quiz::factory()->for($lesson)->create(['title' => 'Repaso de variables']);
            $question = Question::factory()->for($quiz)->create(['prompt' => '¿Qué representa una variable?']);
            QuestionOption::factory()->for($question)->create(['content' => 'Un valor que puede cambiar', 'is_correct' => true]);
            QuestionOption::factory()->for($question)->create(['content' => 'Una operación fija', 'position' => 1]);
            $enrollment = Enrollment::factory()->for($course)->for($student)->create();
            ActivitySubmission::factory()->for($activity)->for($enrollment)->for($teacher, 'grader')->create([
                'response' => 'Una expresión combina variables y operaciones.',
                'status' => 'graded',
                'submitted_at' => now(),
                'score' => 90,
                'feedback' => 'Buen trabajo.',
                'graded_at' => now(),
            ]);
            $classroom = Classroom::factory()->for($institution)->for($teacher, 'creator')->create(['name' => 'Grupo de álgebra']);
            ClassroomMember::factory()->for($classroom)->for($teacher)->create(['role' => 'teacher']);
            ClassroomMember::factory()->for($classroom)->for($student)->create();
            CourseAssignment::factory()->for($classroom)->for($course)->for($teacher, 'assigner')->create();
        });
    }
}
