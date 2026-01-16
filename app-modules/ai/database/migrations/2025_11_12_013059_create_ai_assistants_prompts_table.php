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
        Schema::create('ai_assistants_prompts', function (Blueprint $table): void {
            $table->foreignUuid('ai_assistant_id')->constrained('ai_assistants')->cascadeOnDelete();
            $table->foreignUuid('prompt_id')->constrained('prompts')->cascadeOnDelete();
            $table->integer('order')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_assistants_prompts');
    }
};
