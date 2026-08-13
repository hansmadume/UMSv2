<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ticket_number')->nullable()->unique();
            $table->string('subject')->nullable();
            $table->text('comments');
            $table->string('attachment_path')->nullable();
            $table->string('status')->default('open');
            $table->string('priority')->default('medium');
            $table->unsignedInteger('assigned_to')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
