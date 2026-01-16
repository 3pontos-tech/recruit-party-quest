<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screening_responses', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignUuid('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignUuid('question_id')->constrained('screening_questions')->cascadeOnDelete();

            $table->json('response_value');
            $table->boolean('is_knockout_fail')->default(false);

            $table->timestamp('created_at')->nullable();

            $table->unique(['application_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screening_responses');
    }
};
