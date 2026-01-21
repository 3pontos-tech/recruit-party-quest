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
        Schema::table('recruitment_job_requisitions', function (Blueprint $table): void {
            $table->string('slug')->nullable()->after('id');
        });

        DB::table('recruitment_job_requisitions')->get()->each(function ($requisition): void {
            DB::table('recruitment_job_requisitions')
                ->where('id', $requisition->id)
                ->update(['slug' => (string) Str::uuid()]);
        });

        Schema::table('recruitment_job_requisitions', function (Blueprint $table): void {
            $table->string('slug')->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('recruitment_job_requisitions', function (Blueprint $table): void {
            $table->dropColumn('slug');
        });
    }
};
