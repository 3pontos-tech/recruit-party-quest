<?php

declare(strict_types=1);

return [
    'knockout_helper' => '(pergunta eliminatória)',

    'yes_no' => [
        'label' => 'Sim/Não',
        'yes' => 'Sim',
        'no' => 'Não',
    ],
    'text' => [
        'label' => 'Texto',
        'settings' => [
            'max_length' => 'Tamanho Máximo',
            'multiline' => 'Múltiplas Linhas',
            'multiline_help' => 'Permitir múltiplas linhas de texto (textarea)',
            'placeholder' => 'Placeholder',
            'placeholder_example' => 'Digite o texto do placeholder...',
        ],
    ],
    'number' => [
        'label' => 'Número',
        'settings' => [
            'min' => 'Valor Mínimo',
            'max' => 'Valor Máximo',
            'step' => 'Incremento',
            'prefix' => 'Prefixo',
            'suffix' => 'Sufixo',
        ],
    ],
    'single_choice' => [
        'label' => 'Escolha Única',
        'select_placeholder' => 'Selecione uma opção...',
        'settings' => [
            'layout' => 'Layout',
            'layout_radio' => 'Botões de Rádio',
            'layout_dropdown' => 'Dropdown',
            'choices' => 'Opções',
            'choice_value' => 'Valor',
            'choice_label' => 'Rótulo',
            'add_choice' => 'Adicionar Opção',
        ],
    ],
    'multiple_choice' => [
        'label' => 'Múltipla Escolha',
        'select_between' => 'Selecione entre :min e :max opções',
        'select_min' => 'Selecione pelo menos :min opções',
        'select_max' => 'Selecione até :max opções',
        'settings' => [
            'min_selections' => 'Mínimo de Seleções',
            'max_selections' => 'Máximo de Seleções',
            'no_limit' => 'Sem limite',
            'choices' => 'Opções',
            'choice_value' => 'Valor',
            'choice_label' => 'Rótulo',
            'add_choice' => 'Adicionar Opção',
        ],
    ],
    'file_upload' => [
        'label' => 'Upload de Arquivo',
        'click_to_upload' => 'Clique para enviar',
        'or_drag' => 'ou arraste e solte',
        'max_size_hint' => 'Máximo :size',
        'allowed_hint' => 'Permitidos: :extensions',
        'max_files_hint' => 'Até :count arquivos',
        'settings' => [
            'max_size_kb' => 'Tamanho Máximo do Arquivo',
            'max_size_help' => '5120 KB = 5 MB',
            'max_files' => 'Máximo de Arquivos',
            'allowed_extensions' => 'Extensões Permitidas',
            'extensions_placeholder' => 'Adicionar extensão...',
            'extensions_help' => 'Deixe vazio para permitir todos os tipos',
        ],
    ],
];
