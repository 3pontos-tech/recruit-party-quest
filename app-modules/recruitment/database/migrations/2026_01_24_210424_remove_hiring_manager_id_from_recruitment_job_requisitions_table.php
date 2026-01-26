<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recruitment_job_requisitions', function (Blueprint $table): void {
            $table->dropForeign(['hiring_manager_id']);
            $table->dropColumn('hiring_manager_id');
        });
    }
};
