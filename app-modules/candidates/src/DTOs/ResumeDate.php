<?php

declare(strict_types=1);

namespace He4rt\Candidates\DTOs;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Throwable;

/**
 * Converte a data crua de um currículo em `CarbonImmutable`, ou em `null` quando o valor não
 * é uma data no formato acordado.
 *
 * A normalização é responsabilidade do modelo: o prompt e o schema pedem `YYYY-MM-DD`, com
 * regra explícita para currículo que informa só mês e ano (dia 01) ou só o ano (01/01), e
 * `null` quando não há data. Aqui só se valida o contrato.
 *
 * Existe porque a extração é probabilística e o contrato pode ser violado — foi um `N/A` no
 * lugar da data que derrubou o `AiAnalyzeResumeJob` em produção, perdendo a análise inteira
 * por causa de um campo. Além de estourar, `Carbon::parse()` erra calado no que sobra:
 * `2020` vira 20:20 de hoje e `05/03/2020` vira 3 de maio, não 5 de março. Por isso o que
 * não segue o formato é descartado em vez de adivinhado — data ausente o candidato corrige
 * no wizard, data errada ninguém percebe.
 */
final class ResumeDate
{
    public static function parse(mixed $value): ?CarbonImmutable
    {
        if ($value instanceof DateTimeInterface) {
            return CarbonImmutable::instance($value);
        }

        if (! is_string($value) || blank($value)) {
            return null;
        }

        $value = mb_trim($value);

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})(?:[\sT].*)?$/', $value, $matches) !== 1) {
            return self::discard($value);
        }

        [, $year, $month, $day] = array_map(intval(...), $matches);

        if (! checkdate($month, $day, $year)) {
            return self::discard($value);
        }

        try {
            return CarbonImmutable::create($year, $month, $day)->startOfDay();
        } catch (Throwable) {
            return self::discard($value);
        }
    }

    private static function discard(string $value): null
    {
        logger()->warning('Resume date discarded for not following YYYY-MM-DD', ['value' => $value]);

        return null;
    }
}
