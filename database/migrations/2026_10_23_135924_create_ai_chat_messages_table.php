<?php

declare(strict_types=1);

use App\Enums\AiChatMessageRole;
use App\Enums\AiChatMessageStatus;
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
        Schema::create('ai_chat_messages', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('conversation_id')->constrained('ai_chat_conversations')->cascadeOnDelete();
            $table->text('content');
            $table->string('role')->default(AiChatMessageRole::User->value);
            $table->string('status')->default(AiChatMessageStatus::Pending->value);
            $table->text('error_detail')->nullable();
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->unsignedInteger('output_tokens')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_chat_messages');
    }
};
