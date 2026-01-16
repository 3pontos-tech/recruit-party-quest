<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prompt_types', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->string('title');
            $table->longText('description')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }
};
