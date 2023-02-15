<?php

namespace Delfosti\Massive\Services;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use ErrorException;
use Exception;
use Throwable;

// Services
use Delfosti\Massive\Services\GeneralService;
use Delfosti\Massive\Services\ModelService;
use Delfosti\Massive\Services\DatabaseService;
use Delfosti\Massive\Services\ConfigurationService;
use Delfosti\Massive\Services\ApiService;
use Delfosti\Massive\Services\MassiveUploadLogService;

class MassiveService
{

    private $generalService;
    private $modelService;
    private $configurationService;
    private $databaseService;
    private $massiveUploadLogService;

    public function __construct()
    {
        $this->generalService = new GeneralService();
        $this->modelService = new ModelService();
        $this->configurationService = new ConfigurationService();
        $this->databaseService = new DatabaseService();
        $this->massiveUploadLogService = new MassiveUploadLogService();
    }

    public function getFunctionalities($args)
    {
        $functionality = $args['functionality'] ?? null;
        $fields = $args['fields'] ?? false;

        $response = $this->configurationService->getFunctionalities($functionality);

        if ($fields && $functionality) {
            $models = $this->getModelsGlobally($args);


            foreach ($response['entities'] as $entity) {
                if (array_key_exists($entity['entity'], $models)) {
                    $index = array_search($entity, array_column($response['entities'], 'entity'));
                    dd($response['entities'], $index);

                    dd($index);
                    $response['entities'][$entity]['fields'] = $models[$entity]['fields'];
                }
            }

        }

        return $response;
    }

    public function getModels($args)
    {
        $response = $this->modelService->getModels();
        return $response;
    }

    public function getModelsGlobally($args)
    {
        $architecture = $this->configurationService->getApplicationArchitecture();

        if ($architecture == 'monolith') {
            return $this->modelService->getModels();
        }

        if ($architecture == 'microservices') {

            $models = [];

            $orchestrator = $this->configurationService->getOrchestrator();
            $microservices = $this->configurationService->getMicroservices();

            // Getting orchestrator models
            $responseModelsOrchestrator = [];

            if ($orchestrator == $args['domain']) {
                $responseModelsOrchestrator = $this->modelService->getModels();
            } else {
                $response = (new ApiService($orchestrator))->get('api', 'massive', 'get-models');
                $responseModelsOrchestrator = $response['data'];
            }

            foreach ($responseModelsOrchestrator as $key => $model) {
                $models[$key] = $model;
            }

            // Obtaining models of the microservices
            foreach ($microservices as $microservice) {

                $responseModelsMicroservice = [];

                if ($microservice == $args['domain']) {
                    $responseModelsMicroservice = $this->modelService->getModels();
                } else {
                    $response = (new ApiService($microservice))->get('api', 'massive', 'get-models');
                    $responseModelsMicroservice = $response['data'];
                }

                foreach ($responseModelsMicroservice as $key => $model) {
                    $models[$key] = $model;
                }
            }

            return $models;
        }

    }

    public function getFunctionalityGlobally($args)
    {
        $architecture = $this->configurationService->getApplicationArchitecture();
        $orchestrator = $this->configurationService->getOrchestrator();

        $functionality = $args['action'] ?? null;

        if ($architecture == 'monolith') {
            return $this->configurationService->getFunctionalities($functionality);
        }

        if ($architecture == 'microservices') {
            if ($orchestrator == $args['domain']) {
                return $this->configurationService->getFunctionalities($functionality);
            } else {
                $response = (new ApiService($orchestrator))->get('api', 'massive', 'get-models');
                return $response['data'];
            }
        }

    }

    public function uploader($args)
    {
        try {

            $action = $this->getFunctionalityGlobally($args);

            $validateFunctionality = $this->configurationService->validateFunctionality($action);

            if ($validateFunctionality->fails) {
                throw new Exception(json_encode($validateFunctionality->errors));
            }

            $actionType = $action['type'];
            $actionEntities = $action['entities'];

            $response = $this->$actionType($args, $action);

            $logArgs = [
                'action' => $args['action'],
                'type' => $actionType,
                'entities' => json_encode($actionEntities),
                'upload_status' => 'complete',
                'items' => json_encode($args['items']),
                'user_id' => $args['user_id']
            ];

            $this->massiveUploadLogService->create($logArgs);

            return $response;
        } catch (Throwable $ex) {
            return [
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'line' => $ex->getLine()
            ];
        }

    }

