<?php

declare(strict_types=1);

return [
    'status' => [
        'pending' => 'Aguardando',
        'analyzing' => 'Analisando',
        'completed' => 'Concluida',
        'failed' => 'Falhou',
    ],
    'fields' => [
        'repository' => 'Repositório',
        'language' => 'Linguagem',
        'status' => 'Status',
        'analyzed_at' => 'Analisado em',
        'private' => 'Privado',
        'public' => 'Público',
    ],
    'actions' => [
        'view' => 'Ver',
        'new_analysis' => 'Analisar Repositório',
        'analyze' => 'Analisar Repositório',
        'back_to_list' => 'Voltar para análises',
        'disabled' => [
            'no_github' => 'Conecte seu GitHub para analisar',
            'in_progress' => 'Análise em andamento...',
            'cooldown' => 'Disponível em :days dia|Disponível em :days dias',
        ],
    ],
    'notifications' => [
        'analysis_started' => 'Análise iniciada! Aguarde enquanto processamos seu repositório.',
        'cooldown_active' => 'Este repositório poderá ser reanalisado a partir de :date.',
        'analysis_completed' => 'A análise de :repo foi concluída!',
        'analysis_failed' => 'A análise falhou. Por favor, tente novamente.',
        'analysis_completed_list' => 'Uma análise foi concluída.',
        'cooldown_redirect' => 'Sua próxima análise estará disponível em :days dia.|Sua próxima análise estará disponível em :days dias.',
        'github_unavailable' => 'Não foi possível conectar ao GitHub. Por favor, tente novamente mais tarde.',
        'analysis_in_progress' => 'Você já possui uma análise em andamento. Aguarde a conclusão antes de iniciar outra.',
    ],
    'page' => [
        'list' => [
            'heading' => 'Análise de Código',
            'empty_heading' => 'Nenhuma análise ainda',
            'empty_description' => 'Analise um repositório para receber feedback técnico sobre seu código.',
            'no_github' => [
                'heading' => 'Conecte sua conta GitHub',
                'description' => 'Para visualizar e analisar seus repositórios, conecte sua conta GitHub primeiro.',
                'button' => 'Conectar GitHub',
            ],
        ],
        'new' => [
            'heading' => 'Analisar Repositório',
            'no_github' => [
                'heading' => 'Conecte sua conta GitHub',
                'description' => 'Para analisar um repositório, você precisa conectar sua conta GitHub primeiro.',
                'button' => 'Conectar GitHub',
            ],
            'cooldown' => [
                'heading' => 'Cooldown ativo',
                'description' => 'Este repositório poderá ser reanalisado a partir de :date.',
            ],
        ],
        'result' => [
            'analyzing' => 'Seu repositório está sendo analisado. Isso pode levar alguns minutos...',
            'failed' => 'A análise falhou. Por favor, tente novamente.',
            'summary' => 'Resumo',
            'problems' => 'Problemas encontrados',
            'suggestions' => 'Sugestões de melhoria',
            'learning_topics' => 'O que estudar',
            'comparison' => 'Comparação com análise anterior',
            'improvements' => 'Melhorias desde a última análise',
            'unchanged_issues' => 'Problemas ainda presentes',
            'regressions' => 'Novos problemas ou regressões',
        ],
    ],
    'impact_levels' => [
        'high' => 'Alto',
        'medium' => 'Médio',
        'low' => 'Baixo',
    ],
    'components' => [
        'repository_grid' => [
            'heading' => 'Repositórios',
            'count_singular' => ':count repositório atualizado recentemente',
            'count_plural' => ':count repositórios atualizados recentemente',
            'empty' => [
                'heading' => 'Nenhum repositório encontrado',
                'description' => 'Conecte sua conta do GitHub para começar',
            ],
        ],
        'repository_card' => [
            'analyze_button' => 'Analisar',
            'branch_label' => 'Branch padrão',
        ],
        'analysis_grid' => [
            'heading' => 'Análises',
            'count_singular' => ':count análise realizada',
            'count_plural' => ':count análises realizadas',
            'empty' => [
                'heading' => 'Nenhuma análise encontrada',
                'description' => 'Selecione um repositório para começar',
            ],
        ],
        'analysis_card' => [
            'processing' => 'Processando...',
            'view_button' => 'Ver',
        ],
        'analysis_header' => [
            'view_on_github' => 'Ver no GitHub',
            'back_to_list' => 'Voltar para lista',
        ],
        'summary_section' => [
            'heading' => 'Resumo',
        ],
        'highlights_section' => [
            'heading' => 'Destaques e Riscos',
            'strengths_heading' => 'Pontos Fortes',
            'risks_heading' => 'Principais Riscos',
        ],
        'category_section' => [
            'problems_heading' => 'Problemas',
            'problems_count' => 'Problemas ( :count )',
            'suggestions_heading' => 'Sugestões',
            'suggestions_count' => 'Sugestões ( :count )',
            'study_topics_heading' => 'Tópicos de Aprendizado',
            'why_it_matters' => 'Por que isso importa',
        ],
        'detected_stack' => [
            'dependencies_heading' => 'Dependências Principais',
        ],
        'loading_state' => [
            'analyzing_heading' => 'Analisando repositório...',
            'analyzing_description' => 'Isso pode levar alguns minutos. Por favor, aguarde.',
            'back_button' => 'Voltar para lista',
        ],
        'error_state' => [
            'heading' => 'Falha na análise',
            'description' => 'Ocorreu um erro ao analisar o repositório. Por favor, tente novamente.',
            'back_button' => 'Voltar para lista',
        ],
    ],
];
