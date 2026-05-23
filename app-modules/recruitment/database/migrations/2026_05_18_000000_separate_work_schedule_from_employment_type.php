<?php

declare(strict_types=1);

use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkScheduleEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            if (! Schema::hasColumn('recruitment_job_requisitions', 'work_schedule')) {
                Schema::table('recruitment_job_requisitions', function (Blueprint $table): void {
                    $table->string('employment_type')->nullable()->comment(EmploymentTypeEnum::stringifyCases())->change();
                    $table->string('work_schedule')->nullable()->after('employment_type')->comment(WorkScheduleEnum::stringifyCases());
                });
            }

            DB::table('recruitment_job_requisitions')->where('employment_type', 'full_time_employee')
                ->update(['employment_type' => null, 'work_schedule' => 'full_time']);

            DB::table('recruitment_job_requisitions')->where('employment_type', 'part_time')
                ->update(['employment_type' => null, 'work_schedule' => 'part_time']);

            // clt / contractor / temporary / intern: regimes preservados, work_schedule permanece NULL.
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            DB::table('recruitment_job_requisitions')->where('work_schedule', 'full_time')->whereNull('employment_type')
                ->update(['employment_type' => 'full_time_employee']);

            DB::table('recruitment_job_requisitions')->where('work_schedule', 'part_time')->whereNull('employment_type')
                ->update(['employment_type' => 'part_time']);

            if (Schema::hasColumn('recruitment_job_requisitions', 'work_schedule')) {
                Schema::table('recruitment_job_requisitions', function (Blueprint $table): void {
                    $table->dropColumn('work_schedule');
                });
            }

            DB::table('recruitment_job_requisitions')->whereNull('employment_type')
                ->update(['employment_type' => 'full_time_employee']);

            Schema::table('recruitment_job_requisitions', function (Blueprint $table): void {
                $table->string('employment_type')->nullable(false)->comment(EmploymentTypeEnum::stringifyCases())->change();
            });
        });
    }
};
