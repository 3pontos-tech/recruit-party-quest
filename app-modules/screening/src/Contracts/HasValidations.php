<?php

declare(strict_types=1);

namespace He4rt\Screening\Contracts;

interface HasValidations
{
    /**
     * @return array<int , mixed>
     */
    public function rules(string $attribute, bool $required): array;

    public function initialValue(): mixed;

    /**
     * @return array<string, mixed>
     */
    public function messages(string $attribute): array;
}
