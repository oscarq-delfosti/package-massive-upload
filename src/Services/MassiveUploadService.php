<?php

namespace Delfosti\Massive\Services;

use ErrorException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use Error;
use Exception;

// Services
use Delfosti\Massive\Services\PackageConfigurationService;
use Delfosti\Massive\Services\ModelService;
use Delfosti\Massive\Services\ApiService;
use Delfosti\Massive\Services\GeneralService;
use Delfosti\Massive\Services\DatabaseService;
use Delfosti\Massive\Services\MassiveUploadLogService;

class MassiveUploadService
{

    private $packageConfigurationService;
    private $modelService;
    private $generalService;
    private $databaseService;
    private $massiveUploadLogService;

    public function __construct()
    {
        $this->packageConfigurationService = new PackageConfigurationService();
        $this->modelService = new ModelService();
        $this->generalService = new GeneralService();
        $this->databaseService = new DatabaseService();
        $this->massiveUploadLogService = new MassiveUploadLogService();
    }

    public function getActions()
    {
        return $this->packageConfigurationService->getActions();
    }

    public function getAction($args)
    {

        // Params
        $pAction = $args["action"];
        $pEntityFields = $args["entity_fields"] ?? false;

        $action = $this->packageConfigurationService->getAction($pAction);

        // Get entity fields
        if ($pEntityFields) {
            $models = $this->getModelsGlobally($args["domain"]);

            foreach ($action['entities'] as $key => $entity) {
                if (!array_key_exists('fields', $entity)) {

                    // Find model
                    $existsModel = array_key_exists($entity['entity'], $models);

                    if (!$existsModel) {
                        $fields = [];
                        $required_fields = [];
                    } else {
                        $model = $models[$entity['entity']];
                        $actionType = $action['type'];

                        foreach ($model->validations->$actionType as $field => $validation) {
                            if (str_contains($validation, 'required'))
                                $required_fields[] = $field;
                        }

                        $fields = $this->modelService->getFields($model);
                    }

                    $action['entities'][$key]['fields'] = $fields;
                    $action['entities'][$key]['required_fields'] = $required_fields;

                    $required_fields = [];
                }
            }
        }

        return $action;
    }

    public function getModels()
    {
        $response = $this->modelService->getModels();
        return $response;
    }

    public function getModelsGlobally($domain)
    {
        $architecture = $this->packageConfigurationService->getArchitecture();

        if ($architecture == 'monolith') {
            return $this->modelService->getModels();
        }

        if ($architecture == 'microservices') {

            $models = [];

            $orchestrator = $this->packageConfigurationService->getOrchestrator();
            $microservices = $this->packageConfigurationService->getMicroservices();

            // Getting orchestrator models
            $responseModelsOrchestrator = [];

            if ($orchestrator == $domain) {
                $responseModelsOrchestrator = $this->modelService->getModels();
            } else {
                $response = (new ApiService($orchestrator))->get('api', 'massive-upload', 'get-models');
                $responseModelsOrchestrator = $response['data'];
            }

            foreach ($responseModelsOrchestrator as $key => $model) {
                $models[$key] = $model;
            }

            // Obtaining models of the microservices
            foreach ($microservices as $microservice) {

                $responseModelsMicroservice = [];

                if ($microservice == $domain) {
                    $responseModelsMicroservice = $this->modelService->getModels();
                } else {
                    $response = (new ApiService($microservice))->get('api', 'massive-upload', 'get-models');
                    $responseModelsMicroservice = $response['data']->data;
                }

                foreach ($responseModelsMicroservice as $key => $model) {
                    $models[$key] = $model;
                }
            }

            return $models;
        }
    }

