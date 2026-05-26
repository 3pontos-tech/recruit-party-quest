<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeds the canonical catalog of candidate skills (hard skills, soft skills, languages).
 *
 * Previously this list lived only in DevelopmentSeeder, which is gated behind
 * `! app()->isProduction()`. As a result, production had an empty
 * `candidate_skills` table and the profile's skill <select> rendered with no
 * options. This migration ships the catalog as schema, so any environment
 * (prod, staging, fresh dev) has a baseline list available.
 *
 * Idempotent: skips any name that already exists as a live (non-soft-deleted)
 * row, so it's safe to run on environments where DevelopmentSeeder has already
 * populated the table.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = Date::now();

        foreach ($this->catalog() as $category => $names) {
            foreach ($names as $name) {
                $exists = DB::table('candidate_skills')
                    ->where('name', $name)
                    ->whereNull('deleted_at')
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('candidate_skills')->insert([
                    'id' => (string) Str::uuid(),
                    'name' => $name,
                    'category' => $category,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        $names = collect($this->catalog())->flatten()->all();

        // Only remove catalog rows that no candidate is currently referencing.
        // This prevents rollback from orphaning real candidate skill assignments.
        $referencedSkillIds = DB::table('candidate_known_skills')
            ->distinct()
            ->pluck('skill_id')
            ->all();

        DB::table('candidate_skills')
            ->whereIn('name', $names)
            ->whereNotIn('id', $referencedSkillIds)
            ->delete();
    }

    /**
     * @return array<string, list<string>>
     */
    private function catalog(): array
    {
        return [
            'hard_skill' => [
                'PHP', 'Laravel', 'Symfony', 'Node.js', 'Python', 'Java', 'C#', '.NET',
                'JavaScript', 'TypeScript', 'React', 'Vue.js', 'Angular', 'HTML', 'CSS', 'Tailwind',
                'Flutter', 'React Native', 'Swift', 'Kotlin', 'Dart',
                'Docker', 'AWS', 'Azure', 'Kubernetes', 'CI/CD', 'Jenkins', 'GitLab',
                'MySQL', 'PostgreSQL', 'MongoDB', 'Redis', 'ElasticSearch',
                'Git', 'REST API', 'GraphQL',
            ],
            'soft_skill' => [
                'Leadership', 'Communication', 'Teamwork', 'Problem Solving',
                'Critical Thinking', 'Time Management', 'Adaptability',
                'Project Management', 'Agile', 'Scrum',
            ],
            'language' => [
                'English', 'Portuguese', 'Spanish', 'French', 'German',
            ],
        ];
    }
};
