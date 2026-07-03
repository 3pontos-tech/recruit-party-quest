<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Stages\Filament\Schema;

use BackedEnum;
use Closure;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Relaticle\Flowforge\Column;

class KanbanColumn extends Column
{
    protected string|Htmlable|BackedEnum|Closure|null $description = null;

    /**
     * @var Closure|Collection<int,Recruiter>|null
     */
    protected Collection|Closure|null $recruiters = null;

    public function description(string|Htmlable|BackedEnum|Closure|null $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @param  Collection<int, Recruiter>|Closure|null  $recruiters
     * @return $this
     */
    public function recruiters(Collection|Closure|null $recruiters): static
    {
        $this->recruiters = $recruiters;

        return $this;
    }

    public function getDescription(): string|Htmlable|Closure|BackedEnum|null
    {
        return $this->description;
    }

    /**
     * @return Collection<int,Recruiter>|null
     */
    public function getRecruiters(): ?Collection
    {
        return $this->evaluate($this->recruiters);
    }
}
