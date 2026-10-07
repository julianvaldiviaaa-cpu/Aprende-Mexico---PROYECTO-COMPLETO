<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('preserves administrator permissions and course categories during the upgrade', function () {
    config(['database.connections.legacy' => ['driver' => 'sqlite', 'database' => ':memory:', 'foreign_key_constraints' => true]]);
    $oldMigrations = array_map(fn (string $path): string => 'database/migrations/'.basename($path), [
        ...glob(database_path('migrations/0001_*.php')),
        ...glob(database_path('migrations/2026_10_04_*.php')),
    ]);
    $this->artisan('migrate', ['--database' => 'legacy', '--path' => $oldMigrations, '--force' => true])->assertSuccessful();

    $connection = DB::connection('legacy');
    $adminId = $connection->table('users')->insertGetId(['name' => 'Admin existente', 'email' => 'admin@example.test', 'password' => 'unused', 'is_platform_admin' => true]);
    $teacherId = $connection->table('users')->insertGetId(['name' => 'Docente existente', 'email' => 'teacher@example.test', 'password' => 'unused']);
    $connection->table('instructor_profiles')->insert(['user_id' => $teacherId, 'display_name' => 'Docente', 'slug' => 'docente', 'status' => 'approved']);
    $categoryId = $connection->table('categories')->insertGetId(['name' => 'Historia', 'slug' => 'historia']);
    $courseId = $connection->table('courses')->insertGetId(['instructor_id' => $teacherId, 'category_id' => $categoryId, 'title' => 'Historia local', 'slug' => 'historia-local']);

    $this->artisan('migrate', ['--database' => 'legacy', '--force' => true])->assertSuccessful();

    $this->assertDatabaseHas('users', ['id' => $adminId, 'role' => 'admin'], 'legacy');
    $this->assertDatabaseHas('users', ['id' => $teacherId, 'role' => 'instructor'], 'legacy');
    $this->assertDatabaseHas('category_course', ['course_id' => $courseId, 'category_id' => $categoryId], 'legacy');
    expect(Schema::connection('legacy')->hasColumn('users', 'is_platform_admin'))->toBeFalse();
    expect(Schema::connection('legacy')->hasColumn('courses', 'category_id'))->toBeFalse();

    $this->artisan('migrate:rollback', ['--database' => 'legacy', '--force' => true])->assertSuccessful();
    $this->assertDatabaseHas('courses', ['id' => $courseId, 'category_id' => $categoryId], 'legacy');
    $this->assertDatabaseHas('users', ['id' => $adminId, 'is_platform_admin' => true], 'legacy');
    DB::purge('legacy');
});
