<?php

declare(strict_types=1);

/**
 * Flatten a translation array into dot-notation keys so two locales can be
 * compared structurally regardless of value.
 *
 * @param  array<string, mixed>  $items
 * @return list<string>
 */
function flattenTranslationKeys(array $items, string $prefix = ''): array
{
    $keys = [];

    foreach ($items as $key => $value) {
        $path = $prefix === '' ? (string) $key : sprintf('%s.%s', $prefix, $key);

        if (is_array($value)) {
            $keys = array_merge($keys, flattenTranslationKeys($value, $path));

            continue;
        }

        $keys[] = $path;
    }

    return $keys;
}

it('keeps en and pt_BR screening translations at full key parity', function (string $file): void {
    $base = dirname(__DIR__, 2).'/lang';

    $en = flattenTranslationKeys(require sprintf('%s/en/%s', $base, $file));
    $ptBr = flattenTranslationKeys(require sprintf('%s/pt_BR/%s', $base, $file));

    sort($en);
    sort($ptBr);

    expect(array_values(array_diff($en, $ptBr)))
        ->toBe([], sprintf('Keys present in en/%s but missing in pt_BR/%s', $file, $file))
        ->and(array_values(array_diff($ptBr, $en)))
        ->toBe([], sprintf('Keys present in pt_BR/%s but missing in en/%s', $file, $file));
})->with([
    'enums.php',
    'filament.php',
    'messages.php',
    'question_types.php',
    'question_validations.php',
]);
