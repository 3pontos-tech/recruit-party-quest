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
            $table->boolean('willing_to_relocate');
            $table->string('experience_level');
            $table->jsonb('contact_links')->nullable();
            $table->string('self_identified_gender');
            $table->boolean('has_disability');
            $table->string('source');
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
