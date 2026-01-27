<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table): void {
            $table->text('about')->nullable();
            $table->text('work_schedule')->nullable();
            $table->text('accessibility_accommodations')->nullable();
            $table->boolean('is_disability_confident')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table): void {
            $table->dropColumn([
                'about',
                'work_schedule',
                'accessibility_accommodations',
                'is_disability_confident',
            ]);
        });
    }
};
