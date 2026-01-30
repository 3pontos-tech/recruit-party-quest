<?php

declare(strict_types=1);

namespace He4rt\Screening\Database\Factories;

use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Screening\Enums\QuestionTypeEnum;
use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Teams\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Relations\Relation;

/** @extends Factory<ScreeningQuestion> */
class ScreeningQuestionFactory extends Factory
{
    protected $model = ScreeningQuestion::class;

    public function definition(): array
    {
        $questionType = fake()->randomElement(QuestionTypeEnum::cases());
        $questionData = $this->getRealisticQuestion($questionType);

        return [
            'team_id' => Team::factory(),
            'screenable_type' => Relation::getMorphAlias(JobRequisition::class),
            'screenable_id' => JobRequisition::factory(),
            'question_text' => $questionData['text'],
            'question_type' => $questionType,
            'settings' => $questionData['settings'],
            'is_required' => $questionData['is_required'],
            'is_knockout' => $questionData['is_knockout'],
            'knockout_criteria' => $questionData['knockout_criteria'],
            'display_order' => fake()->numberBetween(1, 20),
        ];
    }

    public function yesNo(): static
    {
        $questions = $this->getQuestionsByType('yes_no');
        $question = fake()->randomElement($questions);

        return $this->state(fn (array $attributes) => [
            'question_text' => $question['text'],
            'question_type' => QuestionTypeEnum::YesNo,
            'settings' => [],
            'is_required' => $question['is_required'] ?? true,
            'is_knockout' => $question['is_knockout'] ?? false,
            'knockout_criteria' => $question['knockout_criteria'] ?? null,
        ]);
    }

    public function singleChoice(): static
    {
        $questions = $this->getQuestionsByType('single_choice');
        $question = fake()->randomElement($questions);

        return $this->state(fn (array $attributes) => [
            'question_text' => $question['text'],
            'question_type' => QuestionTypeEnum::SingleChoice,
            'settings' => $question['settings'],
            'is_required' => $question['is_required'] ?? true,
        ]);
    }

    public function multipleChoice(int $min = 0, ?int $max = null): static
    {
        $questions = $this->getQuestionsByType('multiple_choice');
        $question = fake()->randomElement($questions);

        if (isset($question['settings']) && ($min > 0 || $max !== null)) {
            $question['settings']['min_selections'] = $min;
            $question['settings']['max_selections'] = $max;
        }

        return $this->state(fn (array $attributes) => [
            'question_text' => $question['text'],
            'question_type' => QuestionTypeEnum::MultipleChoice,
            'settings' => $question['settings'],
            'is_required' => $question['is_required'] ?? true,
        ]);
    }

    public function text($max = null): static
    {
        $questions = $this->getQuestionsByType('text');
        $question = fake()->randomElement($questions);

        if (isset($question['settings']) && $max !== null) {
            $question['settings']['max_length'] = $max;
        }

        return $this->state(fn (array $attributes) => [
            'question_text' => $question['text'],
            'question_type' => QuestionTypeEnum::Text,
            'settings' => $question['settings'],
            'is_required' => $question['is_required'] ?? false,
        ]);
    }

    public function number($min = null, $max = null): static
    {
        $questions = $this->getQuestionsByType('number');
        $question = fake()->randomElement($questions);

        if (isset($question['settings'])) {
            if ($min !== null) {
                $question['settings']['min'] = $min;
            }

            if ($max !== null) {
                $question['settings']['max'] = $max;
            }
        }

        return $this->state(fn (array $attributes) => [
            'question_text' => $question['text'],
            'question_type' => QuestionTypeEnum::Number,
            'settings' => $question['settings'],
            'is_required' => $question['is_required'] ?? false,
        ]);
    }

    public function fileUpload(): static
    {
        $questions = $this->getQuestionsByType('file_upload');
        $question = fake()->randomElement($questions);

        return $this->state(fn (array $attributes) => [
            'question_text' => $question['text'],
            'question_type' => QuestionTypeEnum::FileUpload,
            'settings' => $question['settings'],
            'is_required' => $question['is_required'] ?? true,
        ]);
    }

