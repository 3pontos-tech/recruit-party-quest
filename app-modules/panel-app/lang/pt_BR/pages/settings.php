<?php

declare(strict_types=1);

return [
    'profile_info' => [
        'heading' => 'Informações do Perfil',
        'description' => 'Atualize os detalhes do seu perfil profissional e informações de contato.',
        'submit' => 'Salvar Perfil',
        'notify' => 'Informações do perfil atualizadas com sucesso.',
        'fields' => [
            'headline' => 'Título Profissional',
            'summary' => 'Resumo',
            'phone_number' => 'Telefone',
            'linkedin_url' => 'URL do LinkedIn',
            'portfolio_url' => 'URL do Portfólio',
        ],
    ],

    'preferences' => [
        'heading' => 'Preferências e Disponibilidade',
        'description' => 'Gerencie suas preferências de trabalho, expectativa salarial e disponibilidade.',
        'submit' => 'Salvar Preferências',
        'notify' => 'Preferências atualizadas com sucesso.',
        'fields' => [
            'expected_salary' => 'Pretensão Salarial',
            'expected_salary_currency' => 'Moeda',
            'availability_date' => 'Data de Disponibilidade',
            'willing_to_relocate' => 'Disponível para Realocação',
            'is_open_to_remote' => 'Aberto a Trabalho Remoto',
            'experience_level' => 'Nível de Experiência',
            'timezone' => 'Fuso Horário',
            'preferred_language' => 'Idioma de Preferência',
        ],
        'options' => [
            'experience_levels' => [
                'intern' => 'Estagiário',
                'entry_level' => 'Nível Inicial',
                'mid_level' => 'Nível Pleno',
                'senior' => 'Sênior',
                'lead' => 'Líder',
                'principal' => 'Principal',
            ],
            'languages' => [
                'pt_BR' => 'Português (Brasil)',
                'en_US' => 'Inglês (Estados Unidos)',
            ],
        ],
    ],

    'education' => [
        'heading' => 'Educação',
        'description' => 'Gerencie sua formação acadêmica.',
        'submit' => 'Salvar Educação',
        'notify' => 'Educação atualizada com sucesso.',
        'fields' => [
            'education' => 'Educação',
            'institution' => 'Instituição',
            'degree' => 'Grau / Título',
            'field_of_study' => 'Área de Estudo',
            'start_date' => 'Data de Início',
            'end_date' => 'Data de Término',
            'is_enrolled' => 'Matriculado Atualmente',
        ],
    ],

    'work_experience' => [
        'heading' => 'Experiência Profissional',
        'description' => 'Gerencie seu histórico profissional.',
        'submit' => 'Salvar Experiência',
        'notify' => 'Experiência profissional atualizada com sucesso.',
        'fields' => [
            'work_experiences' => 'Experiências Profissionais',
            'company_name' => 'Nome da Empresa',
            'description' => 'Descrição',
            'start_date' => 'Data de Início',
            'end_date' => 'Data de Término',
            'is_currently_working_here' => 'Trabalho Aqui Atualmente',
        ],
    ],

    'skills' => [
        'heading' => 'Habilidades',
        'description' => 'Gerencie suas habilidades e níveis de proficiência.',
        'submit' => 'Salvar Habilidades',
        'notify' => 'Habilidades atualizadas com sucesso.',
        'fields' => [
            'skills' => 'Habilidades',
            'skill' => 'Habilidade',
            'years_of_experience' => 'Anos de Experiência',
            'proficiency_level' => 'Nível de Proficiência',
        ],
        'options' => [
            'proficiency_levels' => [
                1 => 'Iniciante',
                2 => 'Elementar',
                3 => 'Intermediário',
                4 => 'Avançado',
                5 => 'Especialista',
            ],
        ],
    ],
];
