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
        'placeholders' => [
            'headline' => 'ex: Engenheiro de Software Sênior | Desenvolvedor Full-Stack',
            'summary' => 'Escreva um breve resumo profissional destacando sua expertise, experiência e objetivos de carreira...',
            'phone_number' => 'ex: +55 (11) 98765-4321',
            'linkedin_url' => 'seu-nome-perfil',
            'portfolio_url' => 'www.seuportfolio.com.br',
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
        'placeholders' => [
            'expected_salary' => 'ex: 80000',
            'expected_salary_currency' => 'Selecione a moeda...',
            'experience_level' => 'Selecione seu nível de experiência...',
            'timezone' => 'Pesquise ou selecione o fuso horário...',
            'preferred_language' => 'Selecione o idioma de preferência...',
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
        'add_education' => 'Adicionar Formação',
        'fields' => [
            'education' => 'Educação',
            'institution' => 'Instituição',
            'degree' => 'Grau / Título',
            'field_of_study' => 'Área de Estudo',
            'start_date' => 'Data de Início',
            'end_date' => 'Data de Término',
            'is_enrolled' => 'Matriculado Atualmente',
        ],
        'placeholders' => [
            'institution' => 'ex: Universidade de São Paulo',
            'degree' => 'ex: Bacharelado em Ciência da Computação',
            'field_of_study' => 'ex: Engenharia de Software',
        ],
    ],

    'work_experience' => [
        'heading' => 'Experiência Profissional',
        'description' => 'Gerencie seu histórico profissional.',
        'submit' => 'Salvar Experiência',
        'notify' => 'Experiência profissional atualizada com sucesso.',
        'add_work_experience' => 'Adicionar Experiência',
        'fields' => [
            'work_experiences' => 'Experiências Profissionais',
            'company_name' => 'Nome da Empresa',
            'description' => 'Descrição',
            'start_date' => 'Data de Início',
            'end_date' => 'Data de Término',
            'is_currently_working_here' => 'Trabalho Aqui Atualmente',
        ],
        'placeholders' => [
            'company_name' => 'ex: Google, Microsoft, Startup Inc.',
            'description' => 'Descreva suas responsabilidades, conquistas e projetos principais...',
        ],
    ],

    'skills' => [
        'heading' => 'Habilidades',
        'description' => 'Gerencie suas habilidades e níveis de proficiência.',
        'submit' => 'Salvar Habilidades',
        'notify' => 'Habilidades atualizadas com sucesso.',
        'add_skill' => 'Adicionar Habilidade',
        'fields' => [
            'skills' => 'Habilidades',
            'skill' => 'Habilidade',
            'years_of_experience' => 'Anos de Experiência',
            'proficiency_level' => 'Nível de Proficiência',
        ],
        'placeholders' => [
            'skill' => 'Pesquise ou selecione uma habilidade...',
            'years_suffix' => 'anos',
            'proficiency_level' => 'Selecione o nível de proficiência...',
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

    'links' => [
        'heading' => 'Links',
        'description' => 'Gerencie seus links sociais e profissionais.',
        'submit' => 'Salvar Links',
        'notify' => 'Links atualizados com sucesso.',
        'add_link' => 'Adicionar Link',
        'fields' => [
            'links' => 'Links',
            'url' => 'URL',
            'name' => 'Rótulo',
            'type' => 'Tipo',
            'icon' => 'Ícone',
        ],
        'placeholders' => [
            'url' => 'https://...',
            'name' => 'ex: Meu Portfólio',
        ],
    ],
];
