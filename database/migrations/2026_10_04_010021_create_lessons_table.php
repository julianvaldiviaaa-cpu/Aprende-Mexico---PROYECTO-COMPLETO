<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_module_id')->constrained('course_modules')->restrictOnDelete();
            $table->string('title');
            $table->string('slug', 191)->unique();
            $table->longText('content')->nullable();
            $table->string('video_url', 2048)->nullable();
            $table->string('attachment_path')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_required')->default(true);
            $table->timestamps();
            $table->index(['course_module_id', 'position'], 'lessons_i1');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
