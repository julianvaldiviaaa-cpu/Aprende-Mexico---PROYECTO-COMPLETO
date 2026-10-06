<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('instructor_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('institution_id')->nullable()->constrained('institutions')->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->restrictOnDelete();
            $table->string('title');
            $table->string('slug', 191)->unique();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->string('cover_path')->nullable();
            $table->string('level')->default('beginner');
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['instructor_id', 'status'], 'courses_i1');
            $table->index(['institution_id', 'status'], 'courses_i2');
            $table->index(['category_id', 'status'], 'courses_i3');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
