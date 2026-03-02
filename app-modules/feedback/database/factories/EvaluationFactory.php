<?php

declare(strict_types=1);

namespace He4rt\Feedback\Database\Factories;

use He4rt\Applications\Models\Application;
use He4rt\Feedback\Enums\EvaluationRatingEnum;
use He4rt\Feedback\Models\Evaluation;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Teams\Team;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Evaluation> */
class EvaluationFactory extends Factory
{
    /** @var array<int, string> */
    private const array STRENGTHS = [
        'Strong command of object-oriented principles with clean, readable code.',
        'Communicated complex ideas clearly and asked good clarifying questions.',
        'Proactive problem-solver — identified edge cases before they were raised.',
        'Demonstrated solid understanding of system design and scalability trade-offs.',
        'Collaborative mindset; gave constructive feedback during pair-coding exercise.',
        'Showed ownership of past projects and articulated impact metrics well.',
        'Handled ambiguous requirements confidently and iterated quickly.',
        'Technical depth is above average for the level; strong fundamentals.',
        'Impressive portfolio with well-documented projects and clear business impact.',
        'Quick to adapt during the live exercise; recovered well from an initial misstep.',
    ];

    /** @var array<int, string> */
    private const array CONCERNS = [
        'Limited experience with distributed systems at production scale.',
        'Took longer than expected on the live coding exercise under pressure.',
        'Background is more backend-focused; frontend tasks may require ramp-up.',
        'Salary expectation is at the top of the band.',
        'No direct experience leading a team; would need mentoring for a senior IC role.',
        'Some answers were surface-level; follow-up questions revealed gaps.',
        'Communication style may be a challenge in a fast-paced, async team.',
        'Needs more exposure to modern CI/CD and deployment practices.',
    ];

    /** @var array<int, string> */
    private const array RECOMMENDATIONS = [
        'Move to technical interview. Strong signal on fundamentals.',
        'Advance to the next stage; candidate shows clear potential.',
        'Proceed with caution — recommend a second interviewer to validate.',
        'Hold for now; revisit if no stronger candidates proceed.',
        'Strong hire recommendation. One of the best profiles reviewed this cycle.',
        'Move to culture fit interview. Technical bar is cleared.',
        'Recommend rejection. Skills do not meet the minimum bar for this role.',
        'Solid candidate — worth moving forward with standard process.',
    ];

    /** @var array<int, string> */
    private const array NOTES = [
        'Candidate arrived on time and was well-prepared.',
        'Good energy and enthusiasm for the role and company mission.',
        'Asked thoughtful questions about team structure and growth path.',
        'Reference check recommended before offer stage.',
        'May be a better fit for a mid-level position.',
        'Candidate mentioned a competing offer; timeline may be tight.',
        'Follow up within 48h — candidate is actively interviewing elsewhere.',
        'Strong cultural alignment observed throughout the conversation.',
    ];

    protected $model = Evaluation::class;

    public function definition(): array
    {
        $rating = fake()->randomElement(EvaluationRatingEnum::cases());

        [$min, $max] = match ($rating) {
            EvaluationRatingEnum::StrongYes => [4, 5],
            EvaluationRatingEnum::Yes => [3, 5],
            EvaluationRatingEnum::Maybe => [2, 4],
            EvaluationRatingEnum::No => [1, 3],
            EvaluationRatingEnum::StrongNo => [1, 2],
        };

        $score = fn () => fake()->numberBetween($min, $max);

        return [
            'application_id' => Application::factory(),
            'team_id' => Team::factory(),
            'stage_id' => Stage::factory(),
            'evaluator_id' => User::factory(),
            'overall_rating' => $rating,
            'strengths' => fake()->randomElement(self::STRENGTHS),
            'concerns' => fake()->optional(0.7)->randomElement(self::CONCERNS),
            'recommendation' => fake()->randomElement(self::RECOMMENDATIONS),
            'notes' => fake()->optional()->randomElement(self::NOTES),
            'criteria_scores' => [
                'technical_skills' => $score(),
                'communication' => $score(),
                'problem_solving' => $score(),
                'culture_fit' => $score(),
            ],
            'submitted_at' => fake()->dateTimeBetween('-1 month'),
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'submitted_at' => now(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'submitted_at' => null,
        ]);
    }

    public function positive(): static
    {
        return $this->state(fn (array $attributes) => [
            'overall_rating' => fake()->randomElement([EvaluationRatingEnum::Yes, EvaluationRatingEnum::StrongYes]),
        ]);
    }

    public function negative(): static
    {
        return $this->state(fn (array $attributes) => [
            'overall_rating' => fake()->randomElement([EvaluationRatingEnum::No, EvaluationRatingEnum::StrongNo]),
        ]);
    }
}
