<?php

declare(strict_types=1);

use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;
use He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionPriorityEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum;
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
            $table->string('work_arrangement')->comment(WorkArrangementEnum::stringifyCases());
            $table->string('employment_type')->comment(EmploymentTypeEnum::stringifyCases());
            $table->string('experience_level')->comment(ExperienceLevelEnum::stringifyCases());
            $table->string('positions_available');
            $table->integer('salary_range_min')->nullable();
            $table->integer('salary_range_max')->nullable();
            $table->boolean('show_salary_to_candidates');
            $table->string('salary_currency');
            $table->foreignUuid('hiring_manager_id')->constrained('users');
            $table->foreignUuid('created_by_id')->constrained('users');
            $table->string('status')->comment(RequisitionStatusEnum::stringifyCases());
            $table->string('priority')->comment(RequisitionPriorityEnum::stringifyCases());
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
