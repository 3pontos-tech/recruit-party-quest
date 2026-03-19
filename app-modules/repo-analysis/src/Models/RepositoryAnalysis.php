<?php

declare(strict_types=1);

namespace He4rt\RepoAnalysis\Models;

use App\Models\BaseModel;
use He4rt\Candidates\Models\Candidate;
use He4rt\RepoAnalysis\Database\Factories\RepositoryAnalysisFactory;
use He4rt\RepoAnalysis\Enums\AnalysisStatus;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $candidate_id
 * @property string $repo_name
 * @property string $repo_full_name
 * @property string $repo_url
 * @property string $repo_default_branch
 * @property string|null $repo_language
 * @property bool $repo_is_private
 * @property AnalysisStatus $status
 * @property Carbon|null $analyzed_at
 * @property array<string, mixed>|null $result
 * @property-read Candidate $candidate
 *
 * @extends BaseModel<RepositoryAnalysisFactory>
 */
#[UseFactory(RepositoryAnalysisFactory::class)]
class RepositoryAnalysis extends BaseModel
{
    /**
     * @return BelongsTo<Candidate, $this>
     */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function isOnCooldown(): bool
    {

        if (is_null($this->analyzed_at)) {
            return false;
        }

        return $this->analyzed_at->diffInDays(now()) < 7;
    }

    /**
     * @param  Builder<self>  $query
     */
    protected function scopeForCandidate(Builder $query, Candidate $candidate): void
    {
        $query->where('candidate_id', $candidate->getKey());
    }

    /**
     * @param  Builder<self>  $query
     */
    protected function scopeCompletedForRepo(Builder $query, string $repoFullName): void
    {
        $query->where('repo_full_name', $repoFullName)
            ->where('status', AnalysisStatus::Completed);
    }

    protected function casts(): array
    {
        return [
            'status' => AnalysisStatus::class,
            'result' => 'array',
            'repo_is_private' => 'boolean',
            'analyzed_at' => 'datetime',
        ];
    }
}
