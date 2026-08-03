<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidate_work_experiences', function (Blueprint $table): void {
            $table->string('position')->nullable()->after('company_name');
        });
    }
};
