<?php

namespace Delfosti\Massive\Services;

class ConfigurationService
{

    private $massiveUploadConfiguation;

    public function __construct()
    {
        $this->massiveUploadConfiguation = config('massiveupload');
    }

    public function defaultStructureConfiguration()
    {

        return [
            'application' => [
                'architecture' => 'microservices|monolith',
                'orchestrator' => '[URI]',
                'microservices' => [
                    '[URI]',
                ]
            ],
            'functionalities' => [
                [
                    'action' => '',
                    'type' => 'create|update|delete',
                    'friendly_name' => '',
                    'entities' => [
                        [
                            'entity' => '',
                            'order' => 1,
                            'type' => 'parent|child',
                            'finders (optional)' => [
                                [
                                    'entity' => '',
                                    'search_by' => '',
                                    'fk_column' => ''
                                ]
                            ]
                        ],

                    ]
                ],
            ]
        ];

    }

    public function defaultEntityConfiguration()
    {
        return [
            'table_name' => '',
            'fields' => [],
            'foreign_keys (optional)' => [
                '[Entity]' => '[field]',
            ],
            'validations' => [
                'create' => [],
                'update' => [],
                'delete' => []
            ]
        ];
    }

    public function validateEntityConfiguration($entity, $action)
    {

        $data = [
            'fails' => false
        ];

        if (!$entity) {
            $data['errors']['entity'][] = "Entity's configuration is empty";
            $data['fails'] = true;
        }

        if ($entity && (!array_key_exists("table_name", $entity) || $entity["table_name"] == "")) {
            $data['errors']['table_name'][] = "The table name is required";
            $data['fails'] = true;
        }

        if ($entity && (!array_key_exists("fields", $entity) || empty($entity["fields"]))) {
            $data['errors']['fields'][] = "The fields that will be used in the bulk upload are required";
            $data['fails'] = true;
        }

        if (
            $entity &&
            (
                !array_key_exists("validations", $entity) ||
                empty($entity["validations"]) ||
                !property_exists($entity['validations'], $action) ||
                empty($entity['validations']->{$action})
            )
        ) {
            $data['errors']['validations'][] = "Validations for this action are required";
            $data['fails'] = true;
        }

        return (object) $data;
    }

    public function validateFunctionality($functionality)
    {
        $data = [
            'fails' => false
        ];

        if (!$functionality) {
            $data['errors'][] = "Action does not exists";
            $data['fails'] = true;
        }

        if ($functionality && !array_key_exists('type', $functionality)) {
            $data['errors'][] = "Action does not have type";
            $data['fails'] = true;
        }

        $functionalityTypes = ['create', 'update', 'delete'];

        if ($functionality && !in_array($functionality['type'], $functionalityTypes)) {
            $data['errors'][] = "The type of action is not valid";
            $data['fails'] = true;
        }

        if (
            $functionality && (
                !array_key_exists('entities', $functionality) ||
                empty($functionality['entities'])
            )
        ) {
            $data['errors'][] = "The action has no registered entities";
            $data['fails'] = true;
        }

        return (object) $data;
    }

    public function getApplicationArchitecture()
    {
        return $this->massiveUploadConfiguation['application']['architecture'];
    }

    public function getOrchestrator()
    {
        return $this->massiveUploadConfiguation['application']['orchestrator'];
    }

    public function getMicroservices()
    {
        return $this->massiveUploadConfiguation['application']['microservices'];
    }

    public function getFunctionalities($functionality = null)
    {

        $data = null;

        $functionalities = $this->massiveUploadConfiguation['functionalities'];

        $data = $functionalities;

        if ($functionality) {
            $funcionalityIndex = array_search($functionality, array_column($functionalities, 'action'));

            if ($funcionalityIndex === false) {
                return null;
            }

            $data = $functionalities[$funcionalityIndex];
        }

        return $data;
    }

}
