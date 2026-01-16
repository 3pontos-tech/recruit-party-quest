<?php

declare(strict_types=1);

namespace He4rt\Ai\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class ConfidentialPromptScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->user()?->isAdmin) {
            return;
        }

        $builder->where('is_confidential', false)->orWhere(function (Builder $query): void {
            $query->where('is_confidential', true)
                ->where(function (Builder $query): void {
                    $query->where('user_id', auth()->id())
                        ->orWhereHas('confidentialAccessTeams', function (Builder $query): void {
                            $query->whereHas('users', function (Builder $query): void {
                                $query->where('users.id', auth()->id());
                            });
                        })
                        ->orWhereHas('confidentialAccessUsers', function (Builder $query): void {
                            $query->where('users.id', auth()->id());
                        });
                });
        });
    }
}
