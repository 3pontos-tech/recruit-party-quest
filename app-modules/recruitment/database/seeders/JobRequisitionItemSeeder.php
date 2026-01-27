<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Database\Seeders;

use He4rt\Recruitment\Requisitions\Enums\JobRequisitionItemTypeEnum;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Requisitions\Models\JobRequisitionItem;
use Illuminate\Database\Seeder;

final class JobRequisitionItemSeeder extends Seeder
{
    private const array TAGS_BY_TYPE = [
        'description' => ['overview', 'company-culture', 'mission', 'vision'],
        'responsibility' => ['technical', 'leadership', 'collaboration', 'problem-solving'],
        'required_qualification' => ['must-have', 'essential', 'core-skill', 'experience'],
        'preferred_qualification' => ['nice-to-have', 'bonus', 'advanced', 'growth'],
        'benefit' => ['compensation', 'wellness', 'flexibility', 'development'],
    ];

    public function run(): void
    {
        $requisitions = JobRequisition::all();

        foreach ($requisitions as $requisition) {
            $this->seedItemsForRequisition($requisition);
        }
    }

    private function seedItemsForRequisition(JobRequisition $requisition): void
    {
        $itemsConfig = [
            ['type' => JobRequisitionItemTypeEnum::Description, 'count' => fake()->numberBetween(2, 4)],
            ['type' => JobRequisitionItemTypeEnum::Responsibility, 'count' => fake()->numberBetween(4, 6)],
            ['type' => JobRequisitionItemTypeEnum::RequiredQualification, 'count' => fake()->numberBetween(4, 6)],
            ['type' => JobRequisitionItemTypeEnum::PreferredQualification, 'count' => fake()->numberBetween(2, 4)],
            ['type' => JobRequisitionItemTypeEnum::Benefit, 'count' => fake()->numberBetween(4, 6)],
        ];

        foreach ($itemsConfig as $config) {
            for ($i = 0; $i < $config['count']; $i++) {
                $item = JobRequisitionItem::factory()
                    ->ofType($config['type'])
                    ->recycle($requisition)
                    ->create(['order' => $i]);

                $this->attachTagsToItem($item, $config['type']);
            }
        }
    }

    private function attachTagsToItem(JobRequisitionItem $item, JobRequisitionItemTypeEnum $type): void
    {
        $availableTags = self::TAGS_BY_TYPE[$type->value];
        $selectedTags = fake()->randomElements($availableTags, fake()->numberBetween(2, 3));

        $item->attachTags($selectedTags);
    }
}
