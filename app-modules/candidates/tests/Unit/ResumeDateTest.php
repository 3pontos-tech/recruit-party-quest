<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use He4rt\Candidates\DTOs\ResumeDate;

it('parses the agreed format', function (): void {
    expect(ResumeDate::parse('2020-03-05')?->format('Y-m-d'))->toBe('2020-03-05');
});

it('parses the date the form submits, with or without time', function (string $value): void {
    expect(ResumeDate::parse($value)?->format('Y-m-d H:i:s'))->toBe('2020-03-05 00:00:00');
})->with([
    'apenas a data' => '2020-03-05',
    'com hora' => '2020-03-05 14:30:00',
    'iso completo' => '2020-03-05T14:30:00+00:00',
]);

it('keeps a date instance as it is', function (): void {
    expect(ResumeDate::parse(CarbonImmutable::parse('2020-03-05'))?->format('Y-m-d'))
        ->toBe('2020-03-05');
});

it('discards a placeholder instead of throwing', function (string $value): void {
    expect(ResumeDate::parse($value))->toBeNull();
})->with([
    'N/A', 'n/a', 'NA', '-', '--', 'null', 'NULL', 'Presente', 'Atual', 'Present',
    'Não informado', 'desconhecido', 'a definir', '',
]);

it('discards what the model should have normalized to YYYY-MM-DD', function (string $value): void {
    expect(ResumeDate::parse($value))->toBeNull();
})->with([
    'mes e ano' => '03/2020',
    'mes e ano com hifen' => '03-2020',
    'ano e mes' => '2020-03',
    'mes em portugues' => 'março de 2020',
    'mes em ingles' => 'March 2020',
    'ordem brasileira' => '05/03/2020',
    'ordem americana' => '03/05/2020',
]);

it('discards the year alone, which Carbon would read as a time of day', function (): void {
    expect(ResumeDate::parse('2020'))->toBeNull();
});

it('discards a relative expression instead of resolving it to today', function (string $value): void {
    expect(ResumeDate::parse($value))->toBeNull();
})->with(['now', 'today', 'hoje', '+1 day', 'next monday']);

it('discards a date that does not exist in the calendar', function (string $value): void {
    expect(ResumeDate::parse($value))->toBeNull();
})->with([
    '31 de fevereiro' => '2020-02-31',
    'mes treze' => '2020-13-01',
    'dia zero' => '2020-03-00',
]);

it('discards a value that is not a string nor a date', function (mixed $value): void {
    expect(ResumeDate::parse($value))->toBeNull();
})->with([
    'null' => [null],
    'lista' => [['2020-03-05']],
    'booleano' => [true],
]);
