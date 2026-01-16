<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruitment_job_postings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('team_id')->constrained('teams');
            $table->foreignUuid('job_requisition_id')->constrained('recruitment_job_requisitions');
            $table->string('title');
            $table->string('slug');
            $table->longText('summary');
            $table->jsonb('description');
            $table->jsonb('responsibilities');
            $table->jsonb('required_qualifications');
            $table->jsonb('preferred_qualifications');
            $table->jsonb('benefits');
            $table->text('about_company');
            $table->text('about_team');
            $table->text('work_schedule');
            $table->text('accessibility_accommodations');
            $table->boolean('is_disability_confident');
            $table->string('external_post_url');
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
