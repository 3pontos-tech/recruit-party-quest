<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $createTable = static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('provider');
            $table->string('provider_id');
            $table->text('access_token')->nullable();
            $table->string('provider_username')->nullable();
            $table->timestampsTz();

            $table->unique(['provider', 'provider_id']);
            $table->unique(['user_id', 'provider']);
        };

        if (! Schema::hasTable('socialite_users')) {
            // Fresh install: social_accounts ainda não existe, criamos direto.
            Schema::create('social_accounts', $createTable);

            return;
        }

        // Produção: socialite_users existe com dados. Cria social_accounts,
        // migra os registros gerando novos UUIDs, depois dropa a tabela antiga.
        Schema::create('social_accounts', $createTable);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('
                INSERT INTO social_accounts (id, user_id, provider, provider_id, created_at, updated_at)
                SELECT gen_random_uuid(), user_id, provider, provider_id, created_at, updated_at
                FROM socialite_users
            ');
        } else {
            $rows = DB::table('socialite_users')->get();

            foreach ($rows as $row) {
                DB::table('social_accounts')->insert([
                    'id' => (string) Str::uuid(),
                    'user_id' => $row->user_id,
                    'provider' => $row->provider,
                    'provider_id' => $row->provider_id,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }

        Schema::drop('socialite_users');
    }
};
