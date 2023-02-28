<?php

namespace Delfosti\Massive\Services;

use Exception;

class PackageConfigurationService
{
    private $configFile;

    public function __construct()
    {
        $this->configFile = config('massiveupload');
    }

    public function getArchitecture()
    {
        return $this->configFile['application']['architecture'];
    }

    public function getOrchestrator()
    {
        return $this->configFile['application']['orchestrator'];
    }

    public function getMicroservices()
    {
        return $this->configFile['application']['microservices'];
    }

    public function getActions()
    {
        return $this->configFile['actions'];
    }

    public function getAction($action)
    {
        $actions = $this->configFile['actions'];

        $funcionalityIndex = array_search($action, array_column($actions, 'action'));

        if ($funcionalityIndex === false) {
            throw new Exception("The action you are looking for does not exist", 404);
        }

        return $actions[$funcionalityIndex];
    }

}
