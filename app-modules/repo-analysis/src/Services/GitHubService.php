<?php

declare(strict_types=1);

namespace He4rt\RepoAnalysis\Services;

use He4rt\RepoAnalysis\Exceptions\GitHubException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use Throwable;

class GitHubService
{
    private const string BASE_URL = 'https://api.github.com';

    /**
     * @return array<int, array{name: string, full_name: string, html_url: string, default_branch: string, language: string|null, private: bool}>
     *
     * @throws ConnectionException|GitHubException
     */
    public function listRepositories(string $token): array
    {
        $response = Http::withToken($token)
            ->withHeaders(['Accept' => 'application/vnd.github+json'])
            ->get(self::BASE_URL.'/user/repos', [
                'sort' => 'updated',
                'per_page' => 30,
                'type' => 'owner',
            ]);

        $this->checkRateLimit($response);

        if ($response->failed()) {
            return [];
        }

        return collect((array) ($response->json() ?? []))
            ->map(fn (array $repo): array => [
                'name' => $repo['name'],
                'full_name' => $repo['full_name'],
                'html_url' => $repo['html_url'],
                'default_branch' => $repo['default_branch'] ?? 'main',
                'language' => $repo['language'] ?? null,
                'private' => $repo['private'],
            ])
            ->values()
            ->all();
    }

    /**
     * @return array{tree: array<int, array{path: string, type: string, sha: string}>, truncated: bool}
     *
     * @throws ConnectionException|GitHubException
     */
    public function getRepositoryTree(string $token, string $repoFullName, string $branch): array
    {
        $response = Http::withToken($token)
            ->withHeaders(['Accept' => 'application/vnd.github+json'])
            ->get(self::BASE_URL.sprintf('/repos/%s/git/trees/%s', $repoFullName, $branch), [
                'recursive' => '1',
            ]);

        $this->checkRateLimit($response);

        if ($response->failed()) {
            return ['tree' => [], 'truncated' => false];
        }

        $data = (array) ($response->json() ?? []);
        $truncated = (bool) ($data['truncated'] ?? false);

        $rawTree = $data['tree'] ?? [];
        $tree = collect(is_array($rawTree) ? $rawTree : [])
            ->filter(fn (array $item): bool => $item['type'] === 'blob')
            ->map(fn (array $item): array => [
                'path' => $item['path'],
                'type' => $item['type'],
                'sha' => $item['sha'],
            ])
            ->values()
            ->all();

        return ['tree' => $tree, 'truncated' => $truncated];
    }

    /**
     * @param  array<int, array{path: string, type: string, sha: string}>  $tree
     * @return array<int, array{path: string, type: string, sha: string}>
     */
    public function filterTree(array $tree): array
    {

        return collect($tree)->filter(function (array $item): bool {
            $ignoredExtensions = [
                '.jpg', '.jpeg', '.png', '.gif', '.svg', '.ico', '.webp', // Images
                '.pdf', '.doc', '.docx', '.xls', '.xlsx', // Documents
                '.mp3', '.mp4', '.wav', '.avi', // Media
                '.zip', '.tar', '.gz', '.rar', // Archives
                '.lock', '.log', '.sqlite', '.db', // System/Logs/DB
                '.min.js', '.min.css', // Minified
                '.map', // Source maps
            ];
            $ignoredDirectories = [
                'vendor/', 'node_modules/', '.git/', 'dist/', 'build/', 'public/build/',
                'storage/framework/', 'storage/logs/', 'docs/', 'tests/Fixtures/',
            ];
            $path = $item['path'];

            if (array_any($ignoredDirectories, fn ($dir) => str_starts_with($path, $dir) || str_contains($path, '/'.$dir))) {
                return false;
            }

            return array_all($ignoredExtensions, fn ($ext) => ! str_ends_with(mb_strtolower($path), $ext));

        })->values()->all();
    }

    /**
     * @param  array<int, string>  $paths
     * @return array<string, string|null>
     *
     * @throws GitHubException
     */
    public function downloadMultipleFiles(string $token, string $repoFullName, array $paths): array
    {
        if ($paths === []) {
            return [];
        }

        $responses = Http::pool(function (Pool $pool) use ($token, $repoFullName, $paths): array {
            $requests = [];
            foreach ($paths as $path) {
                $url = self::BASE_URL.sprintf('/repos/%s/contents/%s', $repoFullName, $path);
                $requests[] = $pool->as($path)->withToken($token)
                    ->withHeaders(['Accept' => 'application/vnd.github+json'])
                    ->get($url);
            }

            return $requests;
        });

        $fileContents = [];

        foreach ($responses as $path => $response) {
            if ($response instanceof Throwable) {
                $fileContents[$path] = null;

                continue;
            }

            $this->checkRateLimit($response);

            if ($response->failed()) {
                $fileContents[$path] = null;

                continue;
            }

            $data = $response->json();
            $size = $data['size'] ?? 0;

            if ($size > 100_000) { // Limit size to ~100KB per file
                $fileContents[$path] = null;

                continue;
            }

            if (! isset($data['content']) || $data['encoding'] !== 'base64') {
                $fileContents[$path] = null;

                continue;
            }

            $fileContents[$path] = base64_decode(str_replace("\n", '', $data['content']));
        }

        return $fileContents;
    }

    private function checkRateLimit(Response $response): void
    {
        if ($response->status() === 403 || $response->status() === 429) {
            $resetHeader = $response->header('X-RateLimit-Reset');
            $resetTime = $resetHeader ? (int) $resetHeader : (Date::now()->getTimestamp() + 3600);
            throw GitHubException::RateLimitExceeded($resetTime);
        }
    }
}
