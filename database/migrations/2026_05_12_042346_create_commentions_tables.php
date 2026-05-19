<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('commentions.tables.comments', 'comments'), function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuidMorphs('author');
            $table->uuidMorphs('commentable');
            $table->text('body');
            $table->boolean('is_internal')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('commentions.tables.comments', 'comments'));
    }
};
