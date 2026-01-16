<?php

declare(strict_types=1);

return [
    'resource' => [
        'label' => 'Candidato',
        'plural_label' => 'Candidatos',
        'navigation_label' => 'Candidatos',
    ],
    'sections' => [
        'user_info' => 'Informações do Usuário',
        'professional_info' => 'Informações Profissionais',
        'availability' => 'Disponibilidade',
        'compensation' => 'Expectativa Salarial',
        'links' => 'Links & Portfólio',
    ],
    'fields' => [
        'id' => 'ID',
        'user' => 'Usuário',
        'name' => 'Nome',
        'email' => 'E-mail',
        'phone' => 'Telefone',
        'headline' => 'Título Profissional',
        'summary' => 'Resumo',
        'availability_date' => 'Disponível a partir de',
        'is_willing_to_relocate' => 'Aceita Relocação',
        'is_open_to_remote' => 'Aceita Remoto',
        'expected_salary' => 'Salário Esperado',
        'expected_salary_currency' => 'Moeda',
        'linkedin_url' => 'URL do LinkedIn',
        'portfolio_url' => 'URL do Portfólio',
        'skills_count' => 'Habilidades',
        'applications_count' => 'Candidaturas',
        'created_at' => 'Criado em',
        'updated_at' => 'Atualizado em',
        'deleted_at' => 'Excluído em',
    ],
    'filters' => [
        'is_willing_to_relocate' => 'Aceita Relocação',
        'is_open_to_remote' => 'Aceita Remoto',
    ],
];
