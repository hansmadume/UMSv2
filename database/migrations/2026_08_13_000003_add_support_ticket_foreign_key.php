<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('support_tickets', 'internal_notes')) {
                $table->text('internal_notes')->nullable()->after('attachment_path');
            }
        });

        Schema::table('support_tickets', function (Blueprint $table) {
            $connection = Schema::getConnection()->getDriverName();
            if ($connection === 'mysql') {
                $columns = Schema::getColumnListing('support_tickets');
                if (in_array('assigned_to', $columns)) {
                    try {
                        $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
                    } catch (\Throwable $e) {
                        // Foreign key may already exist
                    }
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $connection = Schema::getConnection()->getDriverName();
            if ($connection === 'mysql') {
                try {
                    $table->dropForeign(['assigned_to']);
                } catch (\Throwable $e) {
                    // Foreign key may not exist
                }
            }
            $table->dropColumn('internal_notes');
        });
    }
};
