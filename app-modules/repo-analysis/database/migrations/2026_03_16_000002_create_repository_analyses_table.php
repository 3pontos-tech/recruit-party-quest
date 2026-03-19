<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repository_analyses', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('candidate_id')->constrained('candidates')->cascadeOnDelete();
            $table->string('repo_name');
            $table->string('repo_full_name');
            $table->string('repo_url');
            $table->string('repo_default_branch')->default('main');
            $table->string('repo_language')->nullable();
            $table->boolean('repo_is_private')->default(false);
            $table->string('status')->default('pending');
            $table->timestampTz('analyzed_at')->nullable();
            $table->jsonb('result')->nullable();
            $table->timestampsTz();
        });
    }
};
