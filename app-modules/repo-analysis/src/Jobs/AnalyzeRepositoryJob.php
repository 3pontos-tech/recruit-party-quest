<?php

declare(strict_types=1);

namespace He4rt\RepoAnalysis\Jobs;

use He4rt\RepoAnalysis\Enums\AnalysisStatus;
use He4rt\RepoAnalysis\Events\AnalysisStatusChanged;
use He4rt\RepoAnalysis\Exceptions\GitHubException;
use He4rt\RepoAnalysis\Models\RepositoryAnalysis;
use He4rt\RepoAnalysis\Services\GitHubService;
use He4rt\RepoAnalysis\Services\RepoAnalyzerService;
use He4rt\Users\Models\UserGitHubConnection;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Date;
use Throwable;

final class AnalyzeRepositoryJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 300;

    public function __construct(
        private readonly RepositoryAnalysis $analysis
    ) {}

    public function handle(GitHubService $github, RepoAnalyzerService $analyzer): void
    {
        try {
            $this->analysis->update(['status' => AnalysisStatus::Analyzing]);

            $connection = UserGitHubConnection::query()
                ->where('user_id', $this->analysis->candidate->user_id)
                ->first();

            if (is_null($connection)) {
                $this->analysis->update(['status' => AnalysisStatus::Failed]);

                return;
            }

            $token = $connection->access_token;

            $treeResult = $github->getRepositoryTree(
                $token,
                $this->analysis->repo_full_name,
                $this->analysis->repo_default_branch
            );

            $filteredTree = $github->filterTree($treeResult['tree']);

            $criticalFilePaths = $analyzer->selectCriticalFiles($filteredTree);

            $fileContents = $github->downloadMultipleFiles(
                $token,
                $this->analysis->repo_full_name,
                $criticalFilePaths
            );

            $nonNullCount = collect($fileContents)->filter(fn ($content) => $content !== null)->count();

            if ($nonNullCount === 0) {
                $this->failed(null);

                return;
            }

            $result = $analyzer->analyze($this->analysis, $fileContents, isTruncated: $treeResult['truncated']);

            $this->analysis->update([
                'status' => AnalysisStatus::Completed,
                'result' => $result,
                'analyzed_at' => now(),
            ]);

            event(new AnalysisStatusChanged($this->analysis));

        } catch (GitHubException $e) {
            $delay = max(0, $e->getResetTime() - Date::now()->getTimestamp()) + 10;
            $this->release($delay);
        } catch (Throwable $exception) {
            $this->failed($exception);
        }
    }

    public function failed(?Throwable $exception): void
    {
        $this->analysis->update(['status' => AnalysisStatus::Failed]);

        event(new AnalysisStatusChanged($this->analysis));

        report_if($exception instanceof Throwable, $exception);
    }
}
