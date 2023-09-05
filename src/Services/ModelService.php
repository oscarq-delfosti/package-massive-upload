<?php

namespace Delfosti\Massive\Services;

use ErrorException;

class ModelService
{

    private $DS = DIRECTORY_SEPARATOR;
    private $PATH = '';
    private $MODELS_FOLDER_PATH = '';

    public function __construct()
    {
        $this->PATH = "{$this->DS}App{$this->DS}Models{$this->DS}{$this->DS}";
        $this->MODELS_FOLDER_PATH = "..{$this->DS}..{$this->DS}..{$this->DS}..{$this->DS}..{$this->DS}App{$this->DS}Models{$this->DS}{$this->DS}";
    }

    public function getModels()
    {
        $models = [];

        $files = scandir(dirname(__FILE__) . $this->DS . $this->MODELS_FOLDER_PATH);

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
            return $this->PATH . $siteId . $this->DS . $this->DS . $modelName;
        } else {
            return $this->PATH . $modelName;
        }
    }

    public function getTable($model)
    {
        if (is_array($model)) {
            if (array_key_exists('table_name', $model)) {
                return (string) $model['table_name'];
            } else {
                return "";
            }
        }

        if (is_object($model)) {
            if (property_exists($model, 'table_name')) {
                return (string) $model->table_name;
            } else {
                return "";
            }
        }

        return "";
    }

    public function getFields($model)
    {
        if (is_array($model)) {
            if (array_key_exists('fields', $model)) {
                return (array) $model['fields'];
            } else {
                return [];
            }
        }

        if (is_object($model)) {
            if (property_exists($model, 'fields')) {
                return (array) $model->fields;
            } else {
                return [];
            }
        }

        return [];
    }

    public function getValidations($model, $action)
    {
        if (is_array($model)) {
            if (array_key_exists('validations', $model)) {
                if (is_array($model['validations'])) {
                    if (array_key_exists($action, $model['validations'])) {
                        return (array) $model['validations'][$action];
                    } else {
                        return [];
                    }
                }
                if (is_object($model['validations'])) {
                    if (property_exists($model['validations'], $action)) {
                        return (array) $model['validations']->$action;
                    } else {
                        return [];
                    }
                }
            } else {
                return [];
            }
        }

        if (is_object($model)) {
            if (property_exists($model, 'validations')) {
                if (is_array($model->validations)) {
                    if (array_key_exists($action, $model->validations)) {
                        return (array) $model->validations[$action];
                    } else {
                        return [];
                    }
                }
                if (is_object($model->validations)) {
                    if (property_exists($model->validations, $action)) {
                        return (array) $model->validations->$action;
                    } else {
                        return [];
                    }
                }
            } else {
                return [];
            }
        }

        return [];
    }
}
