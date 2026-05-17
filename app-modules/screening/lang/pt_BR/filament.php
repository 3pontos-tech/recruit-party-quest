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
        'sections' => [
            'question' => [
                'title' => 'Pergunta',
                'description' => 'O texto que o candidato verá no formulário de candidatura.',
            ],
            'answer' => [
                'title' => 'Como é respondida',
                'description' => 'Configure as opções de resposta para o tipo de pergunta escolhido.',
            ],
            'knockout' => [
                'title' => 'Filtro eliminatório (opcional)',
                'description' => 'Quando ativado, a resposta pode reprovar ou avançar o candidato automaticamente, se a vaga estiver com a triagem automática ligada.',
                'unsupported' => 'Este tipo de pergunta não suporta filtro eliminatório.',
            ],
        ],
        'fields' => [
            'question_text' => 'Texto da Pergunta',
            'question_text_placeholder' => 'ex.: Você possui CNH válida?',
            'question_type' => 'Tipo de Pergunta',
            'question_type_help' => 'Define como o candidato responde: sim/não, número, escolha única ou múltipla.',
            'display_order' => 'Ordem de Exibição',
            'choices' => 'Opções',
            'choice_value' => 'Valor',
            'choice_label' => 'Rótulo',
            'is_required' => 'Obrigatória',
            'is_required_help' => 'O candidato não consegue enviar a candidatura sem responder esta pergunta.',
            'is_knockout' => 'Usar esta resposta como filtro eliminatório',
            'is_knockout_help' => 'Quando ligado, defina abaixo qual resposta aprova o candidato. Uma resposta reprovada pode reprovar o candidato automaticamente.',
            'knockout_criteria' => 'Critérios de Eliminação',
            'knockout_expected' => 'Aprovar o candidato se a resposta for',
            'knockout_operator' => 'Aprovar se o número for',
            'knockout_value' => 'Valor de referência',
            'knockout_accepted' => 'Respostas que aprovam',
            'knockout_accepted_multi_help' => 'O candidato passa se marcar pelo menos uma destas.',
            'knockout_accepted_edit_warning' => 'Se você renomear ou remover uma opção, revise este critério — referências desatualizadas são ignoradas na triagem dos candidatos.',
            'responses_count' => 'Respostas',
        ],
    ],
];
