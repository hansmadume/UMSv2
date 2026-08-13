<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            if (! Schema::hasColumn('roles', 'guard_name')) {
                $table->string('guard_name')->default('web')->after('status');
            }
        });

        Schema::table('permissions', function (Blueprint $table) {
            if (! Schema::hasColumn('permissions', 'guard_name')) {
                $table->string('guard_name')->default('web')->after('description');
            }
        });

        if (! Schema::hasTable('model_has_roles')) {
            Schema::create('model_has_roles', function (Blueprint $table) {
                $table->unsignedInteger('role_id');
                $table->string('model_type');
                $table->unsignedInteger('model_id');

                $table->index(['model_id', 'model_type']);

                $table->foreign('role_id')
                    ->references('id')
                    ->on('roles')
                    ->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('model_has_permissions')) {
            Schema::create('model_has_permissions', function (Blueprint $table) {
                $table->unsignedInteger('permission_id');
                $table->string('model_type');
                $table->unsignedInteger('model_id');

                $table->index(['model_id', 'model_type']);

                $table->foreign('permission_id')
                    ->references('id')
                    ->on('permissions')
                    ->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('role_has_permissions')) {
            Schema::create('role_has_permissions', function (Blueprint $table) {
                $table->unsignedInteger('permission_id');
                $table->unsignedInteger('role_id');

                $table->foreign('permission_id')
                    ->references('id')
                    ->on('permissions')
                    ->cascadeOnDelete();

                $table->foreign('role_id')
                    ->references('id')
                    ->on('roles')
                    ->cascadeOnDelete();

                $table->primary(['permission_id', 'role_id']);
            });
        }

        if (Schema::hasTable('role_permissions')) {
            $rows = DB::table('role_permissions')->select('permission_id', 'role_id')->get()->map(function ($row) {
                return ['permission_id' => $row->permission_id, 'role_id' => $row->role_id];
            })->toArray();
            if (! empty($rows)) {
                DB::table('role_has_permissions')->insert($rows);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('model_has_roles');

        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn('guard_name');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('guard_name');
        });
    }
};
