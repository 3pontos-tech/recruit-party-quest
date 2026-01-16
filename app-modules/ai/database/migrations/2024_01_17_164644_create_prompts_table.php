<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prompts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('type_id')->constrained('prompt_types')->cascadeOnDelete();
            $table->string('message_type')->comment('system, user, assistant or tool');
            $table->boolean('is_smart')->default(false);

            $table->string('title');
            $table->longText('description')->nullable();
            $table->longText('prompt');

            $table->timestamps();
            $table->softDeletes();
        });
    }
};