    public function getActionGlobally($action, $domain)
    {
        $architecture = $this->packageConfigurationService->getArchitecture();
        $orchestrator = $this->packageConfigurationService->getOrchestrator();

        if ($architecture == 'monolith') {
            return $this->packageConfigurationService->getAction($action);
        }

        if ($architecture == 'microservices') {
            if ($orchestrator == $domain) {
                return $this->packageConfigurationService->getAction($action);
            } else {
                $response = (new ApiService($orchestrator))->get(
                    'api',
                    'massive',
                    'get-action',
                    null,
                    null,
                    ['action' => $action]
                );
                return $response['data'];
            }
        }
    }

    public function buildStructureValidations($action)
    {
        $data = [];
        $entities = $action['entities'];

        foreach ($entities as $entity) {
            $data[$entity['entity']] = 'required|array';
        }

        return $data;
    }

    public function hasForeignKeysInFlow($entity)
    {
        if (array_key_exists('foreign_keys', $entity)) {
            if (array_key_exists('in_flow', $entity['foreign_keys'])) {
                if (!empty($entity['foreign_keys']['in_flow'])) {
                    return true;
                }
            }
        }

        return false;
    }

    public function hasForeignKeysOutFlow($entity)
    {
        if (array_key_exists('foreign_keys', $entity)) {
            if (array_key_exists('out_flow', $entity['foreign_keys'])) {
                if (!empty($entity['foreign_keys']['out_flow'])) {
                    return true;
                }
            }
        }

        return false;
    }

    public function hasCustomPackageValidations($entity, $action)
    {
        if (array_key_exists('validations', $entity)) {
            if (array_key_exists($action, $entity['validations'])) {
                if (!empty($entity['validations'][$action])) {
                    return true;
                }
            }
        }

        return false;
    }

    public function hasCustomPackageFields($entity)
    {
        if (array_key_exists('fields', $entity)) {
            if (!empty($entity['fields'])) {
                return true;
            }
        }

        return false;
    }

    public function hasCustomPackageCreatedField($entity)
    {
        if (array_key_exists('created', $entity)) {
            if ($entity['created'] != '') {
                return true;
            }
        }

        return false;
    }

    public function hasCustomAuditDates($entity)
    {
        if (array_key_exists('audit_dates', $entity)) {
            if (!empty($entity['audit_dates'])) {
                return true;
            }
        }

        return false;
    }

    public function hasIdInEntity($entity)
    {
        if (array_key_exists('has_id', $entity)) {
            if ($entity['has_id'] === true) {
                return true;
            }

            if ($entity['has_id'] === false) {
                return false;
            }

            return true;
        }

        return true;
    }

    public function hasCustomDeleteOptions($entity)
    {
        if (array_key_exists('delete_options', $entity)) {
            if (array_key_exists('type', $entity['delete_options']) && array_key_exists('fields', $entity['delete_options'])) {
                return true;
            }
        }

        return false;
    }

    public function uploader($args)
    {
        $action = $this->getActionGlobally($args['action'], $args['domain']);

        $actionFriendlyName = $action['friendly_name'];
        $actionType = $action['type'];
        $actionEntities = $action['entities'];

        $response = $this->$actionType($args, $action);

        $logArgs = [
            'action' => $args['action'],
            'friendly_name' => $actionFriendlyName,
            'type' => $actionType,
            'entities' => json_encode($actionEntities),
            'file_name' => $args['file_name'],
            'upload_status' => 'complete',
            'user_id' => $args['user']
        ];

        $this->massiveUploadLogService->create($logArgs);

        return $response;
    }

