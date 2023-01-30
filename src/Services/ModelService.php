<?php

namespace Delfosti\Massive\Services;

use Throwable;

class ModelService
{
    private $generalService;

    const PATH = '\App\Models\\';

    public function __construct()
    {
        $this->generalService = new GeneralService();
    }

    public function getModel(string $model_name, string $site_id = null)
    {
        try {
            $model = self::getPath($model_name, $site_id);
            return new $model;
        } catch (Throwable $exception) {
            $this->generalService->logDebug($exception);
            return null;
        }
    }

    public function getPath(string $model_name, string $site_id = null)
    {
        try {
            if ($site_id) {
                return self::PATH . $site_id . '\\' . $model_name;
            } else {
                return self::PATH . $model_name;
            }
        } catch (Throwable $exception) {
            $this->generalService->logDebug($exception);
            return null;
        }
    }

    public function getFields(string $model_name, string $site_id = null)
    {
        $model = $this->getModel($model_name, $site_id);
        $fields = $model->getFillable();
        return $fields;
    }

    public function filterFieldsByFillable(&$fields, $fillable)
    {
        foreach ($fields as $key => $field) {
            if (!in_array($key, $fillable)) {
                unset($fields[$key]);
            }
        }
    }

    public function hasValidations($model, $action)
    {
        $response = [
            'status' => true,
            'message' => ''
        ];

        if (!$model->validations) {
            $response['status'] = false;
            $response['message'] = "The entity has no validations.";
        }

        if ($model->validations && !array_key_exists($action, $model->validations)) {
            $response['status'] = false;
            $response['message'] = "The entity validations do not contain the action you want to perform.";
        }

        if (empty($model->validations[$action])) {
            $response['status'] = false;
            $response['message'] = "The entity validations do not contain the action you want to perform.";
        }

        if (($action == 'update' || 'delete') && !array_key_exists('id', $model->validations)) {
            $response['status'] = false;
            $response['message'] = "Id validation is required for this action, declare it in your model.";
        }

        return (object) $response;
    }

    public function validateModel($entity, $action)
    {

        $response = [
            'status' => true,
            'message' => ''
        ];

        $model = $this->getModel($entity);

        if (!$model) {
            $response['status'] = false;
            $response['message'] = "The entity does not exist";
        }

        $fields = $this->getFields($entity);

        if (empty($fields)) {
            $response['status'] = false;
            $response['message'] = "Fillable is empty";
        }

        $hasValidations = $this->hasValidations($model, $action);
        if (!$hasValidations->status) {
            $response['status'] = false;
            $response['message'] = $hasValidations->message;
        }

        return (object) $response;
    }
}
