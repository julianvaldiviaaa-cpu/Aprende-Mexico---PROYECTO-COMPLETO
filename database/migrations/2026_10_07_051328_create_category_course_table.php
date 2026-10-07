<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_course', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['category_id', 'course_id']);
        });

        DB::table('courses')->whereNotNull('category_id')->orderBy('id')->chunkById(200, function ($courses): void {
            foreach ($courses as $course) {
                DB::table('category_course')->insert([
                    'category_id' => $course->category_id,
                    'course_id' => $course->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        Schema::table('courses', function (Blueprint $table): void {
            $table->dropIndex('courses_i3');
            $table->dropConstrainedForeignId('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->foreignId('category_id')->nullable()->constrained()->restrictOnDelete();
            $table->index(['category_id', 'status'], 'courses_i3');
        });
        DB::table('category_course')->select('course_id')->selectRaw('MIN(category_id) AS category_id')
            ->groupBy('course_id')->orderBy('course_id')->chunk(200, function ($assignments): void {
                foreach ($assignments as $assignment) {
                    DB::table('courses')->where('id', $assignment->course_id)->update(['category_id' => $assignment->category_id]);
                }
            });
        Schema::dropIfExists('category_course');
    }
};
