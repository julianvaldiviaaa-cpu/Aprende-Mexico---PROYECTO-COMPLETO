<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'is_platform_admin')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->string('role')->default('user')->change();
        });

        DB::table('users')->where('is_platform_admin', true)->update(['role' => 'admin']);
        DB::table('users')->where('role', 'user')->whereIn('id',
            DB::table('instructor_profiles')->where('status', 'approved')->whereNull('deleted_at')->select('user_id')
        )->update(['role' => 'instructor']);

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('is_platform_admin');
        });
    }

    /**
     * Keep the repaired schema; the original role migration owns its rollback.
     */
    public function down(): void {}
};
