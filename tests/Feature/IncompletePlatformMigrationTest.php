<?php

use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('repairs migrations recorded before their schema was completed and preserves existing data', function (bool $missingCategoryId, bool $missingCourseId) {
    config(['database.connections.incomplete' => ['driver' => 'sqlite', 'database' => ':memory:', 'foreign_key_constraints' => true]]);
    $oldMigrations = array_map(fn (string $path): string => 'database/migrations/'.basename($path), [
        ...glob(database_path('migrations/0001_*.php')),
        ...glob(database_path('migrations/2026_10_04_*.php')),
    ]);
    $this->artisan('migrate', ['--database' => 'incomplete', '--path' => $oldMigrations, '--force' => true])->assertSuccessful();
    $schema = Schema::connection('incomplete');
    $connection = DB::connection('incomplete');
    $schema->table('users', function (Blueprint $table): void {
        $table->string('role')->change();
    });
    $schema->create('category_course', function (Blueprint $table) use ($missingCategoryId, $missingCourseId): void {
        $table->id();
        if (! $missingCategoryId) {
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
        }
        if (! $missingCourseId) {
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
        }
        $table->timestamps();
    });
    $connection->table('migrations')->insert([
        ['migration' => '2026_10_07_051327_use_enum_roles_for_users', 'batch' => 1],
        ['migration' => '2026_10_07_051328_create_category_course_table', 'batch' => 1],
    ]);
    $adminId = $connection->table('users')->insertGetId(['name' => 'Admin anterior', 'email' => 'admin@example.test', 'role' => 'user', 'password' => 'unused', 'is_platform_admin' => true]);
    $newAdminId = $connection->table('users')->insertGetId(['name' => 'Admin actual', 'email' => 'current-admin@example.test', 'role' => 'admin', 'password' => 'unused']);
    $teacherId = $connection->table('users')->insertGetId(['name' => 'Docente', 'email' => 'teacher@example.test', 'role' => 'user', 'password' => 'unused']);
    $connection->table('instructor_profiles')->insert(['user_id' => $teacherId, 'display_name' => 'Docente', 'slug' => 'docente', 'status' => 'approved']);
    $categoryId = $connection->table('categories')->insertGetId(['name' => 'Historia', 'slug' => 'historia']);
    $otherCategoryId = $connection->table('categories')->insertGetId(['name' => 'Arte', 'slug' => 'arte']);
    $courseId = $connection->table('courses')->insertGetId(['instructor_id' => $teacherId, 'category_id' => $categoryId, 'title' => 'Historia local', 'slug' => 'historia-local']);

    $this->artisan('migrate', ['--database' => 'incomplete', '--force' => true])->assertSuccessful();

    $this->assertDatabaseHas('category_course', ['category_id' => $categoryId, 'course_id' => $courseId], 'incomplete');
    $this->assertDatabaseHas('users', ['id' => $adminId, 'role' => 'admin'], 'incomplete');
    $this->assertDatabaseHas('users', ['id' => $newAdminId, 'role' => 'admin'], 'incomplete');
    $this->assertDatabaseHas('users', ['id' => $teacherId, 'role' => 'instructor'], 'incomplete');
    expect($schema->hasColumn('courses', 'category_id'))->toBeFalse();
    expect($schema->hasColumn('users', 'is_platform_admin'))->toBeFalse();
    expect(Category::on('incomplete')->withCount('courses')->findOrFail($categoryId)->courses_count)->toBe(1);
    Course::on('incomplete')->findOrFail($courseId)->categories()->sync([$categoryId, $otherCategoryId]);
    $this->assertDatabaseCount('category_course', 2, 'incomplete');
    $connection->table('users')->insert(['name' => 'Nuevo usuario', 'email' => 'new@example.test', 'password' => 'unused']);
    $this->assertDatabaseHas('users', ['email' => 'new@example.test', 'role' => 'user'], 'incomplete');
    DB::purge('incomplete');
})->with([
    'both relationship columns missing' => [true, true],
    'only course column missing' => [false, true],
    'only category column missing' => [true, false],
]);

test('leaves completed roles and multiple course categories intact when repairs are rerun', function () {
    $admin = User::factory()->admin()->create();
    $course = Course::factory()->create();
    $categories = Category::factory()->count(2)->create();
    $course->categories()->attach($categories);
    $roleRepair = require database_path('migrations/2026_10_07_054022_repair_incomplete_user_roles_schema.php');
    $categoryRepair = require database_path('migrations/2026_10_07_054025_repair_incomplete_course_categories_schema.php');

    $roleRepair->up();
    $categoryRepair->up();

    $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin']);
    $this->assertDatabaseCount('category_course', 2);
    $this->actingAs($admin)->get(route('categories.index'))->assertOk();
});

test('preserves unidentified pivot rows instead of inventing their course and category assignments', function () {
    config(['database.connections.incomplete' => ['driver' => 'sqlite', 'database' => ':memory:', 'foreign_key_constraints' => true]]);
    $oldMigrations = array_map(fn (string $path): string => 'database/migrations/'.basename($path), [
        ...glob(database_path('migrations/0001_*.php')),
        ...glob(database_path('migrations/2026_10_04_*.php')),
    ]);
    $this->artisan('migrate', ['--database' => 'incomplete', '--path' => $oldMigrations, '--force' => true])->assertSuccessful();
    Schema::connection('incomplete')->create('category_course', function (Blueprint $table): void {
        $table->id();
        $table->timestamps();
    });
    $connection = DB::connection('incomplete');
    $connection->table('category_course')->insert(['id' => 1]);
    $connection->table('migrations')->insert([
        ['migration' => '2026_10_07_051327_use_enum_roles_for_users', 'batch' => 1],
        ['migration' => '2026_10_07_051328_create_category_course_table', 'batch' => 1],
    ]);

    expect(fn () => $this->artisan('migrate', ['--database' => 'incomplete', '--force' => true])->run())
        ->toThrow(RuntimeException::class, 'La tabla category_course contiene filas sin relaciones identificables');

    $this->assertDatabaseCount('category_course', 1, 'incomplete');
    $this->assertDatabaseHas('category_course', ['id' => 1], 'incomplete');
    expect(Schema::connection('incomplete')->hasColumn('courses', 'category_id'))->toBeTrue();
    DB::purge('incomplete');
});
