<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $mapping = [
        'entry_level' => 'junior',
        'lead' => 'coordinator',
        'principal' => 'head',
    ];

    public function up(): void
    {
        foreach ($this->mapping as $old => $new) {
            DB::table('candidates')
                ->where('experience_level', $old)
                ->update(['experience_level' => $new]);

            DB::table('recruitment_job_requisitions')
                ->where('experience_level', $old)
                ->update(['experience_level' => $new]);
        }
    }

    public function down(): void
    {
        foreach ($this->mapping as $old => $new) {
            DB::table('candidates')
                ->where('experience_level', $new)
                ->update(['experience_level' => $old]);

            DB::table('recruitment_job_requisitions')
                ->where('experience_level', $old)
                ->update(['experience_level' => $new]);
        }
    }
};