    public function create($args, $action)
    {
        // Validate action before process data
        $validate = (new PackageConfigurationValidationService())->validateAction($action);

        if ($validate->fails()) {
            return $validate->errors();
        }

        // Package data
        $models = $this->getModelsGlobally($args["domain"]);
        $entities = $action['entities'];

        // Response data
        $data = [
            'confirmed' => [],
            'failed' => []
        ];

        $this->generalService->sortAssociativeArray($entities, "order");

        $parentIds = [];

        foreach ($args['items'] as $key => $item) {

            $errors = 0;

            // Validate structure of item
            $vStructure = Validator::make($item, $this->buildStructureValidations($action));

            if ($vStructure->fails()) {

                $item['message'] = "The object does not have the correct structure";
                $item['errors'] = $vStructure->errors();

                $errors += 1;

            } else {
                try {
                    DB::beginTransaction();

                    foreach ($entities as $entityKey => $entity) {

                        if ($entity['type'] == 'parent') {

                            $parent = $item[$entity['entity']][0];

                            // Foreign keys

                            // In flow => Get the value of the foreign keys that are inside the stream
                            if ($this->hasForeignKeysInFlow($entity)) {
                                foreach ($entity['foreign_keys']['in_flow'] as $inFlowKey => $inFlowItem) {
                                    if (array_key_exists($inFlowKey, $parentIds)) {

                                        $parent[$inFlowItem] = $parentIds[$inFlowKey];

                                    } else {

                                        $item[$entity['entity']][0]['errors'][] = "Parent storage error";
                                        $errors += 1;

                                    }
                                }
                            }

                            // Out flow => Get value of foreign keys that are outside the stream
                            if ($this->hasForeignKeysOutFlow($entity)) {
                                foreach ($entity['foreign_keys']['out_flow'] as $outFlowItem) {
                                    $searchBy = $outFlowItem['search_by'];
                                    $fkColumn = $outFlowItem['fk_column'];

                                    if (array_key_exists($fkColumn, $parent) && $parent[$fkColumn] != "") {

                                        // Find item by declared field
                                        $response = $this->databaseService->findByField(
                                            $this->modelService->getTable($models[$outFlowItem['entity']]),
                                            $searchBy,
                                            $parent[$fkColumn]
                                        );

                                        if (!$response) {

                                            $item[$entity['entity']][0]['errors'][$fkColumn][] = "No item found with this value";
                                            $errors += 1;

                                        } else {

                                            // Change the search engine for the database field
                                            $parent[$fkColumn] = $response->id;

                                        }
                                    } else {

                                        $item[$entity['entity']][0]['errors'][$outFlowItem['search_by']][] = "The {$outFlowItem['search_by']} field is required";
                                        $errors += 1;

                                    }
                                }
                            }

                            // Validate if entity configuration has custom validations
                            if ($this->hasCustomPackageValidations($entity, 'create')) {
                                $pValidations = (array) $entity['validations']['create'];
                            } else {
                                $pValidations = $this->modelService->getValidations($models[$entity['entity']], 'create');
                            }

                            // Apply validations to the entity
                            $vParent = Validator::make($parent, $pValidations);

                            if ($vParent->fails()) {

                                $item[$entity['entity']][0]['message'] = "The object did not pass system validations";
                                $item[$entity['entity']][0]['errors'] = $vParent->errors();

                                $errors += 1;

                            } else {

                                // If the element passes the validations

                                // Validate if entity configuration has custom validations
                                if ($this->hasCustomPackageFields($entity)) {
                                    $fields = (array) $entity['fields'];
                                } else {
                                    $fields = $this->modelService->getFields($models[$entity['entity']]);
                                }

                                // Remove from the object the fields that are not declared in the configuration
                                $this->generalService->removeDiferentKeys($fields, $parent);

                                if ($this->hasCustomPackageCreatedField($entity)) {
                                    $parent[$entity['created']] = $args['user'];
                                } else {
                                    if (array_key_exists('user_id', $parent)) {
                                        $parent['user_id'] = $args['user'];
                                    }
                                }

                                if ($this->hasCustomAuditDates($entity)) {
                                    foreach ($entity['audit_dates'] as $auditDate) {
                                        $parent[$auditDate] = now();
                                    }
                                }

                                $response = DB::table($this->modelService->getTable($models[$entity['entity']]))->insertGetId($parent);

                                if (!$response) {
                                    $errors += 1;
                                } else {
                                    $parentIds[$entity['entity']] = $response;
                                }
                            }

                        }

                        if ($entity['type'] == 'child') {
                            foreach ($item[$entity['entity']] as $keyChild => $child) {

                                // Foreign keys

                                // In flow => Get the value of the foreign keys that are inside the stream
                                if ($this->hasForeignKeysInFlow($entity)) {
                                    foreach ($entity['foreign_keys']['in_flow'] as $inFlowKey => $inFlowItem) {
                                        if (array_key_exists($inFlowKey, $parentIds)) {
                                            $child[$inFlowItem] = $parentIds[$inFlowKey];
                                        } else {
                                            $item[$entity['entity']][$keyChild]['errors'][] = "Parent storage error";
                                        }
                                    }
                                }

                                // Out flow => Get value of foreign keys that are outside the stream
                                if ($this->hasForeignKeysOutFlow($entity)) {
                                    foreach ($entity['foreign_keys']['out_flow'] as $outFlowItem) {
                                        $searchBy = $outFlowItem['search_by'];
                                        $fkColumn = $outFlowItem['fk_column'];

                                        if (array_key_exists($fkColumn, $child) && $child[$fkColumn] != "") {
                                            // Find item by declared field
                                            $response = $this->databaseService->findByField(
                                                $this->modelService->getTable($models[$outFlowItem['entity']]),
                                                $searchBy,
                                                $child[$fkColumn]
                                            );

                                            if (!$response) {

                                                $item[$entity['entity']][$keyChild]['errors'][$fkColumn][] = "No item found with this value";
                                                $errors += 1;

                                            } else {

                                                // Change the search engine for the database field
                                                $child[$fkColumn] = $response->id;

                                            }
                                        } else {

                                            $item[$entity['entity']][$keyChild]['errors'][$outFlowItem['search_by']][] = "The {$outFlowItem['search_by']} field is required";
                                            $errors += 1;
                                        }
                                    }
                                }

                                // Validate if entity configuration has custom validations
                                if ($this->hasCustomPackageValidations($entity, 'create')) {
                                    $pValidations = (array) $entity['validations']['create'];
                                } else {
                                    $pValidations = $this->modelService->getValidations($models[$entity['entity']], 'create');
                                }

                                // Apply validations to the entity
                                $vChild = Validator::make($child, $pValidations);

                                if ($vChild->fails()) {

                                    $item[$entity['entity']][$keyChild]['message'] = "The object did not pass system validations";
                                    $item[$entity['entity']][$keyChild]['errors'][] = $vChild->errors();

                                    $errors += 1;

                                } else {

                                    // If the element passes the validations

                                    // Validate if entity configuration has custom validations
                                    if ($this->hasCustomPackageFields($entity)) {
                                        $fields = (array) $entity['fields'];
                                    } else {
                                        $fields = $this->modelService->getFields($models[$entity['entity']]);
                                    }

                                    // Remove from the object the fields that are not declared in the configuration
                                    $this->generalService->removeDiferentKeys($fields, $child);

                                    if ($this->hasCustomPackageCreatedField($entity)) {
                                        $child[$entity['created']] = $args['user'];
                                    } else {
                                        if (array_key_exists('user_id', $child)) {
                                            $child['user_id'] = $args['user'];
                                        }
                                    }

                                    if ($this->hasCustomAuditDates($entity)) {
                                        foreach ($entity['audit_dates'] as $auditDate) {
                                            $child[$auditDate] = now();
                                        }
                                    }

                                    if ($this->hasIdInEntity($entity)) {
                                        $response = DB::table($this->modelService->getTable($models[$entity['entity']]))->insertGetId($child);

                                        if (!$response) {
                                            $errors += 1;
                                        } else {
                                            $parentIds[$entity['entity']] = $response;
                                        }
                                    } else {
                                        $response = DB::table($this->modelService->getTable($models[$entity['entity']]))->insert($child);

                                        if (!$response) {
                                            $errors += 1;
                                        }
                                    }

                                }
                            }
                        }

                    }

                    if ($errors > 0) {
                        DB::rollBack();
                        $data['failed'][] = $item;
                    } else {
                        DB::commit();
                        $data['confirmed'][] = $item;
                    }

                    $errors = 0;

                } catch (ErrorException $ex) {
                    DB::rollBack();
                    $item[$entity['entity']][0]['errors'] = [$ex->getMessage(), $ex->getSeverity()];
                    $data['failed'][] = $item;
                } catch (QueryException $ex) {
                    DB::rollBack();
                    $item[$entity['entity']][0]['errors'] = [$ex->getMessage(), $ex->getPrevious()];
                    $data['failed'][] = $item;
                }
            }
        }

        return $data;
    }

