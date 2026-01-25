<?php

declare(strict_types=1);

return [
    'resource' => [
        'label' => 'Candidatura',
        'plural_label' => 'Candidaturas',
        'navigation_label' => 'Candidaturas',
    ],
    'sections' => [
        'application_info' => 'Informações da Candidatura',
        'cover_letter' => 'Carta de Apresentação',
        'rejection' => 'Detalhes da Rejeição',
        'offer' => 'Detalhes da Proposta',
    ],
    'fields' => [
        'id' => 'ID',
        'tracking_code' => 'Código de Rastreamento',
        'requisition' => 'Requisição de Vaga',
        'candidate' => 'Candidato',
        'pipeline_progress' => 'Progresso no Pipeline',
        'status' => 'Status',
        'source' => 'Origem',
        'source_details' => 'Detalhes da Origem',
        'current_stage' => 'Etapa Atual',
        'cover_letter' => 'Carta de Apresentação',
        'evaluations_count' => 'Avaliações',
        'rejected_at' => 'Rejeitado em',
        'rejected_by' => 'Rejeitado por',
        'rejection_reason_category' => 'Categoria da Rejeição',
        'rejection_reason_details' => 'Detalhes da Rejeição',
        'offer_extended_at' => 'Proposta Enviada em',
        'offer_extended_by' => 'Proposta Enviada por',
        'offer_amount' => 'Valor da Proposta',
        'offer_response_deadline' => 'Prazo de Resposta',
        'created_at' => 'Criado em',
        'updated_at' => 'Atualizado em',
        'transition_notes' => 'Notas',
    ],
    'filters' => [
        'status' => 'Status',
        'source' => 'Origem',
        'requisition' => 'Requisição de Vaga',
        'current_stage' => 'Etapa Atual',
    ],
    'actions' => [
        'workflow' => [
            'label' => 'Ações',
        ],
        'bulk_move_to_stage' => [
            'label' => 'Mover para Etapa',
            'modal_heading' => 'Mover Candidaturas para Etapa',
            'modal_submit' => 'Mover Candidaturas',
            'notification' => 'Movidas :count candidaturas para nova etapa.',
            'fields' => [
                'stage' => 'Etapa',
                'notes' => 'Notas',
            ],
        ],
        'bulk_reject' => [
            'label' => 'Rejeitar',
            'modal_heading' => 'Rejeitar Candidaturas',
            'modal_description' => 'Tem certeza que deseja rejeitar estas candidaturas? Esta ação não pode ser desfeita.',
            'modal_submit' => 'Rejeitar Candidaturas',
            'notification' => 'Rejeitadas :count candidaturas.',
            'fields' => [
                'reason_category' => 'Motivo da Rejeição',
                'reason_details' => 'Detalhes da Rejeição',
            ],
        ],
        'extend_offer' => [
            'label' => 'Estender Proposta',
            'modal_heading' => 'Estender Proposta',
            'modal_description' => 'Enviar uma proposta de emprego para este candidato.',
            'modal_submit' => 'Enviar Proposta',
            'notification' => 'Proposta enviada ao candidato.',
            'fields' => [
                'offer_amount' => 'Valor da Proposta',
                'response_deadline' => 'Prazo de Resposta (dias)',
            ],
        ],
        'mark_hired' => [
            'label' => 'Marcar como Contratado',
            'modal_heading' => 'Marcar Candidatura como Contratada',
            'modal_description' => 'Marcar esta candidatura como contratada. O status do candidato será atualizado.',
            'modal_submit' => 'Marcar como Contratado',
            'notification' => 'Candidatura marcada como contratada.',
        ],
        'move_to_stage' => [
            'label' => 'Mover para Etapa',
            'modal_heading' => 'Mover Candidatura para Etapa',
            'modal_description' => 'Mover esta candidatura para uma etapa diferente no pipeline de contratação.',
            'modal_submit' => 'Mover para Etapa',
            'notification' => 'Candidatura movida para nova etapa.',
            'fields' => [
                'stage' => 'Etapa',
                'notes' => 'Notas',
            ],
        ],
        'reject' => [
            'label' => 'Rejeitar',
            'modal_heading' => 'Rejeitar Candidatura',
            'modal_description' => 'Tem certeza que deseja rejeitar esta candidatura? Esta ação não pode ser desfeita.',
            'modal_submit' => 'Rejeitar Candidatura',
            'notification' => 'Candidatura rejeitada.',
        ],
        'withdraw' => [
            'label' => 'Retirar',
            'modal_heading' => 'Retirar Candidatura',
            'modal_description' => 'Tem certeza que deseja retirar esta candidatura? O candidato será notificado.',
            'modal_submit' => 'Retirar Candidatura',
            'notification' => 'Candidatura retirada.',
        ],
        'fields' => [
            'stage' => 'Etapa',
            'notes' => 'Notas',
            'reason_category' => 'Motivo da Rejeição',
            'reason_details' => 'Detalhes da Rejeição',
            'offer_amount' => 'Valor da Proposta',
            'response_deadline' => 'Prazo de Resposta (dias)',
        ],
        'change_status' => [
            'label' => 'Alterar status',
            'modal_heading' => 'Alterar status da candidatura',
            'modal_submit' => 'Confirmar',
            'no_transitions_tooltip' => 'Nenhuma transição de status disponível',
            'notifications' => [
                'updated' => [
                    'title' => 'Status atualizado',
                ],
            ],
        ],
    ],
];