    public function create($args, $action)
    {
        try {

            $models = $this->getModelsGlobally($args);
            $actionEntities = $action['entities'];

            $structureValidations = [];

            foreach ($actionEntities as $key => $actionEntity) {

                // Build structure validator
                $structureValidations[$actionEntity['entity']] = 'required|array';

                $entityConfiguration = (array) $models[$actionEntity['entity']];

                // Validate if the entity configuration is correct
                $validateEntConfig = $this->configurationService->validateEntityConfiguration($entityConfiguration, 'create');

                if ($validateEntConfig->fails) {
                    throw new Exception(json_encode($validateEntConfig->errors));
                }

                $actionEntities[$key]['massive_upload'] = $entityConfiguration;
            }

            $data = [
                'confirmed' => [],
                'failed' => []
            ];

            $this->generalService->sortAssociativeArray($actionEntities, "order");

            $parentIds = [];

            foreach ($args['items'] as $key => $item) {

                $validateStructure = Validator::make($item, $structureValidations);

                if ($validateStructure->fails()) {

                    $item['message'] = "The object does not have the correct structure";
                    $item['errors'] = $validateStructure->errors();
                    $data['failed'][] = $item;

                } else {
                    try {

                        $errors = 0;

                        DB::beginTransaction();

                        foreach ($actionEntities as $key => $entity) {

                            if ($entity['type'] == 'parent') {
                                $parentItem = $item[$entity['entity']][0];

                                foreach ($entity['finders'] as $finder) {
                                    $searchBy = $finder['search_by'];

                                    if (array_key_exists($searchBy, $parentItem) && $parentItem[$searchBy] != "") {
                                        // Find item by declared field
                                        $response = $this->databaseService->findByField(
                                                $models[$finder['entity']]->table_name,
                                            $searchBy,
                                            $parentItem[$searchBy]
                                        );

                                        if (!$response) {
                                            $item[$entity['entity']][0]['errors'][$finder['search_by']][] = "No item found with this value";
                                            $errors++;
                                        } else {
                                            // Change the search engine for the database field
                                            unset($parentItem[$searchBy]);
                                            $parentItem[$finder['fk_column']] = $response->id;
                                        }
                                    } else {
                                        $item[$entity['entity']][0]['errors'][$finder['search_by']][] = "The {$finder['search_by']} field is required";
                                        $errors++;
                                    }
                                }

                                $validations = (array) $entity['massive_upload']['validations']->create;
                                $validateParent = Validator::make($parentItem, $validations);

                                if ($validateParent->fails()) {
                                    $item[$entity['entity']][0]['message'] = "The object did not pass system validations";
                                    $item[$entity['entity']][0]['errors'] = $validateParent->errors();

                                    $errors++;
                                } else {
                                    // Remove from the object the fields that are not declared in the configuration
                                    $this->generalService->removeDiferentKeys(
                                        $entity['massive_upload']['fields'],
                                        $parentItem
                                    );

                                    if (array_key_exists('user_id', $parentItem)) {
                                        $parentItem['user_id'] = $args['user_id'];
                                    }

                                    $responseSaveParent = DB::table(
                                        $entity['massive_upload']['table_name']
                                    )->insertGetId($parentItem);

                                    if (!$responseSaveParent) {
                                        $errors++;
                                    } else {
                                        $parentIds[$entity['entity']] = $responseSaveParent;
                                    }
                                }
                            }

                            if ($entity['type'] == 'child') {
                                foreach ($item[$entity['entity']] as $keyChild => $child) {

                                    foreach ($entity['finders'] as $finder) {
                                        $searchBy = $finder['search_by'];

                                        if (array_key_exists($searchBy, $child) && $child[$searchBy] != "") {
                                            // Find item by declared field
                                            $response = $this->databaseService->findByField(
                                                    $models[$finder['entity']]->table_name,
                                                $searchBy,
                                                $child[$searchBy]
                                            );

                                            if (!$response) {
                                                $errors++;
                                            } else {
                                                // Change the search engine for the database field
                                                unset($child[$searchBy]);
                                                $child[$finder['fk_column']] = $response->id;
                                            }
                                        } else {
                                            $errors++;
                                        }
                                    }

                                    // Asign foreign keys
                                    if (!empty($entity['massive_upload']['foreign_keys'])) {
                                        foreach ($entity['massive_upload']['foreign_keys'] as $key => $foreignKey) {
                                            if (array_key_exists($key, $parentIds)) {
                                                $child[$foreignKey] = $parentIds[$key];
                                            } else {
                                                $item[$entity['entity']][$keyChild]['errors'][] = "Parent storage error";
                                            }
                                        }
                                    }

                                    $validations = (array) $entity['massive_upload']['validations']->create;
                                    $validateChild = Validator::make($child, $validations);

                                    if ($validateChild->fails()) {
                                        $item[$entity['entity']][$keyChild]['message'] = "The object did not pass system validations";
                                        $item[$entity['entity']][$keyChild]['errors'][] = $validateChild->errors();
                                    } else {

                                        // Remove from the object the fields that are not declared in the configuration
                                        $this->generalService->removeDiferentKeys(
                                            $entity['massive_upload']['fields'],
                                            $child
                                        );

                                        if (array_key_exists('user_id', $child)) {
                                            $child['user_id'] = $args['user_id'];
                                        }

                                        DB::table($entity['massive_upload']['table_name'])->insertGetId($child);
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
                        $data['failed'][$key] = $item;
                    } catch (QueryException $ex) {
                        DB::rollBack();
                        $item[$entity['entity']][0]['errors'] = [$ex->getMessage(), $ex->getPrevious()];
                        $data['failed'][$key] = $item;
                    }
                }
            }

            return $this->generalService->processResponse($data);

        } catch (Throwable $ex) {
            return [
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'line' => $ex->getLine()
            ];
        }
    }

    public function update($args, $action)
    {
        try {

            $models = $this->getModelsGlobally($args);
            $actionEntities = $action['entities'];

            $structureValidations = [];

            foreach ($actionEntities as $key => $actionEntity) {

                // Build structure validator
                $structureValidations[$actionEntity['entity']] = 'required|array';

                $entityConfiguration = (array) $models[$actionEntity['entity']];

                // Validate if the entity configuration is correct
                $validateEntConfig = $this->configurationService->validateEntityConfiguration($entityConfiguration, 'create');

                if ($validateEntConfig->fails) {
                    throw new Exception(json_encode($validateEntConfig->errors));
                }

                $actionEntities[$key]['massive_upload'] = $entityConfiguration;
            }

            $data = [
                'confirmed' => [],
                'failed' => []
            ];

            $this->generalService->sortAssociativeArray($actionEntities, "order");

            foreach ($args['items'] as $key => $item) {

                $validateStructure = Validator::make($item, $structureValidations);

                if ($validateStructure->fails()) {

                    $item['message'] = "The object does not have the correct structure";
                    $item['errors'] = $validateStructure->errors();
                    $data['failed'][$key] = $item;

                } else {
                    try {

                        $errors = 0;

                        DB::beginTransaction();

                        foreach ($actionEntities as $key => $entity) {

                            if ($entity['type'] == 'parent') {
                                $parentItem = $item[$entity['entity']][0];

                                foreach ($entity['finders'] as $finder) {
                                    $searchBy = $finder['search_by'];

                                    if (array_key_exists($searchBy, $parentItem) && $parentItem[$searchBy] != "") {
                                        // Find item by declared field
                                        $response = $this->databaseService->findByField(
                                                $models[$finder['entity']]->table_name,
                                            $searchBy,
                                            $parentItem[$searchBy]
                                        );

                                        if (!$response) {
                                            $item[$entity['entity']][0]['errors'][$finder['search_by']][] = "No item found with this value";
                                            $errors++;
                                        } else {
                                            // Change the search engine for the database field
                                            unset($parentItem[$searchBy]);
                                            $parentItem[$finder['fk_column']] = $response->id;
                                        }
                                    } else {
                                        $item[$entity['entity']][0]['errors'][$finder['search_by']][] = "The {$finder['search_by']} field is required";
                                        $errors++;
                                    }
                                }

                                $validations = (array) $entity['massive_upload']['validations']->create;
                                $validateParent = Validator::make($parentItem, $validations);

                                if ($validateParent->fails()) {
                                    $item[$entity['entity']][0]['message'] = "The object did not pass system validations";
                                    $item[$entity['entity']][0]['errors'] = $validateParent->errors();

                                    $errors++;
                                } else {

                                    $id = $parentItem[$entity['search_by']];

                                    // Remove from the object the fields that are not declared in the configuration
                                    $this->generalService->removeDiferentKeys(
                                        $entity['massive_upload']['fields'],
                                        $parentItem
                                    );

                                    // Temporarily save and collect parent id
                                    DB::table(
                                        $entity['massive_upload']['table_name']
                                    )
                                        ->where($entity['search_by'], $id)
                                        ->update($parentItem);

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
                        $data['failed'][$key] = $item;
                    } catch (QueryException $ex) {
                        DB::rollBack();
                        $item[$entity['entity']][0]['errors'] = [$ex->getMessage(), $ex->getPrevious()];
                        $data['failed'][$key] = $item;
                    }
                }
            }

            return $this->generalService->processResponse($data);

        } catch (Throwable $ex) {
            return [
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'line' => $ex->getLine()
            ];
        }
    }

    public function delete($args, $action)
    {
        try {

            $models = $this->getModelsGlobally($args);
            $actionEntities = $action['entities'];

            $structureValidations = [];

            foreach ($actionEntities as $key => $actionEntity) {

                // Build structure validator
                $structureValidations[$actionEntity['entity']] = 'required|array';

                $entityConfiguration = (array) $models[$actionEntity['entity']];

                // Validate if the entity configuration is correct
                $validateEntConfig = $this->configurationService->validateEntityConfiguration($entityConfiguration, 'delete');

                if ($validateEntConfig->fails) {
                    throw new Exception(json_encode($validateEntConfig->errors));
                }

                $actionEntities[$key]['massive_upload'] = $entityConfiguration;
            }

            $data = [
                'confirmed' => [],
                'failed' => []
            ];

            $this->generalService->sortAssociativeArray($actionEntities, "order");

            foreach ($args['items'] as $key => $item) {

                $validateStructure = Validator::make($item, $structureValidations);

                if ($validateStructure->fails()) {

                    $item['message'] = "The object does not have the correct structure";
                    $item['errors'] = $validateStructure->errors();
                    $data['failed'][$key] = $item;

                } else {
                    try {

                        $errors = 0;

                        DB::beginTransaction();

                        foreach ($actionEntities as $key => $entity) {

                            if ($entity['type'] == 'parent') {
                                $parentItem = $item[$entity['entity']][0];

                                $validations = (array) $entity['massive_upload']['validations']->delete;
                                $validateParent = Validator::make($parentItem, $validations);

                                if ($validateParent->fails()) {
                                    $item[$entity['entity']][0]['message'] = "The object did not pass system validations";
                                    $item[$entity['entity']][0]['errors'] = $validateParent->errors();

                                    $errors++;
                                } else {

                                    // Temporarily delete
                                    DB::table(
                                        $entity['massive_upload']['table_name']
                                    )
                                        ->where($entity['search_by'], $parentItem[$entity['search_by']])
                                        ->update([
                                            'deleted_at' => now()
                                        ]);

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
                        $data['failed'][$key] = $item;
                    } catch (QueryException $ex) {
                        DB::rollBack();
                        $item[$entity['entity']][0]['errors'] = [$ex->getMessage(), $ex->getPrevious()];
                        $data['failed'][$key] = $item;
                    }
                }
            }

            return $this->generalService->processResponse($data);

        } catch (Throwable $ex) {
            return [
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'line' => $ex->getLine()
            ];
        }
    }
}
