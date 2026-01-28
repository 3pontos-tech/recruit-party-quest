<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('links', static function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->string('name');
            $table->string('slug');
            $table->string('url');

            $table->string('icon')->nullable();
            $table->string('type')->nullable();

            $table->unsignedInteger('order_column')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('linkables', static function (Blueprint $table): void {
            $table->foreignUuid('link_id')->constrained()->cascadeOnDelete();

            $table->morphs('linkable');

            $table->unique(['link_id', 'linkable_id', 'linkable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linkables');
        Schema::dropIfExists('links');
    }
};
