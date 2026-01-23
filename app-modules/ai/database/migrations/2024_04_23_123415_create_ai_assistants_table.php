<?php

declare(strict_types=1);

use He4rt\Ai\Enums\AiAssistantApplication;
use He4rt\Ai\Enums\AiModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_assistants', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('owner_id')->nullable()->constrained('users');
            $table->string('application')->nullable()->comment(AiAssistantApplication::stringifyCases());
            $table->boolean('is_default')->default(false);
            $table->string('model')->nullable()->comment(AiModel::stringifyCases());

            $table->string('name');
            $table->string('type')->nullable();
            $table->text('description')->nullable();
            $table->longText('instructions')->nullable();
            $table->longText('knowledge')->nullable();

            $table->dateTime('archived_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {

        Schema::dropIfExists('ai_assistants');
    }
};
