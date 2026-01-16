<?php

declare(strict_types=1);

return [
    'relation_managers' => [
        'evaluations' => [
            'title' => 'Avaliações',
            'label' => 'Avaliação',
            'plural_label' => 'Avaliações',
        ],
    ],
    'evaluation' => [
        'sections' => [
            'context' => 'Contexto da Avaliação',
            'rating' => 'Classificação Geral',
            'detailed_feedback' => 'Feedback Detalhado',
            'criteria_scores' => 'Notas por Critério',
            'submission' => 'Detalhes do Envio',
        ],
        'fields' => [
            'stage' => 'Etapa',
            'evaluator' => 'Avaliador',
            'overall_rating' => 'Classificação Geral',
            'strengths' => 'Pontos Fortes',
            'concerns' => 'Preocupações',
            'recommendation' => 'Recomendação',
            'notes' => 'Notas',
            'criteria_scores' => 'Notas por Critério',
            'submitted_at' => 'Enviado em',
            'created_at' => 'Criado em',
        ],
    ],
];
