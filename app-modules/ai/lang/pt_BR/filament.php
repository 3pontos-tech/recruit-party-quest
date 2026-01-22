<?php

declare(strict_types=1);

return [
    'cluster' => [
        'navigation_label' => 'Inteligência Artificial',
        'breadcrumb' => 'IA',
    ],
    'resource' => [
        'ai_assistant' => [
            'label' => 'Assistente Customizado',
            'plural_label' => 'Assistentes Customizados',
            'navigation_label' => 'Assistentes Customizados',
        ],
        'ai_message' => [
            'label' => 'Mensagem de IA',
            'plural_label' => 'Mensagens de IA',
            'navigation_label' => 'Mensagens de IA',
        ],
        'ai_thread' => [
            'label' => 'Conversa de IA',
            'plural_label' => 'Conversas de IA',
            'navigation_label' => 'Conversas de IA',
        ],
        'ai_thread_folder' => [
            'label' => 'Pasta de Conversa',
            'plural_label' => 'Pastas de Conversas',
            'navigation_label' => 'Pastas de Conversas',
        ],
        'prompt' => [
            'label' => 'Prompt',
            'plural_label' => 'Prompts',
            'navigation_label' => 'Biblioteca de Prompts',
        ],
        'prompt_type' => [
            'label' => 'Tipo de Prompt',
            'plural_label' => 'Tipos de Prompts',
            'navigation_label' => 'Tipos de Prompts',
        ],
    ],
    'fields' => [
        'created_at' => 'Data de Criação',
        'updated_at' => 'Última Modificação',
        'saved_at' => 'Data de Salvamento',
        'locked_at' => 'Data de Bloqueio',
        'avatar' => 'Avatar',
        'owner' => 'Criado por',
    ],
    'enums' => [
        'application' => [
            'personal_assistant' => [
                'label' => 'Assistente Customizado',
            ],
            'test' => [
                'label' => 'Teste',
            ],
        ],
        'prompt_tabs' => [
            'newest' => [
                'label' => 'Mais recentes',
            ],
            'most_loved' => [
                'label' => 'Mais curtidos',
            ],
            'most_viewed' => [
                'label' => 'Mais vistos',
            ],
        ],
        'message_log_feature' => [
            'draft_with_ai' => [
                'label' => 'Rascunho com IA',
            ],
            'conversations' => [
                'label' => 'Conversas',
            ],
        ],
        'thread_share_target' => [
            'user' => [
                'label' => 'Usuário',
            ],
            'team' => [
                'label' => 'Time',
            ],
        ],
        'reasoning_effort' => [
            'low' => [
                'label' => 'Baixo',
            ],
            'medium' => [
                'label' => 'Médio',
            ],
            'high' => [
                'label' => 'Alto',
            ],
        ],
        'model' => [
            'gpt-5-mini' => [
                'label' => 'GPT-5 Mini',
            ],
            'test' => [
                'label' => 'Teste',
            ],
        ],
    ],
    'sections' => [
        'configure_ai_advisor' => [
            'title' => 'Configurar Assistente de IA',
            'description' => 'Desenhe a capacidade do seu assistente incluindo instruções detalhadas abaixo.',
        ],
    ],
];
