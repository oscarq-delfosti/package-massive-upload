<?php

namespace Delfosti\Massive\Services;

use Throwable;

class ModelService
{
    private $generalService;
    const PATH = '\App\Models\\';
    const MODELS_FOLDER_PATH = '..\..\App\Models\\';

    public function __construct()
    {
        $this->generalService = new GeneralService();
    }

    public function getModels()
    {
        $models = [];

        $files = scandir(dirname(__FILE__) . '\\' . self::MODELS_FOLDER_PATH);

        foreach ($files as $file) {
            $fileName = explode('.', $file);

            if ($fileName[1] == "php") {
                $models[$fileName[0]] = $this->getModel($fileName[0]);
            }
        }

        return $models;
    }

    public function getModel(string $model_name, string $property = "massiveUpload")
    {
        try {
            $model = self::getPath($model_name);
            return get_object_vars(new $model)[$property];
        } catch (Throwable $exception) {
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
            return null;
        }
    }

    public function filterFieldsByFillable(&$fields, $fillable)
    {
        foreach ($fields as $key => $field) {
            if (!in_array($key, $fillable)) {
                unset($fields[$key]);
            }
        }
    }
}
