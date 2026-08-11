<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable()->after('id');
                $table->string('full_name')->nullable();
                $table->string('username')->unique();
                $table->string('email')->unique()->nullable();
                $table->string('password_hash');
                $table->timestamp('email_verified_at')->nullable()->after('password_hash');
                $table->rememberToken()->after('deleted_at');
                $table->string('status')->default('active');
                $table->string('contact_number')->nullable();
                $table->text('address')->nullable();
                $table->string('profile_photo')->nullable();
                $table->timestamp('last_login')->nullable();
                $table->foreignId('role_id')->nullable()->index()->after('profile_photo');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('description')->nullable();
                $table->string('icon')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('role_permissions')) {
            Schema::create('role_permissions', function (Blueprint $table) {
                $table->foreignId('role_id')->constrained()->cascadeOnDelete();
                $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
                $table->primary(['role_id', 'permission_id']);
            });
        }

        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('user_name')->nullable();
                $table->string('action');
                $table->string('ip_address')->nullable();
                $table->timestamp('created_at')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('audit_logs')) {
            Schema::dropIfExists('audit_logs');
        }
        if (Schema::hasTable('role_permissions')) {
            Schema::dropIfExists('role_permissions');
        }
        if (Schema::hasTable('permissions')) {
            Schema::dropIfExists('permissions');
        }
        if (Schema::hasTable('roles')) {
            Schema::dropIfExists('roles');
        }
        if (Schema::hasTable('users')) {
            Schema::dropIfExists('users');
        }
    }
};
