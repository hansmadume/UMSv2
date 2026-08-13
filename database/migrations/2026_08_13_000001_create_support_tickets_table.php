<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->text('comments');
            $table->string('attachment_path')->nullable();
            $table->string('status')->default('open');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