    public function required(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_required' => true,
        ]);
    }

    public function knockout(array $criteria = ['expected' => 'yes']): static
    {
        return $this->state(fn (array $attributes) => [
            'is_knockout' => true,
            'knockout_criteria' => $criteria,
        ]);
    }

    private function getRealisticQuestion($questionType): array
    {
        $questions = $this->getQuestionsByType($questionType);
        $question = fake()->randomElement($questions);

        return [
            'text' => $question['text'],
            'settings' => $question['settings'] ?? null,
            'is_required' => $question['is_required'] ?? fake()->boolean(70),
            'is_knockout' => $question['is_knockout'] ?? fake()->boolean(20),
            'knockout_criteria' => $question['knockout_criteria'] ?? null,
        ];
    }

    private function getQuestionsByType($questionType): array
    {
        $typeValue = is_object($questionType) ? $questionType->value : $questionType;

        return match ($typeValue) {
            'yes_no' => [
                [
                    'text' => 'Are you authorized to work in the United States without sponsorship?',
                    'is_required' => true,
                    'is_knockout' => true,
                    'knockout_criteria' => ['expected' => 'yes'],
                ],
                [
                    'text' => 'Do you have experience with version control systems (Git)?',
                    'is_required' => true,
                    'is_knockout' => false,
                ],
                [
                    'text' => 'Are you comfortable working in an Agile development environment?',
                    'is_required' => false,
                    'is_knockout' => false,
                ],
                [
                    'text' => 'Do you have experience with cloud platforms (AWS, Azure, GCP)?',
                    'is_required' => false,
                    'is_knockout' => false,
                ],
                [
                    'text' => 'Are you open to occasional travel for client meetings?',
                    'is_required' => false,
                    'is_knockout' => false,
                ],
            ],
            'single_choice' => [
                [
                    'text' => 'What is your preferred programming language?',
                    'settings' => [
                        'layout' => 'radio',
                        'choices' => [
                            ['value' => 'javascript', 'label' => 'JavaScript/TypeScript'],
                            ['value' => 'python', 'label' => 'Python'],
                            ['value' => 'java', 'label' => 'Java'],
                            ['value' => 'csharp', 'label' => 'C#'],
                            ['value' => 'go', 'label' => 'Go'],
                        ],
                    ],
                ],
                [
                    'text' => 'How many years of professional software development experience do you have?',
                    'settings' => [
                        'layout' => 'radio',
                        'choices' => [
                            ['value' => '0-1', 'label' => '0-1 years'],
                            ['value' => '2-3', 'label' => '2-3 years'],
                            ['value' => '4-6', 'label' => '4-6 years'],
                            ['value' => '7-10', 'label' => '7-10 years'],
                            ['value' => '10+', 'label' => '10+ years'],
                        ],
                    ],
                    'is_required' => true,
                ],
                [
                    'text' => 'What type of development role are you most interested in?',
                    'settings' => [
                        'layout' => 'radio',
                        'choices' => [
                            ['value' => 'frontend', 'label' => 'Frontend Development'],
                            ['value' => 'backend', 'label' => 'Backend Development'],
                            ['value' => 'fullstack', 'label' => 'Full-Stack Development'],
                            ['value' => 'mobile', 'label' => 'Mobile Development'],
                            ['value' => 'devops', 'label' => 'DevOps/Infrastructure'],
                        ],
                    ],
                ],
            ],
            'multiple_choice' => [
                [
                    'text' => 'Which technologies have you worked with? (Select all that apply)',
                    'settings' => [
                        'min_selections' => 1,
                        'max_selections' => null,
                        'choices' => [
                            ['value' => 'react', 'label' => 'React'],
                            ['value' => 'vue', 'label' => 'Vue.js'],
                            ['value' => 'angular', 'label' => 'Angular'],
                            ['value' => 'nodejs', 'label' => 'Node.js'],
                            ['value' => 'python', 'label' => 'Python'],

                        ],
                    ],
                    'is_required' => true,
                ],
                [
                    'text' => 'Which development methodologies have you used?',
                    'settings' => [
                        'min_selections' => 0,
                        'max_selections' => null,
                        'choices' => [
                            ['value' => 'scrum', 'label' => 'Scrum'],
                            ['value' => 'kanban', 'label' => 'Kanban'],
                            ['value' => 'waterfall', 'label' => 'Waterfall'],
                            ['value' => 'lean', 'label' => 'Lean'],
                            ['value' => 'xp', 'label' => 'Extreme Programming (XP)'],
                        ],
                    ],
                ],
            ],
            'text' => [
                [
                    'text' => 'Please describe a challenging technical problem you solved recently.',
                    'settings' => [
                        'max_length' => 500,
                        'multiline' => true,
                        'placeholder' => 'Describe the problem, your approach, and the outcome...',
                    ],
                    'is_required' => true,
                ],
                [
                    'text' => 'What interests you most about working at our company?',
                    'settings' => [
                        'max_length' => 300,
                        'multiline' => true,
                        'placeholder' => 'Tell us what draws you to this opportunity...',
                    ],
                    'is_required' => true,
                ],
                [
                    'text' => 'What is your GitHub username or portfolio URL?',
                    'settings' => [
                        'max_length' => 100,
                        'multiline' => false,
                        'placeholder' => 'https://github.com/username or portfolio URL',
                    ],
                    'is_required' => false,
                ],
            ],
            'number' => [
                [
                    'text' => 'What is your expected salary range (minimum in USD per month)?',
                    'settings' => [
                        'min' => 3000,
                        'max' => 30000,
                        'step' => 500,
                        'prefix' => '$',
                        'suffix' => '/month',
                    ],
                    'is_required' => false,
                ],
                [
                    'text' => 'How many days notice can you provide to your current employer?',
                    'settings' => [
                        'min' => 0,
                        'max' => 90,
                        'step' => 1,
                        'suffix' => 'days',
                    ],
                    'is_required' => true,
                ],
            ],
            'file_upload' => [
                [
                    'text' => 'Please upload your resume/CV',
                    'settings' => [
                        'max_size_kb' => 5120,
                        'max_files' => 1,
                        'allowed_extensions' => ['pdf', 'doc', 'docx'],
                    ],
                    'is_required' => true,
                ],
                [
                    'text' => 'Upload a cover letter (optional)',
                    'settings' => [
                        'max_size_kb' => 2048,
                        'max_files' => 1,
                        'allowed_extensions' => ['pdf', 'doc', 'docx', 'txt'],
                    ],
                    'is_required' => false,
                ],
            ],
            default => [
                [
                    'text' => 'Are you interested in this position?',
                    'is_required' => true,
                ],
            ],
        };
    }
}
