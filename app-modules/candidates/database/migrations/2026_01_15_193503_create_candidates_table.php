<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users');
            $table->string('phone_number')->nullable();
            $table->string('headline')->nullable();
            $table->text('summary')->nullable();
            $table->date('availability_date')->nullable();
            $table->boolean('willing_to_relocate')->default(false);
            $table->boolean('is_open_to_remote')->default(true);
            $table->decimal('expected_salary', 12, 2)->nullable();
            $table->string('expected_salary_currency', 3)->default('USD');
            $table->string('linkedin_url')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->string('experience_level')->nullable();
            $table->boolean('is_onboarded')->default(false);
            $table->timestamp('onboarding_completed_at')->nullable();
            $table->string('timezone', 50)->nullable();
            $table->string('preferred_language', 10)->default('en');
            $table->boolean('data_consent_given')->default(false);
            $table->jsonb('contact_links')->nullable();
            $table->string('self_identified_gender')->nullable();
            $table->boolean('has_disability')->default(false);
            $table->string('source')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
