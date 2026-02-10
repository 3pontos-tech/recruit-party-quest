<?php

declare(strict_types=1);

return [
    'tabs' => [
        'overview' => 'Visão Geral',
        'experience' => 'Experiência',
        'comments' => 'Comentários',
        'screening-responses' => 'Respostas de Screening',
    ],

    'section' => [
        'quick_actions' => 'Ações Rápidas',
    ],
    'applications' => [
        'resource' => [
            'navigation_label' => 'Candidaturas',
            'label' => 'Candidatura',
            'plural_label' => 'Candidaturas',
        ],
    ],
    'job-requisitions' => [
        'resource' => [
            'navigation_label' => 'Gestão de Vagas',
            'label' => 'Gestão de Vaga',
            'plural_label' => 'Gestão de Vagas',
        ],
    ],

    'actions' => [
        'advance_stage' => [
            'label' => 'Avançar Etapa',
            'modal_heading' => 'Avançar para a Próxima Etapa',
            'modal_description' => 'Deseja avançar este candidato para a próxima etapa do recrutamento?',
        ],
        'schedule_interview' => [
            'label' => 'Agendar Entrevista',
            'modal_heading' => 'Agendar Entrevista',
            'modal_description' => 'Agende uma entrevista com o candidato.',
        ],
        'send_email' => [
            'label' => 'Enviar E-mail',
            'modal_heading' => 'Enviar E-mail ao Candidato',
            'modal_description' => 'Enviar um e-mail personalizado ao candidato.',
        ],
        'add_comment' => [
            'label' => 'Adicionar Comentário Interno',
            'modal_heading' => 'Adicionar Comentário Interno',
            'modal_description' => 'Adicione uma nota visível somente para recrutadores e administradores.',
        ],
        'reject_application' => [
            'label' => 'Rejeitar Candidatura',
            'modal_heading' => 'Rejeitar Candidatura',
            'modal_description' => 'Esta ação não pode ser desfeita. O candidato será notificado da rejeição.',
        ],
        'generate_job_requisition' => [
            'label' => 'Gerar com IA',
            'modal_heading' => 'Gerar Requisição de Vaga com IA',
            'modal_description' => 'Forneça informações básicas e deixe a IA gerar uma requisição e anúncio de vaga completo baseado no perfil da sua empresa.',
            'fields' => [
                'title' => 'Título da Vaga',
                'description' => 'Tecnologias & Detalhes do Trabalho',
                'description_helper' => 'Liste tecnologias-chave, ferramentas e detalhes importantes do trabalho. A IA usará isso para gerar a descrição completa.',
                'experience_level' => 'Nível de Experiência',
                'experience_level_description' => 'Senioridade exigida para esta posição',
                'employment_type' => 'Tipo de Contratação',
                'work_arrangement' => 'Modelo de Trabalho',
                'work_arrangement_description' => 'Onde e como o colaborador irá trabalhar',
                'priority' => 'Prioridade',
                'priority_description' => 'Qual a urgência para fechar esta posição?',
            ],
            'success_title' => 'Vaga criada com sucesso!',
            'success_body' => 'A IA gerou sua requisição e anúncio de vaga. Revise e edite conforme necessário antes de publicar.',
            'error_title' => 'Falha na Geração',
        ],
    ],

    'notifications' => [
        'ok_title' => 'Pronto',
        'ok_body' => 'Ação concluída com sucesso.',
    ],

    'tables' => [
        'last_movement' => 'Última Movimentação',
        'position' => 'Cargo',
        'kanban' => 'Kanban',
    ],

    'group' => [
        'job' => 'Vaga',
        'job_no_title' => 'Vaga sem título',
        'job_description' => 'Empresa: :team • Departamento: :department',
    ],

    'kanban' => [
        'sections' => [
            'candidate_information' => 'Informações do Candidato',
            'application_comments' => 'Comentários da Candidatura',
            'skills' => 'Habilidades',
        ],
        'skill_name' => 'Nome da Habilidade',
    ],

    'forms' => [
        'overall_rating' => 'Avaliação Geral',
        'scores' => 'Pontuações',
        'criteria_key_placeholder' => 'Critério',
        'comments' => 'Comentários',
        'comments_placeholder' => 'Digite seus comentários...',
        'recommendation' => 'Recomendação',
        'recommendation_placeholder' => 'Digite sua recomendação...',
        'strengths' => 'Pontos Fortes',
        'strengths_placeholder' => 'Descreva os pontos fortes do candidato...',
        'concerns' => 'Preocupações',
        'concerns_placeholder' => 'Descreva quaisquer preocupações...',
    ],

    'proficiency' => [
        1 => 'Iniciante',
        2 => 'Básico',
        3 => 'Intermediário',
        4 => 'Avançado',
        5 => 'Especialista',
    ],
];
