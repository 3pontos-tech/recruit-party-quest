<?php

declare(strict_types=1);

namespace He4rt\Candidates\Database\Factories;

use He4rt\Candidates\Enums\CandidateSkillCategory;
use He4rt\Candidates\Models\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

/** @extends Factory<Skill> */
class SkillFactory extends Factory
{
    protected $model = Skill::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'category' => fake()->randomElement(CandidateSkillCategory::cases()),
            'created_at' => Date::now(),
            'updated_at' => Date::now(),
        ];
    }
}
