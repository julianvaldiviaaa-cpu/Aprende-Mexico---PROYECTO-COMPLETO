<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classroom_members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('role')->default('student');
            $table->timestamp('joined_at');
            $table->timestamps();
            $table->unique(['classroom_id', 'user_id'], 'classroom_members_u1');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_members');
    }
};
