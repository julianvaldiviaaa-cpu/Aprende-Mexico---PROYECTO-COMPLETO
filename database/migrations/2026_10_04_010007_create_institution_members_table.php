<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institution_members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('role')->default('member');
            $table->timestamp('joined_at');
            $table->timestamps();
            $table->unique(['institution_id', 'user_id'], 'institution_members_u1');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institution_members');
    }
};
