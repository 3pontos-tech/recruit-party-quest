<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruitment_job_requisitions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('team_id')->constrained('teams');
            $table->foreignUuid('department_id')->constrained('departments');
            $table->string('work_arrangement');
            $table->string('employment_type');
            $table->string('experience_level');
            $table->string('positions_available');
            $table->integer('salary_range_min')->nullable();
            $table->integer('salary_range_max')->nullable();
            $table->string('salary_currency');
            $table->foreignUuid('hiring_manager_id')->constrained('users');
            $table->foreignUuid('created_by_id')->constrained('users');
            $table->string('status');
            $table->string('priority');
            $table->timestamp('target_start_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->boolean('is_internal_only');
            $table->boolean('is_confidential');
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
