<?php

declare(strict_types=1);

namespace He4rt\Ai\Database\Seeders;

use He4rt\Ai\Models\PromptType;
use Illuminate\Database\Seeder;

final class PromptTypeSeeder extends Seeder
{
    public function run(): void
    {
        PromptType::factory()->count(20)->create();
    }
}
