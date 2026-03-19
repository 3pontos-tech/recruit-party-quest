<?php

declare(strict_types=1);

namespace He4rt\RepoAnalysis\Services;

use He4rt\RepoAnalysis\Models\RepositoryAnalysis;
use He4rt\RepoAnalysis\Schemas\CriticalFilesSchema;
use He4rt\RepoAnalysis\Schemas\RepoEvaluationSchema;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Facades\Prism;
use Throwable;

class RepoAnalyzerService
{
    /**
     * @param  array<int, array{path: string, type: string, sha: string}>  $filteredTree
     * @return array<int, string>
     *
     * @throws PrismException|Throwable
     */
    public function selectCriticalFiles(array $filteredTree): array
    {
        if ($filteredTree === []) {
            return [];
        }

        $paths = collect($filteredTree)->pluck('path')->all();

        $prompt = view('repo-analysis::prompts.select-critical-files', [
            'paths' => $paths,
        ])->render();

        $schema = CriticalFilesSchema::build();

        $response = Prism::structured()
            ->using(config('ai.provider.gemini.enum'), config('ai.provider.gemini.model'))
            ->withSchema($schema)
            ->withPrompt($prompt)
            ->withClientOptions(['timeout' => 180])
            ->asStructured();

        return $response->structured['files'] ?? [];
    }

    /**
     * @param  array<string, string|null>  $fileContents  key = path, value = content
     * @return array<string, mixed>
     *
     * @throws PrismException|Throwable
     */
    public function analyze(
        RepositoryAnalysis $analysis,
        array $fileContents,
        bool $isTruncated = false,
    ): array {
        $prompt = view('repo-analysis::prompts.evaluate-repository', [
            'analysis' => $analysis,
            'files' => $fileContents,
            'isTruncated' => $isTruncated,
        ])->render();

        $response = Prism::structured()
            ->using(config('ai.provider.gemini.enum'), config('ai.provider.gemini.model'))
            ->withSchema(RepoEvaluationSchema::build())
            ->withPrompt($prompt)
            ->withClientOptions(['timeout' => 180])
            ->asStructured();

        return $response->structured;
    }
}
