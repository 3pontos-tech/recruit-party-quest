<?php

declare(strict_types=1);

namespace He4rt\Ai\Database\Seeders;

use He4rt\Ai\Models\Prompt;
use Illuminate\Database\Seeder;

final class PromptSeeder extends Seeder
{
    public function run(): void
    {
        Prompt::factory()->count(20)->create();
    }
}
