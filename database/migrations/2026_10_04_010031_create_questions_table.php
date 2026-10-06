<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->restrictOnDelete();
            $table->string('type')->default('multiple_choice');
            $table->text('prompt');
            $table->decimal('points', 10, 2)->default(1);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->index(['quiz_id', 'position'], 'questions_i1');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
