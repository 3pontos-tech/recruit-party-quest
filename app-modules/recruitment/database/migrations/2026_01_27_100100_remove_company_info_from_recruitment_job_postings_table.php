<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recruitment_job_postings', function (Blueprint $table): void {
            $table->dropColumn([
                'about_company',
                'about_team',
                'work_schedule',
                'accessibility_accommodations',
                'is_disability_confident',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('recruitment_job_postings', function (Blueprint $table): void {
            $table->text('about_company')->nullable();
            $table->text('about_team')->nullable();
            $table->text('work_schedule')->nullable();
            $table->text('accessibility_accommodations')->nullable();
            $table->boolean('is_disability_confident')->default(false);
        });
    }
};
