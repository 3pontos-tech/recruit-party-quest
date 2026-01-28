<?php

declare(strict_types=1);

return [
    'tabs' => [
        'overview' => 'Visão Geral',
        'experience' => 'Experiência',
    ],

    'section' => [
        'quick_actions' => 'Ações Rápidas',
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

    'proficiency' => [
        1 => 'Iniciante',
        2 => 'Básico',
        3 => 'Intermediário',
        4 => 'Avançado',
        5 => 'Especialista',
    ],
];
