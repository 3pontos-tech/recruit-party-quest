<?php

declare(strict_types=1);

namespace He4rt\Applications\Database\Factories;

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\CandidateSourceEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Application> */
class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    public function definition(): array
    {
        return [
            'requisition_id' => JobRequisition::factory(),
            'candidate_id' => Candidate::factory(),
            'current_stage_id' => null,
            'status' => fake()->randomElement(ApplicationStatusEnum::cases()),
            'source' => fake()->randomElement(CandidateSourceEnum::cases()),
            'source_details' => fake()->sentence(),
            'cover_letter' => fake()->paragraphs(3, true),
            'tracking_code' => fake()->unique()->bothify('APP-####-????'),
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_reason_category' => null,
            'rejection_reason_details' => null,
            'offer_extended_at' => null,
            'offer_extended_by' => null,
            'offer_amount' => null,
            'offer_response_deadline' => null,
        ];
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ApplicationStatusEnum::Rejected,
            'rejected_at' => now(),
            'rejected_by' => User::factory(),
            'rejection_reason_category' => fake()->randomElement(RejectionReasonCategoryEnum::cases()),
            'rejection_reason_details' => fake()->sentence(),
        ]);
    }

    public function withOffer(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ApplicationStatusEnum::OfferExtended,
            'offer_extended_at' => now(),
            'offer_extended_by' => User::factory(),
            'offer_amount' => fake()->numberBetween(30000, 150000),
            'offer_response_deadline' => now()->addDays(7),
        ]);
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Application $application): void {
            if ($application->requisition_id && ! $application->current_stage_id) {
                $stage = Stage::factory()->create(['job_requisition_id' => $application->requisition_id]);
                $application->update(['current_stage_id' => $stage->id]);
            }
        });
    }
}
