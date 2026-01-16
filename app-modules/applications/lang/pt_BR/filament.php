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
    ],
    'filters' => [
        'status' => 'Status',
        'source' => 'Origem',
        'requisition' => 'Requisição de Vaga',
        'current_stage' => 'Etapa Atual',
    ],
];
