<?php

namespace Delfosti\Massive\Services;

use Illuminate\Support\Facades\Validator;

class PackageConfigurationValidationService
{
    private $configFile;
    private $internalFails = false;
    private $internalErrors = [];

    public function __construct()
    {
        $this->configFile = config('massiveupload');
    }

    public function validate(): PackageConfigurationValidationResponse
    {

        self::validateApplication();

        return new PackageConfigurationValidationResponse(
            $this->internalFails,
            $this->internalErrors
        );
    }

    public function validateApplication()
    {

        $file = $this->configFile;

        if (!$file) {

            $this->internalFails = true;
            $this->internalErrors["massiveupload"] = "Configuration file does not exist";

        } else {

            $validate = Validator::make($this->configFile, [
                'application' => 'required|array',
                'application.architecture' => 'required|in:monolith,microservices',
                'application.orchestrator' => 'required_if:application.architecture,microservices',
                'application.microservices' => 'required_if:application.architecture,microservices',
                'actions' => 'required|array',
                'actions.*.action' => 'required',
                'actions.*.type' => 'required|in:create,update,delete',
                'actions.*.friendly_name' => 'required',
                'actions.*.entities' => 'required|array',
                'actions.*.entities.*.entity' => 'required',
                'actions.*.entities.*.order' => 'required|numeric',
                'actions.*.entities.*.type' => 'required|in:parent,child',
                'actions.*.entities.*.search_by' => 'required_if:actions.*.type,update,delete'
            ]);

            if ($validate->fails()) {
                $this->internalFails = true;
                $this->internalErrors = $validate->errors();
            }
        }
    }

    public function validateAction($action)
    {

        $validate = Validator::make($action, [
            'action' => 'required',
            'type' => 'required|in:create,update,delete',
            'friendly_name' => 'required',
            'entities' => 'required|array',
            'entities.*.entity' => 'required',
            'entities.*.order' => 'required|numeric',
            'entities.*.type' => 'required|in:parent,type',
            'entities.*.search_by' => 'required_if:type,update,delete',
            'entities.*.audit_dates' => 'array',
            'entities.*.delete_options' => 'array',
            'entities.*.delete_options.type' => 'in:physically,logically',
            'entities.*.delete_options.fields' => 'array|required_if:entities.*.delete_options.type,logically',
            'entities.*.foreign_keys' => 'array',
            'entities.*.foreign_keys.in_flow' => 'array',
            'entities.*.foreign_keys.out_flow' => 'array',
            'entities.*.fields' => 'array',
            'entities.*.validations' => 'array',
            'entities.*.validations.create' => 'array',
            'entities.*.validations.update' => 'array',
            'entities.*.validations.delete' => 'array',
        ]);

        if ($validate->fails()) {
            $this->internalFails = true;
            $this->internalErrors = $validate->errors();
        }

        return new PackageConfigurationValidationResponse(
            $this->internalFails,
            $this->internalErrors
        );
    }

}

class PackageConfigurationValidationResponse
{
    private $internalFails = false;
    private $internalErrors = [];

    public function __construct($fails, $errors)
    {
        $this->internalFails = $fails;
        $this->internalErrors = $errors;
    }

    public function fails()
    {
        return $this->internalFails;
    }

    public function errors()
    {
        return [
            'message' => 'Error in the package configuration file',
            'errors' => $this->internalErrors
        ];
    }
}
