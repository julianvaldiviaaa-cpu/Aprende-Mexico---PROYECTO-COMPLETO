<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $missingCategoryId = ! Schema::hasColumn('category_course', 'category_id');
        $missingCourseId = ! Schema::hasColumn('category_course', 'course_id');
        $hasLegacyCategory = Schema::hasColumn('courses', 'category_id');

        if (! $missingCategoryId && ! $missingCourseId && ! $hasLegacyCategory) {
            return;
        }

        if (($missingCategoryId || $missingCourseId) && DB::table('category_course')->exists()) {
            throw new RuntimeException('La tabla category_course contiene filas sin relaciones identificables; no se modificaron esos datos.');
        }

        Schema::table('category_course', function (Blueprint $table) use ($missingCategoryId, $missingCourseId): void {
            if ($missingCategoryId) {
                $table->foreignId('category_id')->constrained()->restrictOnDelete();
            }
            if ($missingCourseId) {
                $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            }
        });

        if (! Schema::hasIndex('category_course', 'category_course_category_id_course_id_unique')) {
            Schema::table('category_course', function (Blueprint $table): void {
                $table->unique(['category_id', 'course_id']);
            });
        }

        if (! $hasLegacyCategory) {
            return;
        }

        DB::table('courses')->whereNotNull('category_id')->orderBy('id')->chunkById(200, function ($courses): void {
            foreach ($courses as $course) {
                DB::table('category_course')->insertOrIgnore([
                    'category_id' => $course->category_id,
                    'course_id' => $course->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        if (Schema::hasIndex('courses', 'courses_i3')) {
            Schema::table('courses', function (Blueprint $table): void {
                $table->dropIndex('courses_i3');
            });
        }
        Schema::table('courses', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('category_id');
        });
    }

    /**
     * Keep the repaired schema; the original pivot migration owns its rollback.
     */
    public function down(): void {}
};