    public function update($args, $action)
    {
        // Validate action before process data
        $validate = (new PackageConfigurationValidationService())->validateAction($action);

        if ($validate->fails()) {
            return $validate->errors();
        }

        // Package data
        $models = $this->getModelsGlobally($args["domain"]);
        $entities = $action['entities'];

        // Response data
        $data = [
            'confirmed' => [],
            'failed' => []
        ];

        $this->generalService->sortAssociativeArray($entities, "order");

        foreach ($args['items'] as $key => $item) {

            $errors = 0;

            // Validate structure of item
            $vStructure = Validator::make($item, $this->buildStructureValidations($action));

            if ($vStructure->fails()) {

                $item['message'] = "The object does not have the correct structure";
                $item['errors'] = $vStructure->errors();

                $errors += 1;

            } else {
                try {
                    DB::beginTransaction();

                    foreach ($entities as $key => $entity) {

                        if ($entity['type'] == 'parent') {

                            $parent = $item[$entity['entity']][0];

                            // Foreign keys

                            // Out flow => Get value of foreign keys that are outside the stream
                            if ($this->hasForeignKeysOutFlow($entity)) {
                                foreach ($entity['foreign_keys']['out_flow'] as $outFlowItem) {
                                    $searchBy = $outFlowItem['search_by'];
                                    $fkColumn = $outFlowItem['fk_column'];

                                    if (array_key_exists($fkColumn, $parent) && $parent[$fkColumn] != "") {

                                        // Find item by declared field
                                        $response = $this->databaseService->findByField(
                                            $this->modelService->getTable($models[$outFlowItem['entity']]),
                                            $searchBy,
                                            $parent[$fkColumn]
                                        );

                                        if (!$response) {

                                            $item[$entity['entity']][0]['errors'][$fkColumn][] = "No item found with this value";
                                            $errors += 1;

                                        } else {

                                            // Change the search engine for the database field
                                            $parent[$fkColumn] = $response->id;

                                        }
                                    } else {

                                        $item[$entity['entity']][0]['errors'][$outFlowItem['search_by']][] = "The {$outFlowItem['search_by']} field is required";
                                        $errors += 1;

                                    }
                                }
                            }

                            // Validate if entity configuration has custom validations
                            if ($this->hasCustomPackageValidations($entity, 'update')) {
                                $pValidations = $entity['validations']['update'];
                            } else {
                                $pValidations = $this->modelService->getValidations($models[$entity['entity']], 'update');
                            }

                            // Apply validations to the entity
                            $vParent = Validator::make($parent, $pValidations);

                            if ($vParent->fails()) {

                                $item[$entity['entity']][0]['message'] = "The object did not pass system validations";
                                $item[$entity['entity']][0]['errors'] = $vParent->errors();

                                $errors += 1;

                            } else {

                                // If the element passes the validations

                                $id = $parent[$entity['search_by']];

                                // Validate if entity configuration has custom validations
                                if ($this->hasCustomPackageFields($entity)) {
                                    $fields = $entity['fields'];
                                } else {
                                    $fields = $models[$entity['entity']]['fields'];
                                }

                                // Remove from the object the fields that are not declared in the configuration
                                $this->generalService->removeDiferentKeys($fields, $parent);

                                $response = DB::table($this->modelService->getTable($models[$entity['entity']]))
                                    ->where($entity['search_by'], $id)
                                    ->update($parent);

                                if (!$response) {
                                    $errors += 1;
                                }
                            }
                        }
                    }

                    if ($errors > 0) {
                        DB::rollBack();
                        $data['failed'][] = $item;
                    } else {
                        DB::commit();
                        $data['confirmed'][] = $item;
                    }

                    $errors = 0;

                } catch (ErrorException $ex) {
                    DB::rollBack();
                    $item[$entity['entity']][0]['errors'] = [$ex->getMessage(), $ex->getSeverity()];
                    $data['failed'][] = $item;
                } catch (QueryException $ex) {
                    DB::rollBack();
                    $item[$entity['entity']][0]['errors'] = [$ex->getMessage(), $ex->getPrevious()];
                    $data['failed'][] = $item;
                }
            }
        }

        return $data;
    }

