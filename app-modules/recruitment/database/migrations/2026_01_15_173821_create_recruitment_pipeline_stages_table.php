<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruitment_pipeline_stages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('team_id')->constrained('teams');
            $table->foreignUuid('job_requisition_id')->constrained('recruitment_job_requisitions');
            $table->string('name');
            $table->string('stage_type');
            $table->integer('display_order');
            $table->string('description');
            $table->integer('expected_duration_days');
            $table->boolean('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
