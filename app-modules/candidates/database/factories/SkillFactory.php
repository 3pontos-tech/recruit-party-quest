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
        $category = fake()->randomElement(CandidateSkillCategory::cases());

        $categorySkills = [
            CandidateSkillCategory::Language->value => ['PHP', 'JavaScript', 'Python', 'Java'],
            CandidateSkillCategory::SoftSkill->value => ['Teamwork', 'Communication', 'Leadership'],
            CandidateSkillCategory::HardSkill->value => ['Database', 'Web Development', 'Software Engineering'],
        ];

        return [
            'name' => fake()->randomElement($categorySkills[$category->value]),
            'category' => $category,
            'created_at' => Date::now(),
            'updated_at' => Date::now(),
        ];
    }
}
