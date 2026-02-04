<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\Actions\AiJobRequisition;

use Filament\Notifications\Notification;
use He4rt\Recruitment\Requisitions\DTOs\JobRequisitionDTO;
use He4rt\Recruitment\Requisitions\Enums\JobRequisitionItemTypeEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Exceptions\GenerateJobRequisitionException;
use He4rt\Users\User;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;

class GenerateJobRequisition
{
    /**
     * @throws GenerateJobRequisitionException
     */
    public function execute(GenerateJobRequisitionDTO $dto): JobRequisitionDTO
    {
        try {
            $response = Prism::structured()
                ->using(config('ai.provider.gemini.enum'), config('ai.provider.gemini.model'))
                ->withSchema($this->structureSchema())
                ->withPrompt(
                    <<<PROMPT
                        Contexto da empresa:
                        {$dto->companyDescription}

                        Dados da vaga:
                        - Título da Vaga: {$dto->title}
                        - Descrição da Vaga: {$dto->description}
                        - Nível de experiência: {$dto->experienceLevel->value}
                        - Tipo de contratação: {$dto->employmentType->value}
                        - Regime de trabalho: {$dto->workArrangement->value}
                        - Quantidade de vagas: 1

                        Gere o conteúdo completo da vaga com base nessas informações.
                        PROMPT
                )
                ->asStructured();
            $response = $response->structured;

            return JobRequisitionDTO::make([
                'title' => $response['title'],
                'slug' => $response['title'],
                'description' => $response['description'],
                'department_id' => $dto->departmentId,
                'team_id' => $dto->teamId,
                'recruiter_id' => $dto->recruiterId,
                'experience_level' => $dto->experienceLevel,
                'employment_type' => $dto->employmentType,
                'work_arrangement' => $dto->workArrangement,
                'priority' => $dto->priority,
                'status' => RequisitionStatusEnum::Draft,
                'summary' => $response['summary'],
                'created_by' => $dto->createdBy,
                'items' => $response['items'],
            ]);
        } catch (PrismException) {
            $notifiable = User::whereId($dto->createdBy)->first();

            Notification::make()
                ->danger()
                ->title(__('recruitment::filament.requisition.job_posting.notifications.failed'))
                ->broadcast($notifiable);

            throw GenerateJobRequisitionException::somethingWentWrong();
        }
    }

    private function structureSchema(): ObjectSchema
    {
        return new ObjectSchema(
            'job_requisition',
            'Dados estruturados da vaga',
            /** @phpstan-ignore-next-line argument.type */
            [
                'title' => new StringSchema(
                    'title',
                    'Título da vaga'
                ),

                'description' => new StringSchema(
                    'description',
                    'Descrição da vaga alinhada com os dados da empresa'
                ),
                'summary' => new StringSchema(
                    'summary',
                    'Gere um resumo da vaga.'
                ),

                'items' => new ObjectSchema(
                    'items',
                    'Itens da vaga',
                    /** @phpstan-ignore-next-line argument.type */
                    [
                        'responsibilities' => new ArraySchema(
                            'responsibilities',
                            'Lista de responsabilidades da vaga',
                            new StringSchema(
                                JobRequisitionItemTypeEnum::Responsibility->value,
                                'Responsabilidade da vaga'
                            )
                        ),

                        'required_qualifications' => new ArraySchema(
                            'required_qualifications',
                            'Requisitos obrigatórios',
                            new StringSchema(
                                JobRequisitionItemTypeEnum::RequiredQualification->value,
                                'Qualificação obrigatória'
                            )
                        ),

                        'preferred_qualifications' => new ArraySchema(
                            'preferred_qualifications',
                            'Requisitos desejáveis',
                            new StringSchema(
                                JobRequisitionItemTypeEnum::PreferredQualification->value,
                                'Qualificação desejável'
                            )
                        ),

                        'benefits' => new ArraySchema(
                            'benefits',
                            'Benefícios oferecidos',
                            new StringSchema(
                                JobRequisitionItemTypeEnum::Benefit->value,
                                'Benefício'
                            )
                        ),
                    ]
                ),
            ]
        );
    }
}
