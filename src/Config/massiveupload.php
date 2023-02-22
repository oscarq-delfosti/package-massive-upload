<?php

return [
    'application' => [
        'architecture' => '',
        'orchestrator' => '',
        'microservices' => []
    ],
    'functionalities' => [
        [
            'action' => 'create',
            'type' => 'create',
            'friendly_name' => 'Create',
            'entities' => [
                [
                    'entity' => 'ParentEntity',
                    'order' => 1,
                    'type' => 'parent',
                    'finders' => [
                        [
                            'entity' => 'Entity',
                            'search_by' => '',
                            'fk_column' => ''
                        ]
                    ]
                ],
                [
                    'entity' => 'ChildEntity',
                    'order' => 2,
                    'type' => 'child',
                    'finders' => [
                        [
                            'entity' => 'Entity',
                            'search_by' => '',
                            'fk_column' => ''
                        ]
                    ]
                ]
            ]
        ],
        [
            'action' => 'update',
            'type' => 'update',
            'friendly_name' => 'Update',
            'entities' => [
                [
                    'entity' => 'ParentEntity',
                    'order' => 1,
                    'type' => 'parent',
                    'search_by (required)' => '',
                    'finders' => [
                        [
                            'entity' => 'Entity',
                            'search_by' => '',
                            'fk_column' => ''
                        ]
                    ]
                ]
            ]
        ],
        [
            'action' => 'delete',
            'type' => 'delete',
            'friendly_name' => 'Delete',
            'entities' => [
                [
                    'entity' => 'ParentEntity',
                    'order' => 1,
                    'type' => 'parent',
                    'search_by (required)' => '',
                    'finders' => [
                        [
                            'entity' => 'Entity',
                            'search_by' => '',
                            'fk_column' => ''
                        ]
                    ]
                ]
            ]
        ],
    ],

];
