<?php

declare(strict_types=1);

use He4rt\Candidates\Models\Candidate;
use He4rt\RepoAnalysis\Enums\AnalysisStatus;
use He4rt\RepoAnalysis\Exceptions\GitHubException;
use He4rt\RepoAnalysis\Jobs\AnalyzeRepositoryJob;
use He4rt\RepoAnalysis\Models\RepositoryAnalysis;
use He4rt\RepoAnalysis\Services\GitHubService;
use He4rt\RepoAnalysis\Services\RepoAnalyzerService;
use He4rt\Users\Models\UserGitHubConnection;
use He4rt\Users\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->candidate = Candidate::factory()->create(['user_id' => $this->user->getKey()]);
    $this->connection = UserGitHubConnection::factory()->create(['user_id' => $this->user->getKey()]);

    $this->analysis = RepositoryAnalysis::factory()->create([
        'candidate_id' => $this->candidate->getKey(),
        'repo_full_name' => 'johndoe/my-project',
        'repo_default_branch' => 'main',
    ]);

    $this->fakeTree = [
        ['path' => 'README.md', 'type' => 'blob', 'sha' => '123'],
        ['path' => 'composer.json', 'type' => 'blob', 'sha' => '456'],
        ['path' => 'src/app.php', 'type' => 'blob', 'sha' => '789'],
    ];

    $this->fakeTreeResult = ['tree' => $this->fakeTree, 'truncated' => false];

    $this->fakeResult = [
        'summary' => 'A Laravel project',
        'highlights' => [
            'strengths' => ['Clean code structure', 'Good use of Laravel conventions'],
            'main_risks' => ['No test coverage', 'Missing error handling'],
        ],
        'detected_stack' => [
            'language' => 'PHP',
            'framework' => 'Laravel',
            'architecture' => 'MVC',
            'main_dependencies' => [],
        ],
        'categories' => [],
    ];
    Log::spy();
});

it('completes analysis successfully with valid data', function (): void {
    $github = $this->mock(GitHubService::class);
    $github->shouldReceive('getRepositoryTree')->andReturn($this->fakeTreeResult);
    $github->shouldReceive('filterTree')->andReturn($this->fakeTree);
    $github->shouldReceive('downloadMultipleFiles')->andReturn(['README.md' => '# My Project']);

    $analyzer = $this->mock(RepoAnalyzerService::class);
    $analyzer->shouldReceive('selectCriticalFiles')->andReturn(['README.md', 'composer.json']);
    $analyzer->shouldReceive('analyze')->andReturn($this->fakeResult);

    dispatch_sync(new AnalyzeRepositoryJob($this->analysis));

    expect($this->analysis->fresh()->status)->toBe(AnalysisStatus::Completed);
});

it('saves the analysis result and sets analyzed_at on completion', function (): void {
    $github = $this->mock(GitHubService::class);
    $github->shouldReceive('getRepositoryTree')->andReturn($this->fakeTreeResult);
    $github->shouldReceive('filterTree')->andReturn($this->fakeTree);
    $github->shouldReceive('downloadMultipleFiles')->andReturn(['README.md' => '# readme content']);

    $analyzer = $this->mock(RepoAnalyzerService::class);
    $analyzer->shouldReceive('selectCriticalFiles')->andReturn(['README.md']);
    $analyzer->shouldReceive('analyze')->andReturn($this->fakeResult);

    dispatch_sync(new AnalyzeRepositoryJob($this->analysis));

    $fresh = $this->analysis->fresh();
    expect($fresh->status)->toBe(AnalysisStatus::Completed)
        ->and($fresh->result)->toBe($this->fakeResult)
        ->and($fresh->analyzed_at)->not->toBeNull();
});

