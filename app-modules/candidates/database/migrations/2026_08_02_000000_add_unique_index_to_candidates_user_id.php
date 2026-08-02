<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Índice parcial: `candidates` usa SoftDeletes, então um perfil apagado não pode bloquear
 * a criação de um novo para o mesmo usuário.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            'CREATE UNIQUE INDEX candidates_user_id_unique ON candidates (user_id) WHERE deleted_at IS NULL'
        );
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS candidates_user_id_unique');
    }
};
