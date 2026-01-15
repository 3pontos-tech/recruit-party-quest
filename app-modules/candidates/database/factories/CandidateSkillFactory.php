<?php

declare(strict_types=1);

namespace He4rt\Candidates\Database\Factories;

use He4rt\Candidates\Models\Candidate;
use He4rt\Candidates\Models\CandidateSkill;
use He4rt\Candidates\Models\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CandidateSkill> */
class CandidateSkillFactory extends Factory
{
    protected $model = CandidateSkill::class;

    public function definition(): array
    {
        return [
            'skill_id' => Skill::factory(),
            'candidate_id' => Candidate::factory(),
            'proficiency_level' => fake()->numberBetween(1, 5),
            'years_of_experience' => fake()->numberBetween(1, 5),
        ];
    }
}
