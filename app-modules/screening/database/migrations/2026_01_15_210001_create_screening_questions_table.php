<?php

declare(strict_types=1);

use He4rt\Screening\Enums\QuestionTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screening_questions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('team_id')->constrained('teams')->cascadeOnDelete();
            $table->morphs('screenable');

            $table->text('question_text');
            $table->string('question_type')->comment(QuestionTypeEnum::stringifyCases());

            $table->json('settings')->nullable();

            // Validation
            $table->boolean('is_required')->default(false);
            $table->boolean('is_knockout')->default(false);
            $table->json('knockout_criteria')->nullable();

            $table->integer('display_order');

            $table->timestamps();

            //            $table->unique(['requisition_id', 'display_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screening_questions');
    }
};
