<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignUuid('requisition_id')->constrained('recruitment_job_requisitions')->cascadeOnDelete();
            $table->foreignUuid('candidate_id')->constrained('candidates')->cascadeOnDelete();

            // Pipeline Position
            $table->foreignUuid('current_stage_id')->nullable()->constrained('recruitment_pipeline_stages')->nullOnDelete();

            // Status
            $table->string('status')->default('new');

            // Source
            $table->string('source');
            $table->text('source_details')->nullable();

            // Cover Letter
            $table->text('cover_letter')->nullable();

            // Tracking
            $table->string('tracking_code')->nullable()->unique();

            // Rejection
            $table->timestamp('rejected_at')->nullable();
            $table->foreignUuid('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('rejection_reason_category')->nullable();
            $table->text('rejection_reason_details')->nullable();

            // Offer
            $table->timestamp('offer_extended_at')->nullable();
            $table->foreignUuid('offer_extended_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('offer_amount', 15, 2)->nullable();
            $table->timestamp('offer_response_deadline')->nullable();

            $table->timestamps();

            $table->unique(['requisition_id', 'candidate_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
