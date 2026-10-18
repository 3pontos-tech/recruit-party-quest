<?php

declare(strict_types=1);

return [
    'application_status' => [
        'new' => [
            'label' => 'Nova',
        ],
        'in_review' => [
            'label' => 'Em Revisão',
        ],
        'in_progress' => [
            'label' => 'Em Andamento',
        ],
        'offer_extended' => [
            'label' => 'Proposta Enviada',
        ],
        'offer_accepted' => [
            'label' => 'Proposta Aceita',
        ],
        'offer_declined' => [
            'label' => 'Proposta Recusada',
        ],
        'hired' => [
            'label' => 'Contratado',
        ],
        'rejected' => [
            'label' => 'Rejeitado',
        ],
        'withdrawn' => [
            'label' => 'Desistiu',
        ],
    ],
    'candidate_source' => [
        'linkedin' => [
            'label' => 'LinkedIn',
        ],
        'indeed' => [
            'label' => 'Indeed',
        ],
        'glassdoor' => [
            'label' => 'Glassdoor',
        ],
        'referral' => [
            'label' => 'Indicação',
        ],
        'career_page' => [
            'label' => 'Página de Carreiras',
        ],
        'community' => [
            'label' => '3 Pontos Comunidade',
        ],
        'three_dots' => [
            'label' => '3 Pontos',
        ],
        'other' => [
            'label' => 'Outro',
        ],
    ],
    'rejection_reason_category' => [
        'qualifications' => [
            'label' => 'Qualificações',
        ],
        'experience' => [
            'label' => 'Experiência',
        ],
        'culture_fit' => [
            'label' => 'Fit Cultural',
        ],
        'compensation' => [
            'label' => 'Remuneração',
        ],
        'location' => [
            'label' => 'Localização',
        ],
        'availability' => [
            'label' => 'Disponibilidade',
        ],
        'position_filled' => [
            'label' => 'Vaga Preenchida',
        ],
        'other' => [
            'label' => 'Outro',
        ],
        'screening_knockout' => [
            'label' => 'Triagem Automática',
        ],
    ],
    'application_status_group' => [
        'new' => ['label' => 'Novas'],
        'active' => ['label' => 'Em processo'],
        'offer' => ['label' => 'Oferta e contratação'],
        'closed' => ['label' => 'Encerradas'],
    ],
    'screening_verdict_filter' => [
        'all' => ['label' => 'Toda a triagem'],
        'passed' => ['label' => 'Aprovados na eliminatória'],
        'failed' => ['label' => 'Reprovados na eliminatória'],
        'unanswered' => ['label' => 'Sem respostas'],
    ],
    'seen_filter' => [
        'all' => ['label' => 'Vistos e não vistos'],
        'unseen' => ['label' => 'Não vistos'],
        'seen' => ['label' => 'Já vistos'],
    ],
    'application_list_sort' => [
        'attention' => ['label' => 'Atenção'],
        'days_in_stage' => ['label' => 'Mais tempo na etapa'],
        'applied' => ['label' => 'Inscrição mais recente'],
        'name' => ['label' => 'Nome'],
        'stage' => ['label' => 'Etapa mais avançada'],
    ],
];
