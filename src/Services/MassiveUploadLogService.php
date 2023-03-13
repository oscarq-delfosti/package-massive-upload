<?php

namespace Delfosti\Massive\Services;

use Illuminate\Support\Facades\DB;

use Delfosti\Massive\Models\MassiveUploadLog;
use Delfosti\Massive\Resources\MassiveUploadLogResource;

use Delfosti\Massive\Services\GeneralService;
use Delfosti\Massive\Services\PackageConfigurationService;

class MassiveUploadLogService
{

    private $generalService;
    private $packageConfigurationService;

    public function __construct()
    {
        $this->generalService = new GeneralService();
        $this->packageConfigurationService = new PackageConfigurationService();
    }

    public function show($args)
    {
        $query = MassiveUploadLog::query();

        // Filters
        if (!empty($args['id']))
            $query->where('id', $args['id']);
        if (isset($args['status']) && $args['status'] !== '' && $args['status'] !== null)
            $query->where('status', $args['status']);

        $output = $query->first();

        if ($output === null) {
            return null;
        }

        return new MassiveUploadLogResource($output);
    }

    public function get($args)
    {
        $query = MassiveUploadLog::query();

        // Filters
        $this->filters($query, $args);

        // Process quantity
        if (isset($args['offset']))
            $query->skip($args['offset']);
        if (!empty($args['limit']))
            $query->take($args['limit']);

        $output = $query->get();

        // Validate output
        if ($output === null) {
            return null;
        }

        return MassiveUploadLogResource::collection($output);
    }

    public function list($args)
    {
        // Pagination
        $limit = (isset($args['limit']) && $args['limit'] !== '' && $args['limit'] !== NULL) ? $args['limit'] : 0;

        if (!$limit) {
            return null;
        }

        $query = MassiveUploadLog::query();

        // Filters
        $this->filters($query, $args);

        $data = $query->paginate(
            $args["limit"],
            $columns = ['*'],
            $pageName = 'logs',
            $args["page"]
        );

        return MassiveUploadLogResource::collection($data);
    }

    public function create($args)
    {
        $massiveUploadLog = new MassiveUploadLog();

        $massiveUploadLog->action = $args["action"];
        $massiveUploadLog->friendly_name = $args["friendly_name"];
        $massiveUploadLog->type = $args["type"];
        $massiveUploadLog->entities = $args["entities"];
        $massiveUploadLog->file_name = $args["file_name"];
        $massiveUploadLog->upload_status = $args["upload_status"];
        $massiveUploadLog->user_id = $args["user_id"];

        $massiveUploadLog->save();
    }

    private function filters(&$query, $args)
    {
        // Filter default
        if (!empty($args['id']))
            $query->whereIn('id', is_array($args['id']) ? $args['id'] : explode(',', $args['id']));
        if (!empty($args['action']))
            $query->where('action', $args['action']);

        // Get created data from configuration file
        $createdData = $this->packageConfigurationService->getCreatedData();
        $fieldsToGet = "'id', {$createdData['table']}.{$createdData['primary_key']}";

        foreach ($createdData['fields'] as $key => $value) {
            $fieldsToGet .= ",'{$value}',{$createdData['table']}.{$value}";
        }

        // Get fields
        $query->select(
            'massive_upload_log.id',
            'massive_upload_log.action',
            'massive_upload_log.friendly_name',
            'massive_upload_log.entities',
            'massive_upload_log.upload_status',
            'massive_upload_log.file_name',
            'massive_upload_log.user_id',
            'massive_upload_log.created_at',
            'massive_upload_log.updated_at',

            DB::raw("JSON_OBJECT({$fieldsToGet}) as user")
        )
            ->join(
                "{$createdData['table']}",
                "{$createdData['table']}.{$createdData['primary_key']}",
                '=',
                'massive_upload_log.user_id'
            );

        // Filter date
        $this->generalService->filterDate($query, $args);

        // Order
        $order_field = !empty($args['order_field']) ? $args['order_field'] : 'updated_at';
        $order_sort = !empty($args['order_sort']) ? $args['order_sort'] : 'desc';
        $query->orderBy($order_field, $order_sort);

    }

}
