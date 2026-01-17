<?php

declare(strict_types=1);

return [
    'title' => 'Onboarding do Candidato',
    'actions' => [
        'save_progress' => 'Salvar Progresso',
    ],
    'notifications' => [
        'progress_saved' => [
            'title' => 'Progresso Salvo',
            'message' => 'Seu progresso de onboarding foi salvo. Você pode continuar mais tarde.',
        ],
        'consent_required' => [
            'title' => 'Consentimento Necessário',
            'message' => 'Você deve consentir com o processamento de dados para concluir o onboarding.',
        ],
        'work_experience_required' => [
            'title' => 'Experiência Profissional Necessária',
            'message' => 'Por favor, adicione pelo menos uma experiência profissional.',
        ],
        'education_required' => [
            'title' => 'Educação Necessária',
            'message' => 'Por favor, adicione pelo menos uma entrada de educação.',
        ],
        'onboarding_complete' => [
            'title' => 'Onboarding Concluído',
            'message' => 'Seu perfil foi configurado com sucesso! Você já pode começar a se candidatar a vagas.',
        ],
    ],
    'steps' => [
        'account' => [
            'label' => 'Conta & Identidade',
            'sections' => [
                'account_info' => 'Informações da Conta',
            ],
            'fields' => [
                'email' => 'E-mail',
                'timezone' => 'Fuso Horário',
                'preferred_language' => 'Idioma de Preferência',
                'data_consent' => 'Eu consinto com o processamento dos meus dados pessoais',
                'data_consent_helper' => 'Isso é necessário para prosseguir com sua candidatura.',
            ],
        ],
        'cv' => [
            'label' => 'CV / Currículo',
            'description' => 'Faça o upload do seu currículo para análise',
            'sections' => [
                'upload_cv' => 'Upload de Currículo',
            ],
            'fields' => [
                'cv_file' => 'Upload de Currículo',
                'cv_file_helper' => 'Arquivos PDF ou DOC de até 10MB',
            ],
        ],
        'profile' => [
            'label' => 'Perfil Profissional',
            'sections' => [
                'work_experience' => 'Experiência Profissional',
                'education' => 'Educação',
                'skills' => 'Habilidades',
            ],
            'fields' => [
                'work_experience' => 'Experiência Profissional',
                'company_name' => 'Nome da Empresa',
                'description' => 'Descrição',
                'start_date' => 'Data de Início',
                'end_date' => 'Data de Término',
                'is_currently_working_here' => 'Trabalho aqui atualmente',
                'education' => 'Educação',
                'institution' => 'Instituição',
                'degree' => 'Grau / Título',
                'field_of_study' => 'Área de Estudo',
                'is_enrolled' => 'Matriculado atualmente',
                'skills' => 'Habilidades',
                'skills_helper' => 'Pelo menos 3 habilidades são necessárias',
            ],
        ],
        'preferences' => [
            'label' => 'Preferências & Disponibilidade',
            'sections' => [
                'compensation' => 'Remuneração',
                'availability' => 'Disponibilidade',
                'job_interests' => 'Interesses de Trabalho',
            ],
            'fields' => [
                'expected_salary' => 'Pretensão Salarial',
                'currency' => 'Moeda',
                'availability_date' => 'Data de Disponibilidade',
                'availability_date_helper' => 'Quando você está disponível para começar?',
                'willing_to_relocate' => 'Disponível para Relocação',
                'is_open_to_remote' => 'Aberto a Trabalho Remoto',
                'experience_level' => 'Nível de Experiência',
                'employment_type_interests' => 'Tipos de Emprego (separados por vírgula)',
                'employment_type_interests_helper' => 'Tempo integral, contratado, estagiário, etc.',
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
                    'en' => 'Inglês',
                    'pt' => 'Português',
                    'es' => 'Espanhol',
                ],
            ],
        ],
        'review' => [
            'label' => 'Revisar & Publicar',
            'sections' => [
                'review_summary' => 'Resumo da Revisão',
            ],
            'fields' => [
                'email' => 'E-mail',
                'phone' => 'Telefone',
                'headline' => 'Título Profissional',
                'expected_salary' => 'Pretensão Salarial',
                'confirm_submission' => 'Confirmo que todas as informações estão corretas e completas',
            ],
        ],
    ],
];
