<?php

declare(strict_types=1);

namespace He4rt\Teams;

use App\Models\BaseModel;
use He4rt\Teams\Database\Factories\DepartmentFactory;
use He4rt\Teams\Policies\DepartmentPolicy;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[UsePolicy(DepartmentPolicy::class)]
#[UseFactory(DepartmentFactory::class)]
class Department extends BaseModel
{
    use SoftDeletes;

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function headUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_user_id');
    }
}
