<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('commentions.tables.comment_subscriptions', 'comment_subscriptions'), function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuidMorphs('subscribable');
            $table->uuidMorphs('subscriber');
            $table->timestamps();

            $table->unique([
                'subscribable_type', 'subscribable_id', 'subscriber_type', 'subscriber_id',
            ], 'commentions_subscriptions_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('commentions.tables.comment_subscriptions', 'comment_subscriptions'));
    }
};
