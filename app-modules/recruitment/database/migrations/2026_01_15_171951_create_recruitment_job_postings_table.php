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
            $table->text('description');
            $table->string('external_post_url');
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
