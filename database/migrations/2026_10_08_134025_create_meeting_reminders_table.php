<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('meeting_reminders', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('meeting_id')->constrained()->onDelete('cascade');
            $table->foreignUlid('recipient_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->string('type');
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['meeting_id', 'type', 'recipient_id']); // 重複配信防止
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_reminders');
    }
};
