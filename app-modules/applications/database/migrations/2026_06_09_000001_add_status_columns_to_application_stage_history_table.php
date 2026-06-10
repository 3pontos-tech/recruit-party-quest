<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Records the status transition alongside the stage move on each history row.
     * Nullable on purpose: legacy rows written before this migration carry no
     * status and fall back to the stage-name rendering in the timeline.
     */
    public function up(): void
    {
        Schema::table('application_stage_history', function (Blueprint $table): void {
            $table->string('from_status')->nullable()->after('to_stage_id');
            $table->string('to_status')->nullable()->after('from_status');
        });
    }

    public function down(): void
    {
        Schema::table('application_stage_history', function (Blueprint $table): void {
            $table->dropColumn(['from_status', 'to_status']);
        });
    }
};