it('sets status to failed when no github connection exists', function (): void {
    $this->connection->delete();

    $github = $this->mock(GitHubService::class);
    $github->shouldNotReceive('getRepositoryTree');

    $analyzer = $this->mock(RepoAnalyzerService::class);
    $analyzer->shouldNotReceive('analyze');

    dispatch_sync(new AnalyzeRepositoryJob($this->analysis));

    expect($this->analysis->fresh()->status)->toBe(AnalysisStatus::Failed);
});

it('sets status to failed when the failed hook is called', function (): void {
    $job = new AnalyzeRepositoryJob($this->analysis);
    $job->failed(null);

    expect($this->analysis->fresh()->status)->toBe(AnalysisStatus::Failed);
});

it('keeps status as analyzing when github rate limit is exceeded', function (): void {
    $resetTime = now()->addMinutes(30)->getTimestamp();

    $github = $this->mock(GitHubService::class);
    $github->shouldReceive('getRepositoryTree')
        ->andThrow(GitHubException::RateLimitExceeded($resetTime));

    $analyzer = $this->mock(RepoAnalyzerService::class);
    $analyzer->shouldNotReceive('selectCriticalFiles');
    $analyzer->shouldNotReceive('analyze');

    $job = new AnalyzeRepositoryJob($this->analysis);
    $job->handle($github, $analyzer);

    // O job liberou a fila (release) em vez de falhar — status permanece Analyzing
    expect($this->analysis->fresh()->status)->toBe(AnalysisStatus::Analyzing);
});

it('sets status to failed when the analyzer service throws an unhandled exception', function (): void {
    $github = $this->mock(GitHubService::class);
    $github->shouldReceive('getRepositoryTree')->andReturn($this->fakeTreeResult);
    $github->shouldReceive('filterTree')->andReturn($this->fakeTree);
    $github->shouldReceive('downloadMultipleFiles')->andReturn(['README.md' => '# My Project']);

    $analyzer = $this->mock(RepoAnalyzerService::class);
    $analyzer->shouldReceive('selectCriticalFiles')->andReturn(['README.md']);
    $analyzer->shouldReceive('analyze')
        ->andThrow(new ConnectionException('cURL error 28: timeout after 30001ms'));

    dispatch_sync(new AnalyzeRepositoryJob($this->analysis));

    expect($this->analysis->fresh()->status)->toBe(AnalysisStatus::Failed);
});

it('sets status to failed when all downloaded files have null content', function (): void {
    $github = $this->mock(GitHubService::class);
    $github->shouldReceive('getRepositoryTree')->andReturn($this->fakeTreeResult);
    $github->shouldReceive('filterTree')->andReturn($this->fakeTree);
    $github->shouldReceive('downloadMultipleFiles')->andReturn([
        'README.md' => null,
        'composer.json' => null,
        'src/app.php' => null,
    ]);

    $analyzer = $this->mock(RepoAnalyzerService::class);
    $analyzer->shouldReceive('selectCriticalFiles')->andReturn(['README.md', 'composer.json', 'src/app.php']);
    $analyzer->shouldNotReceive('analyze');

    dispatch_sync(new AnalyzeRepositoryJob($this->analysis));

    expect($this->analysis->fresh()->status)->toBe(AnalysisStatus::Failed);
});

it('proceeds with analysis when at least one file has content', function (): void {
    $github = $this->mock(GitHubService::class);
    $github->shouldReceive('getRepositoryTree')->andReturn($this->fakeTreeResult);
    $github->shouldReceive('filterTree')->andReturn($this->fakeTree);
    $github->shouldReceive('downloadMultipleFiles')->andReturn([
        'README.md' => '# My Project',
        'composer.json' => null,
    ]);

    $analyzer = $this->mock(RepoAnalyzerService::class);
    $analyzer->shouldReceive('selectCriticalFiles')->andReturn(['README.md', 'composer.json']);
    $analyzer->shouldReceive('analyze')->andReturn($this->fakeResult);

    dispatch_sync(new AnalyzeRepositoryJob($this->analysis));

    expect($this->analysis->fresh()->status)->toBe(AnalysisStatus::Completed);
});
