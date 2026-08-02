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
PROMPT;
    }
}
