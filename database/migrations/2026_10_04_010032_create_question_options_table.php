<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_options', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->restrictOnDelete();
            $table->text('content');
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->index(['question_id', 'position'], 'question_options_i1');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_options');
    }
};
