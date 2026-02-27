<?php

declare(strict_types=1);

namespace He4rt\Permissions;

enum Roles: string
{
    case SuperAdmin = 'super_admin';

    case Admin = 'admin';

    case Owner = 'owner';

    case User = 'user';
}
