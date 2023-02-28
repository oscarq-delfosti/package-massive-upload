<?php

namespace Delfosti\Massive\Services;

use ErrorException;

class ModelService
{

    const PATH = '\App\Models\\';
    const MODELS_FOLDER_PATH = '..\..\App\Models\\';

    public function getModels()
    {
        $models = [];

        $files = scandir(dirname(__FILE__) . '\\' . self::MODELS_FOLDER_PATH);

        foreach ($files as $file) {
            $fileName = explode('.', $file);

            if ($fileName[1] == "php") {
                $models[$fileName[0]] = $this->getModelProperty($fileName[0]);
            }
        }

        return $models;
    }

    public function getModel(string $modelName)
    {
        return self::getPath($modelName);
    }

    public function getModelProperty(string $modelName, $property = "massiveUpload")
    {
        try {

            $model = self::getPath($modelName);
            return get_object_vars(new $model)[$property];

        } catch (ErrorException $ex) {
            return [];
        }
    }

    public function getPath(string $modelName, string $siteId = null)
    {
        if ($siteId) {
            return self::PATH . $siteId . '\\' . $modelName;
        } else {
            return self::PATH . $modelName;
        }
    }

    public function getTable($model)
    {
        return $model['table_name'];
    }

    public function getFields($model)
    {
        return $model['fields'];
    }

    public function getValidations($model, $action)
    {
        return $model['validations'][$action];
    }

}
