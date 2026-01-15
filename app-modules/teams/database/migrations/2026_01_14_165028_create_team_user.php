<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_user', function (Blueprint $table): void {
            $table->foreignUuid('team_id')->constrained('teams');
            $table->foreignUuid('user_id')->constrained('users');

            $table->primary(['team_id', 'user_id']);
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_user');
    }
};
