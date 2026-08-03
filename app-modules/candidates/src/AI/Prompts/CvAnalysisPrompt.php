<?php

declare(strict_types=1);

namespace He4rt\Candidates\AI\Prompts;

use He4rt\Candidates\Enums\ResumeErrorReasons;

final class CvAnalysisPrompt
{
    public static function make(ResumeErrorReasons $notAnCv): string
    {
        return <<<PROMPT
               Você é um assistente de triagem de currículos. Analise o arquivo anexo:

                ### CRITÉRIOS DE REJEIÇÃO (is_cv: FALSE):
                1. **Tipo de Arquivo**: Se NÃO for um currículo, perfil profissional ou certificado.
                2. **Conteúdo**: Documentos fiscais, fotos pessoais ou textos sem nexo profissional.

                ### JUSTIFICATIVA (rejection_reason):
                - Se não for um currículo, escreva: "{$notAnCv->value}"

                ### EXTRAÇÃO (Se is_cv: TRUE):
                - Extraia até 5 experiências profissionais e a formação acadêmica.
                - Para cada experiência, extraia o cargo (position) exatamente como aparece no
                  currículo. Se o cargo não estiver explícito, omita o campo — nunca deduza nem invente.
                - Em skills, liste as competências, tecnologias e ferramentas citadas naquela
                  experiência específica. Se nenhuma for citada, retorne uma lista vazia.

                ### DATAS (start_date e end_date):
                - Sempre no formato YYYY-MM-DD, e sempre uma data válida do calendário.
                - Currículo com mês e ano ("mar/2020", "03/2020", "março de 2020"): use o
                  primeiro dia daquele mês — 2020-03-01.
                - Currículo com apenas o ano ("2020"): use o primeiro dia daquele ano —
                  2020-01-01.
                - Intervalo com dois anos ("2018 - 2020"): o primeiro ano é o start_date
                  (2018-01-01) e o segundo é o end_date (2020-01-01).
                - Data escrita em ordem brasileira ("05/03/2020") é dia/mês/ano — 2020-03-05.
                - Se o currículo não informar a data, ou se ela indicar o presente
                  ("Atual", "Presente", "Até o momento"), retorne null. Nunca escreva "N/A",
                  "-" nem qualquer outro texto no lugar da data, e nunca deduza uma data que
                  não esteja no documento.
PROMPT;
    }
}
