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
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE recruitment_job_requisitions ALTER COLUMN positions_available TYPE integer USING positions_available::integer');
            DB::statement('ALTER TABLE recruitment_job_requisitions ALTER COLUMN positions_available SET DEFAULT 1');
            DB::statement('ALTER TABLE recruitment_job_requisitions ALTER COLUMN positions_available SET NOT NULL');
        } else {
            Schema::table('recruitment_job_requisitions', function (Blueprint $table): void {
                $table->unsignedInteger('positions_available')->default(1)->change();
            });
        }
    }
};
