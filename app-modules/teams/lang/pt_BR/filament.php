<?php

declare(strict_types=1);

return [
    'resource' => [
        'label' => 'Time',
        'plural_label' => 'Times',
        'navigation_label' => 'Times',
    ],
    'fields' => [
        'id' => 'ID',
        'name' => 'Nome',
        'description' => 'Descrição',
        'slug' => 'Slug',
        'status' => 'Status',
        'owner' => 'Dono',
        'contact_email' => 'E-mail de Contato',
        'members_count' => 'Quantidade de Membros',
        'created_at' => 'Criado em',
        'updated_at' => 'Atualizado em',
        'deleted_at' => 'Excluído em',
        'email' => 'E-mail',
        'password' => 'Senha',
    ],
    'table' => [
        'slug_description' => 'slug: :slug',
    ],
    'relation_managers' => [
        'members' => [
            'title' => 'Membros',
            'label' => 'Membro',
            'plural_label' => 'Membros',
            'joined_at' => 'Entrou em',
            'invite_action' => 'Convidar Membro',
            'invite_heading' => 'Convidar Novo Membro',
            'invite_description' => 'Criar um novo usuário e adicioná-lo a este time.',
            'invite_success' => 'Convite enviado com sucesso!',
        ],
        'departments' => [
            'title' => 'Departamentos',
            'label' => 'Departamento',
            'plural_label' => 'Departamentos',
        ],
    ],
    'department' => [
        'label' => 'Departamento',
        'plural_label' => 'Departamentos',
        'navigation_label' => 'Departamentos',
        'fields' => [
            'id' => 'ID',
            'team' => 'Time',
            'name' => 'Nome',
            'description' => 'Descrição',
            'head_user' => 'Responsável',
            'requisitions_count' => 'Requisições',
            'created_at' => 'Criado em',
            'updated_at' => 'Atualizado em',
            'deleted_at' => 'Excluído em',
        ],
        'filters' => [
            'team' => 'Time',
        ],
        'create_action' => 'Criar Departamento',
    ],
    'emails' => [
        'team_invitation' => [
            'subject' => 'Bem-vindo ao time :team_name!',
            'greeting' => 'Olá :name,',
            'introduction' => 'Você foi convidado a se juntar ao time :team_name.',
            'credentials_title' => 'Suas Credenciais de Acesso',
            'email_label' => 'E-mail',
            'password_label' => 'Senha Temporária',
            'instructions' => 'Por favor, faça login usando estas credenciais. Recomendamos alterar sua senha após o primeiro acesso.',
            'login_button' => 'Entrar Agora',
            'forgot_password_button' => 'Esqueceu Sua Senha?',
            'footer' => 'Time :team_name',
        ],
    ],
];
