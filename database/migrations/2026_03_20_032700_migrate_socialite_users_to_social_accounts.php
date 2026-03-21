<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('social_accounts')) {
            Schema::create('social_accounts', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
                $table->string('provider_id');
                $table->string('provider');
                $table->text('access_token')->nullable();
                $table->string('provider_username')->nullable();
                $table->timestampsTz();

                $table->unique(['provider', 'provider_id']);
                $table->unique(['user_id', 'provider']);
            });
        }

        DB::statement('
            INSERT INTO social_accounts (id, user_id, provider, provider_id, created_at, updated_at)
            SELECT gen_random_uuid(), user_id, provider, provider_id, created_at, updated_at
            FROM socialite_users
        ');

        Schema::dropIfExists('socialite_users');
    }
};
