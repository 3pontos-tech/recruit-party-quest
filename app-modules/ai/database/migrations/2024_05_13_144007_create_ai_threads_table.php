<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_threads', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('assistant_id')->constrained('ai_assistants');
            $table->foreignUuid('folder_id')->nullable()->constrained('ai_thread_folders')->nullOnDelete();
            $table->foreignUuid('user_id')->constrained();
            $table->string('name')->nullable();

            $table->integer('cloned_count')->default(0);
            $table->integer('emailed_count')->default(0);
            $table->dateTime('saved_at')->nullable();
            $table->dateTime('locked_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_threads');
    }
};
