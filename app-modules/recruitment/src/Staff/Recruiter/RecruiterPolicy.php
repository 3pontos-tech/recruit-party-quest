<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Staff\Recruiter;

use He4rt\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RecruiterPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Recruiter $recruiter): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Recruiter $recruiter): bool
    {
        return true;
    }

    public function delete(User $user, Recruiter $recruiter): bool
    {
        return true;
    }

    public function restore(User $user, Recruiter $recruiter): bool
    {
        return true;
    }

    public function forceDelete(User $user, Recruiter $recruiter): bool
    {
        return true;
    }
}
