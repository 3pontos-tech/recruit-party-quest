<?php

declare(strict_types=1);

return [
    'resource' => [
        'label' => '',
        'plural_label' => '',
        'navigation_label' => '',
    ],
    'fields' => [],
    'table' => [],
    'relation_managers' => [],
    'requisition' => [
        'label' => 'Requisição de Vaga',
        'plural_label' => 'Requisições de Vagas',
        'navigation_label' => 'Requisições de Vagas',
        'sections' => [
            'basic_information' => 'Informações Básicas',
            'position_details' => 'Detalhes da Posição',
            'compensation' => 'Remuneração',
            'settings' => 'Configurações',
        ],
        'fields' => [
            'id' => 'ID',
            'team' => 'Time',
            'department' => 'Departamento',
            'hiring_manager' => 'Gestor Responsável',
            'status' => 'Status',
            'priority' => 'Prioridade',
            'work_arrangement' => 'Regime de Trabalho',
            'employment_type' => 'Tipo de Contratação',
            'experience_level' => 'Nível de Experiência',
            'positions_available' => 'Vagas Disponíveis',
            'salary_range' => 'Faixa Salarial',
            'salary_range_min' => 'Salário Mínimo',
            'salary_range_max' => 'Salário Máximo',
            'salary_currency' => 'Moeda',
            'is_internal_only' => 'Apenas Interno',
            'is_confidential' => 'Confidencial',
            'target_start_at' => 'Data de Início Prevista',
            'published_at' => 'Publicado em',
            'created_at' => 'Criado em',
            'updated_at' => 'Atualizado em',
            'deleted_at' => 'Excluído em',
        ],
        'filters' => [
            'status' => 'Status',
            'priority' => 'Prioridade',
            'work_arrangement' => 'Regime de Trabalho',
            'employment_type' => 'Tipo de Contratação',
            'experience_level' => 'Nível de Experiência',
            'team' => 'Time',
            'department' => 'Departamento',
            'is_internal_only' => 'Apenas Interno',
            'is_confidential' => 'Confidencial',
        ],
    ],
];
