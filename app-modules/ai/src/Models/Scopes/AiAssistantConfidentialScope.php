<?php

declare(strict_types=1);

namespace He4rt\Ai\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

final class AiAssistantConfidentialScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (! Auth::check() || Auth::user()?->isAdmin) {
            return;
        }

        $builder->where(function (Builder $query): void {
            $query->where('is_confidential', false)
                ->orWhere(function (Builder $query): void {
                    $query->where('is_confidential', true)
                        ->where(function (Builder $query): void {
                            $query->whereBelongsTo(Auth::user(), 'createdBy')
                                ->orWhereHas('confidentialAccessTeams', function (Builder $query): void {
                                    $query->whereHas('users', function (Builder $query): void {
                                        $query->where('users.id', Auth::id());
                                    });
                                })
                                ->orWhereHas('confidentialAccessUsers', function (Builder $query): void {
                                    $query->where('users.id', Auth::id());
                                });
                        });
                });
        });
    }
}
