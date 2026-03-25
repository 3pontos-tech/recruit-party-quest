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
PROMPT;
    }
}
