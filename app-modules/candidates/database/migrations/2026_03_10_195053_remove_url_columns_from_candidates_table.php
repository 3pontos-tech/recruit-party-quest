<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table): void {
            $table->dropColumn(['linkedin_url', 'portfolio_url', 'contact_links']);
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table): void {
            $table->string('linkedin_url')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->jsonb('contact_links')->nullable();
        });
    }
};
