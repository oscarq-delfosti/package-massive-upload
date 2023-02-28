<?php

return [
    'application' => [
        'architecture' => 'monolith',
        'orchestrator' => '',
        'microservices' => []
    ],
    'actions' => [
        [
            'action' => 'register_clients',
            'type' => 'create',
            'friendly_name' => 'Registrar clientes',
            'entities' => [
                [
                    'entity' => '',
                    'order' => 1,
                    'type' => 'parent',
                    'created' => 'user_id',
                    'foreign_keys' => [
                        'in_flow' => [
                            'Entity' => 'field'
                        ],
                        'out_flow' => [
                            [
                                'entity' => 'Client',
                                'search_by' => 'ruc',
                                'fk_column' => 'client_id'
                            ]
                        ]
                    ],
                    'fields' => [],
                    'validations' => [
                        'create' => [],
                        'update' => [],
                        'delete' => []
                    ]
                ],
            ]
        ],
    ],

];
