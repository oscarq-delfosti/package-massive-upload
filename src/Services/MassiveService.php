<?php

namespace Delfosti\Massive\Services;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use ErrorException;

use Delfosti\Massive\Services\GeneralService;
use Delfosti\Massive\Services\ModelService;

class MassiveService
{

    private $generalService;
    private $modelService;

    public function __construct()
    {
        $this->generalService = new GeneralService();
        $this->modelService = new ModelService();
    }

    public function create($args)
    {
        $data = [
            'confirmed' => [],
            'invalid' => [],
            'failed' => []
        ];

        $entity = $this->generalService->capitalizeEntity($args['entity']);
        $validateModel = $this->modelService->validateModel($entity, 'create');

        if (!$validateModel->status) {
            return $this->generalService->processResponse(null, $validateModel->message, false, 400);
        }

        $items = $args['items'];
        $model = $this->modelService->getModel($entity);
        $fields = $this->modelService->getFields($entity);

        $fieldsToRemove = [
            'created_at',
            'updated_at'
        ];

        $this->generalService->removeItemsFromArray($fields, $fieldsToRemove);

        foreach ($items as $key => $item) {

            $validate = Validator::make($item, $model->validations['create']);

            // Items that failed validation
            if ($validate->fails()) {

                $item['wrong_row'] = $key += 2;
                $item['errors'] = $validate->errors();
                array_push($data['invalid'], $item);

            } else {

                try {

                    $this->modelService->filterFieldsByFillable($item, $fields);
                    $model::create($item);
                    array_push($data['confirmed'], $item);

                } catch (ErrorException $ex) {

                    $item['wrong_row'] = $key += 2;
                    $item['errors'][] = [
                        'code' => $ex->getCode(),
                        'message' => $ex->getMessage()
                    ];

                    array_push($data['failed'], $item);

                } catch (QueryException $ex) {

                    $item['wrong_row'] = $key += 2;
                    $item['errors'][] = [
                        'code' => $ex->getCode(),
                        'message' => $ex->getPrevious()->getMessage()
                    ];

                    array_push($data['failed'], $item);

                }

            }
        }

        return $this->generalService->processResponse($data);

    }

    public function update($args)
    {

        $data = [
            'confirmed' => [],
            'invalid' => [],
            'failed' => []
        ];

        $entity = $this->generalService->capitalizeEntity($args['entity']);
        $validateModel = $this->modelService->validateModel($entity, 'update');

        if (!$validateModel->status) {
            return $this->generalService->processResponse(null, $validateModel->message, false, 400);
        }

        $items = $args['items'];
        $model = $this->modelService->getModel($entity);
        $fields = $this->modelService->getFields($entity);

        $fieldsToRemove = [
            'created_at',
            'updated_at'
        ];

        $this->generalService->removeItemsFromArray($fields, $fieldsToRemove);

        foreach ($items as $key => $item) {

            $validate = Validator::make($item, $model->validations['update']);

            // Items that failed validation
            if ($validate->fails()) {

                $item['wrong_row'] = $key += 2;
                $item['errors'] = $validate->errors();
                array_push($data['invalid'], $item);

            } else {

                try {

                    $itemToUpdate = $model::find($item['id']);

                    $this->modelService->filterFieldsByFillable($item, $fields);

                    foreach ($item as $key => $it) {
                        $itemToUpdate->$key = $item[$key];
                    }

                    $itemToUpdate->save();

                    array_push($data['confirmed'], $item);

                } catch (ErrorException $ex) {

                    $item['wrong_row'] = $key += 2;
                    $item['errors'][] = [
                        'code' => $ex->getCode(),
                        'message' => $ex->getMessage()
                    ];

                    array_push($data['failed'], $item);

                } catch (QueryException $ex) {

                    $item['wrong_row'] = $key += 2;
                    $item['errors'][] = [
                        'code' => $ex->getCode(),
                        'message' => $ex->getPrevious()->getMessage()
                    ];

                    array_push($data['failed'], $item);

                }

            }
        }

        return $this->generalService->processResponse($data);

    }

    public function delete($args)
    {

        $data = [
            'confirmed' => [],
            'invalid' => [],
            'failed' => []
        ];

        $entity = $this->generalService->capitalizeEntity($args['entity']);
        $validateModel = $this->modelService->validateModel($entity, 'delete');

        if (!$validateModel->status) {
            return $this->generalService->processResponse(null, $validateModel->message, false, 400);
        }

        $items = $args['items'];
        $model = $this->modelService->getModel($entity);
        $fields = $this->modelService->getFields($entity);

        $fieldsToRemove = [
            'created_at',
            'updated_at'
        ];

        $this->generalService->removeItemsFromArray($fields, $fieldsToRemove);

        foreach ($items as $key => $item) {

            $validate = Validator::make($item, $model->validations['delete']);

            // Items that failed validation
            if ($validate->fails()) {

                $item['wrong_row'] = $key += 2;
                $item['errors'] = $validate->errors();
                array_push($data['invalid'], $item);

            } else {

                try {

                    $itemToDelete = $model::find($item['id']);
                    $itemToDelete->delete();
                    array_push($data['confirmed'], $item);

                } catch (ErrorException $ex) {

                    $item['wrong_row'] = $key += 2;
                    $item['errors'][] = [
                        'code' => $ex->getCode(),
                        'message' => $ex->getMessage()
                    ];

                    array_push($data['failed'], $item);

                } catch (QueryException $ex) {

                    $item['wrong_row'] = $key += 2;
                    $item['errors'][] = [
                        'code' => $ex->getCode(),
                        'message' => $ex->getPrevious()->getMessage()
                    ];

                    array_push($data['failed'], $item);

                }

            }
        }

        return $this->generalService->processResponse($data);

    }

}
