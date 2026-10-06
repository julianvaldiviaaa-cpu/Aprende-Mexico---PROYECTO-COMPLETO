<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->restrictOnDelete();
            $table->foreignId('quiz_attempt_id')->constrained('quiz_attempts')->restrictOnDelete();
            $table->json('response')->nullable();
            $table->decimal('points_awarded', 10, 2)->nullable();
            $table->timestamps();
            $table->unique(['quiz_attempt_id', 'question_id'], 'quiz_answers_u1');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_answers');
    }
};
