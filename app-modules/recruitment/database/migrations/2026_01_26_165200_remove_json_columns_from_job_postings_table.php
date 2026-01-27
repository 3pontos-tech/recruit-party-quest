<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recruitment_job_postings', function (Blueprint $table): void {
            $table->dropColumn([
                'description',
                'responsibilities',
                'required_qualifications',
                'preferred_qualifications',
                'benefits',
            ]);
            $table->text('description')->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('recruitment_job_postings', function (Blueprint $table): void {
            $table->text('description')->change();
            $table->jsonb('responsibilities');
            $table->jsonb('required_qualifications');
            $table->jsonb('preferred_qualifications');
            $table->jsonb('benefits');
        });
    }
};
