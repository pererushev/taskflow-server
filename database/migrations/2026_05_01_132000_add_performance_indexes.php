<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('team_user', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('team_id');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->index(['project_id', 'status']);
            $table->index(['project_id', 'order']);
        });

        Schema::table('task_attachments', function (Blueprint $table) {
            $table->index('task_id');
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("CREATE INDEX IF NOT EXISTS tasks_not_done_idx ON tasks (project_id, status, \"order\") WHERE status <> 'done'");
        }
    }

    public function down(): void
    {
        Schema::table('team_user', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['team_id']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'status']);
            $table->dropIndex(['project_id', 'order']);
        });

        Schema::table('task_attachments', function (Blueprint $table) {
            $table->dropIndex(['task_id']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS tasks_not_done_idx');
        }
    }
};
