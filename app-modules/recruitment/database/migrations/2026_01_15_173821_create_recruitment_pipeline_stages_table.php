<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the recruitment_pipeline_stages table with columns for UUID primary id, team and job requisition foreign keys, stage metadata, ordering, duration, active flag, timestamps, and soft deletion.
     *
     * The table includes:
     * - `id`: UUID primary key.
     * - `team_id`, `job_requisition_id`: UUID foreign keys constrained to `teams` and `recruitment_job_requisitions`.
     * - `name`, `stage_type`, `description`: string columns for stage metadata.
     * - `display_order`: nullable decimal(20,10) column for ordering.
     * - `expected_duration_days`: integer duration in days.
     * - `active`: boolean flag.
     * - automatic `created_at`/`updated_at` timestamps and `deleted_at` soft delete column.
     */
    public function up(): void
    {
        Schema::create('recruitment_pipeline_stages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('team_id')->constrained('teams');
            $table->foreignUuid('job_requisition_id')->constrained('recruitment_job_requisitions');
            $table->string('name');
            $table->string('stage_type');
            $table->decimal('display_order', 20, 10)->nullable();
            $table->string('description');
            $table->integer('expected_duration_days');
            $table->boolean('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }
};