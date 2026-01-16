<?php

declare(strict_types=1);

return [
    'relation_managers' => [
        'questions' => [
            'title' => 'Perguntas de Triagem',
            'label' => 'Pergunta',
            'plural_label' => 'Perguntas',
        ],
    ],
    'question' => [
        'fields' => [
            'question_text' => 'Texto da Pergunta',
            'question_type' => 'Tipo de Pergunta',
            'display_order' => 'Ordem de Exibição',
            'choices' => 'Opções',
            'choice_value' => 'Valor',
            'choice_label' => 'Rótulo',
            'is_required' => 'Obrigatória',
            'is_knockout' => 'Filtro Eliminatório (Knockout)',
            'knockout_criteria' => 'Critérios de Eliminação',
            'responses_count' => 'Respostas',
        ],
    ],
];
