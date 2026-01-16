<?php

declare(strict_types=1);

namespace He4rt\Ai\Database\Seeders;

use He4rt\Ai\Enums\AiAssistantApplication;
use He4rt\Ai\Enums\AiModel;
use He4rt\Ai\Enums\AiPromptMessageType;
use He4rt\Ai\Models\AiAssistant;
use He4rt\Ai\Models\AiThreadFolder;
use He4rt\Ai\Models\PromptType;
use He4rt\Users\User;
use Illuminate\Database\Seeder;

final class AiAssistantSeeder extends Seeder
{
    private string $assistantInstructions = <<<'PROMPT'
        ═══════════════════════════════════════════════════════════
        💡 EXEMPLOS DE RESPOSTAS HUMANIZADAS
        ═══════════════════════════════════════════════════════════

        ❌ FRIO: "Renda insuficiente para quitação em prazo razoável."
        ✅ HUMANIZADO: "Vi que sua renda é de R$ X. Vamos pensar juntos em formas de organizar isso, tá bom?"

        ❌ TÉCNICO: "Inadimplência detectada há 6 meses."
        ✅ AMIGÁVEL: "Vi que você tá com algumas contas atrasadas há uns meses. Sem problema, muita gente passa por isso! Vamos ver como resolver?"

        ❌ JULGADOR: "Você não deveria emprestar dinheiro."
        ✅ COMPREENSIVO: "Entendo que ajudar amigos e família é importante pra você. Vamos só ver como equilibrar isso com seus objetivos, combinado?"

        SEMPRE: Validação → Normalização → Solução positiva → Próxima pergunta
    PROMPT;

    private string $assistantKnowledge = <<<'PROMPT'
        Você é Sofia, uma instrutora financeira amigável e acolhedora da Pleno! 💙

        🌟 SEU JEITO DE SER:
        Você é aquela amiga de confiança que entende de dinheiro e adora ajudar as pessoas a organizarem suas finanças. Você é:
        • Calorosa e empática - como conversar com uma amiga próxima
        • Encorajadora - sempre vê o lado positivo primeiro
        • Compreensiva - entende que finanças podem ser difíceis
        • Clara - explica tudo de forma simples e direta
        • Sem julgamentos - NUNCA critica ou faz a pessoa se sentir mal
        • Otimista - sempre focada em soluções, não em problemas
    PROMPT;

    public function run(): void
    {
        $user = User::query()->first();

        $assistant = AiAssistant::query()->create([
            'name' => 'Financial Personal Assistant',
            'application' => AiAssistantApplication::PersonalAssistant,
            'model' => AiModel::OpenAiGpt4oMini,
            'is_default' => true,
            'description' => 'Financial Personal Assistant',
            'instructions' => $this->assistantInstructions,
            'knowledge' => $this->assistantKnowledge,
            'owner_id' => $user->getKey(),
            'archived_at' => null,
        ]);

        foreach ($this->promptTypes() as $promptType) {

            $promptTypeModel = PromptType::query()->create([
                'title' => $promptType['name'],
                'description' => $promptType['description'],
            ]);

            foreach ($promptType['items'] as $item) {
                $filename = str($item['name'])->slug()->toString();
                $fileContent = file_get_contents(resource_path(sprintf('prompts/rules/%s.md', $filename)));

                $prompt = $promptTypeModel->prompts()->create([
                    'message_type' => AiPromptMessageType::System,
                    'title' => $item['name'],
                    'description' => $item['description'],
                    'prompt' => $fileContent,
                ]);

                $assistant->prompts()->attach($prompt);
            }
        }

        AiThreadFolder::query()->create([
            'name' => 'Personal Assistant',
            'application' => AiAssistantApplication::PersonalAssistant,
            'user_id' => $user->getKey(),
        ]);
    }

    public function promptTypes(): array
    {
        return [
            [
                'name' => 'Psicologia e Comportamento Financeiro',
                'description' => 'Compreensão do impacto emocional e comportamental nas decisões financeiras.',
                'items' => [
                    [
                        'name' => 'Fundamentos de Psicologia Financeira',
                        'description' => 'Explora como crenças, emoções e experiências moldam o comportamento financeiro.',
                    ],
                    [
                        'name' => 'Gestão de Emoções e Sensibilidade',
                        'description' => 'Ensina como lidar com emoções intensas em conversas sobre dinheiro.',
                    ],
                    [
                        'name' => 'Normalização e Validação Emocional',
                        'description' => 'Promove empatia e acolhimento para reduzir julgamentos e vergonhas financeiras.',
                    ],
                    [
                        'name' => 'Motivação e Resiliência',
                        'description' => 'Trabalha estratégias para manter o engajamento e superação de desafios financeiros.',
                    ],
                ],
            ],
            [
                'name' => 'Comunicação e Relacionamento',
                'description' => 'Desenvolvimento de habilidades comunicativas para gerar conexão e confiança.',
                'items' => [
                    [
                        'name' => 'Comunicação Empática e Não-Julgadora',
                        'description' => 'Foca em escuta ativa, empatia e linguagem neutra em conversas financeiras.',
                    ],
                    [
                        'name' => 'Engajamento',
                        'description' => 'Cada interação deve ser classificada em um dos cinco estados abaixo, com base no tom, respostas, e consistência do usuário.',
                    ],
                    [
                        'name' => 'Construção de Confiança e Relacionamento',
                        'description' => 'Aborda técnicas para criar vínculo genuíno e segurança emocional com o cliente.',
                    ],
                    [
                        'name' => 'Padrões de Linguagem e Expressão',
                        'description' => 'Define diretrizes para uma comunicação clara, acolhedora e assertiva.',
                    ],
                    [
                        'name' => 'Gestão de Expectativas',
                        'description' => 'Ajuda a alinhar percepções e resultados esperados em um processo financeiro.',
                    ],
                ],
            ],
            [
                'name' => 'Ética e Responsabilidade Profissional',
                'description' => 'Princípios éticos e práticas seguras em atendimentos e diagnósticos financeiros.',
                'items' => [
                    [
                        'name' => 'Ética e Responsabilidade Financeira',
                        'description' => 'Garante transparência, sigilo e respeito nas interações sobre finanças pessoais.',
                    ],
                    [
                        'name' => 'Cenários Sensíveis e Tratamento Especial',
                        'description' => 'Orienta como lidar com situações delicadas, vulnerabilidades e crises financeiras.',
                    ],
                ],
            ],
            [
                'name' => 'Técnicas e Educação',
                'description' => 'Aplicação de métodos e estratégias educativas para orientar decisões financeiras.',
                'items' => [
                    [
                        'name' => 'Técnicas de Entrevista e Coleta de Informações',
                        'description' => 'Apresenta métodos eficazes para compreender o contexto e as necessidades do cliente.',
                    ],
                    [
                        'name' => 'Educação Financeira Acessível',
                        'description' => 'Foca em tornar o aprendizado financeiro simples, prático e inclusivo.',
                    ],
                ],
            ],
        ];

    }
}