    public function delete($args, $action)
    {
        // Validate action before process data
        $validate = (new PackageConfigurationValidationService())->validateAction($action);

        if ($validate->fails()) {
            return $validate->errors();
        }

        // Package data
        $models = $this->getModelsGlobally($args["domain"]);
        $entities = $action['entities'];

        // Response data
        $data = [
            'confirmed' => [],
            'failed' => []
        ];

        $this->generalService->sortAssociativeArray($entities, "order");

        foreach ($args['items'] as $key => $item) {

            $errors = 0;

            // Validate structure of item
            $vStructure = Validator::make($item, $this->buildStructureValidations($action));

            if ($vStructure->fails()) {

                $item['message'] = "The object does not have the correct structure";
                $item['errors'] = $vStructure->errors();

                $errors += 1;

            } else {
                try {
                    DB::beginTransaction();

                    foreach ($entities as $key => $entity) {

                        if ($entity['type'] == 'parent') {

                            $parent = $item[$entity['entity']][0];

                            // Validate if entity configuration has custom validations
                            if ($this->hasCustomPackageValidations($entity, 'delete')) {
                                $pValidations = $entity['validations']['delete'];
                            } else {
                                $pValidations = $this->modelService->getValidations($models[$entity['entity']], 'delete');
                            }

                            // Apply validations to the entity
                            $vParent = Validator::make($parent, $pValidations);

                            if ($vParent->fails()) {

                                $item[$entity['entity']][0]['message'] = "The object did not pass system validations";
                                $item[$entity['entity']][0]['errors'] = $vParent->errors();

                                $errors += 1;

                            } else {

                                // If the element passes the validations

                                $id = $parent[$entity['search_by']];

                                if ($this->hasCustomDeleteOptions($entity)) {
                                    if ($entity['delete_options']['type'] == "physically") {
                                        DB::table($this->modelService->getTable($models[$entity['entity']]))
                                            ->where($entity['search_by'], $id)
                                            ->delete();
                                    }

                                    if ($entity['delete_options']['type'] == "logically") {
                                        DB::table($this->modelService->getTable($models[$entity['entity']]))
                                            ->where($entity['search_by'], $id)
                                            ->update($entity['delete_options']['fields']);
                                    }
                                } else {
                                    DB::table($this->modelService->getTable($models[$entity['entity']]))
                                        ->where($entity['search_by'], $id)
                                        ->delete();
                                }
                            }
                        }
                    }

                    if ($errors > 0) {
                        DB::rollBack();
                        $data['failed'][] = $item;
                    } else {
                        DB::commit();
                        $data['confirmed'][] = $item;
                    }

                    $errors = 0;

                } catch (ErrorException $ex) {
                    DB::rollBack();
                    $item[$entity['entity']][0]['errors'] = [$ex->getMessage(), $ex->getSeverity()];
                    $data['failed'][] = $item;
                } catch (QueryException $ex) {
                    DB::rollBack();
                    $item[$entity['entity']][0]['errors'] = [$ex->getMessage(), $ex->getPrevious()];
                    $data['failed'][] = $item;
                }
            }
        }

        return $data;
    }

}
