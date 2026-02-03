<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Recruitment\Requisitions\Actions\AiJobRequisition\GenerateJobRequisition;
use He4rt\Recruitment\Requisitions\Actions\AiJobRequisition\GenerateJobRequisitionDTO;
use He4rt\Recruitment\Requisitions\DTOs\JobRequisitionDTO;
use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;
use He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionPriorityEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum;
use He4rt\Recruitment\Requisitions\Jobs\GeneratePostJob;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Requisitions\Models\JobRequisitionItem;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Teams\Department;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    $this->recruiter = Recruiter::factory()->createOne();
    actingAs($this->recruiter->user);
    $this->team = $this->recruiter->team;
    $this->department = Department::factory()
        ->recycle($this->team)
        ->createOne([
            'head_user_id' => $this->recruiter->user_id,
        ]);
    filament()->setTenant($this->team);
    filament()->setCurrentPanel(FilamentPanel::Organization->value);
});

it('should be able to create job post with ai', function (): void {
    $dto = GenerateJobRequisitionDTO::make([
        'title' => 'title',
        'description' => 'description',
        'work_arrangement' => WorkArrangementEnum::OnSite->value,
        'employment_type' => EmploymentTypeEnum::Contractor->value,
        'experience_level' => ExperienceLevelEnum::Lead->value,
        'priority' => RequisitionPriorityEnum::Medium->value,
        'recruiter_id' => $this->recruiter->getKey(),
        'created_by' => auth()->user()->getKey(),
        'company_description' => $this->team->description,
        'department_id' => $this->department->getKey(),
        'team_id' => $this->team->getKey(),
    ]);
    fakeClass();

    dispatch(new GeneratePostJob($dto));

    assertDatabaseCount(JobRequisition::class, 1);
    assertDatabaseHas(JobRequisition::class, [
        'team_id' => $dto->teamId,
        'department_id' => $dto->departmentId,
        'work_arrangement' => $dto->workArrangement,
        'employment_type' => $dto->employmentType,
        'experience_level' => $dto->experienceLevel,
        'salary_currency' => 'BRL',
        'positions_available' => 1,
        'show_salary_to_candidates' => 0,
        'recruiter_id' => $dto->recruiterId,
        'created_by_id' => $dto->createdBy,
        'status' => RequisitionStatusEnum::Draft->value,
        'priority' => $dto->priority,
        'is_confidential' => 0,
        'is_internal_only' => 0,
    ]);
    $jobRequisition = JobRequisition::query()->first();

    assertDatabaseHas(JobRequisitionItem::class, [
        'job_requisition_id' => $jobRequisition->getKey(),
        'type' => 'required_qualification',
        'content' => 'Experiência sênior com PHP',
    ]);
    assertDatabaseHas(JobRequisitionItem::class, [
        'job_requisition_id' => $jobRequisition->getKey(),
        'type' => 'preferred_qualification',
        'content' => 'Experiência com Docker',
    ]);
    assertDatabaseHas(JobRequisitionItem::class, [
        'job_requisition_id' => $jobRequisition->getKey(),
        'type' => 'benefit',
        'content' => 'Trabalho remoto ou híbrido',
    ]);

    assertDatabaseCount(JobPosting::class, 1);
    assertDatabaseHas(JobPosting::class, [
        'job_requisition_id' => $jobRequisition->getKey(),
        'title' => $dto->title,
        'team_id' => $dto->teamId,
        'summary' => 'fake summary',
        'description' => $dto->description,
    ]);

});

function fakeClass(): void
{
    app()->bind(GenerateJobRequisition::class, fn () => new class
    {
        public function execute(GenerateJobRequisitionDTO $dto): JobRequisitionDTO
        {
            return JobRequisitionDTO::make([
                'title' => $dto->title,
                'slug' => $dto->title,
                'description' => $dto->description,
                'department_id' => $dto->departmentId,
                'team_id' => $dto->teamId,
                'recruiter_id' => $dto->recruiterId,
                'experience_level' => $dto->experienceLevel,
                'employment_type' => $dto->employmentType,
                'work_arrangement' => $dto->workArrangement,
                'priority' => $dto->priority,
                'status' => RequisitionStatusEnum::Draft,
                'summary' => 'fake summary',
                'created_by' => $dto->createdBy,
                'items' => [
                    'responsibility' => [
                        'Desenvolver e manter aplicações web em PHP utilizando Laravel',
                    ],

                    'required_qualifications' => [
                        'Experiência sênior com PHP',
                    ],

                    'preferred_qualifications' => [
                        'Experiência com Docker',
                    ],

                    'benefits' => [
                        'Trabalho remoto ou híbrido',
                    ],
                ],
            ]);
        }
    });
}
