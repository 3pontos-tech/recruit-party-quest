<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('application_comments');
    }

    public function down(): void
    {
        // Recreating the legacy table is intentionally omitted: the backfill
        // migration that preceded this one is also irreversible. Rolling back
        // this migration alone would leave an empty table with no data.
    }
};
