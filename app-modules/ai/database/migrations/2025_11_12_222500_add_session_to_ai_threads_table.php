<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_threads', function (Blueprint $table): void {
            $table->json('session')->nullable()->after('locked_at');
        });
    }

    public function down(): void
    {
        Schema::table('ai_threads', function (Blueprint $table): void {
            $table->dropColumn('session');
        });
    }
};
