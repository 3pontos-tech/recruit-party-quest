<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @template TFactory of Factory
 */
abstract class BasePivot extends Pivot
{
    /** @use HasFactory<TFactory> */
    use HasFactory;
    use HasUuids;

    public $timestamps = true;
}
