<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_saved_jobs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('candidate_id')->constrained('candidates');
            $table->foreignUuid('job_requisition_id')->constrained('recruitment_job_requisitions');
            $table->timestamp('saved_at')->useCurrent();
            $table->timestamps();

            $table->unique(['candidate_id', 'job_requisition_id']);
        });
    }
};
