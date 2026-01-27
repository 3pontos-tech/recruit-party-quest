<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruitment_job_requisition_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('job_requisition_id')->constrained('recruitment_job_requisitions')->cascadeOnDelete();
            $table->string('type');
            $table->text('content');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['job_requisition_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recruitment_job_requisition_items');
    }
};
