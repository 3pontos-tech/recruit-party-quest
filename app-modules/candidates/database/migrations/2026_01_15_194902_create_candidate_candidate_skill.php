<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_known_skills', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('candidate_id')->constrained('candidates');
            $table->foreignUuid('skill_id')->constrained('candidate_skills');
            $table->integer('years_of_experience');
            $table->integer('proficiency_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_candidate_skill');
    }
};
