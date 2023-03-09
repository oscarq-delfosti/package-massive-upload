<?php

namespace Delfosti\Massive\Services;

use Delfosti\Massive\Models\MassiveUploadLog;
use Delfosti\Massive\Resources\MassiveUploadLogResource;

use Delfosti\Massive\Services\GeneralService;

class MassiveUploadLogService
{

    private $generalService;

    public function __construct()
    {
        $this->generalService = new GeneralService();
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

        // Defined fields
        if (!empty($args['fields'])) {
            $query->select(explode(',', $args['fields']));
        }

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
        $massiveUploadLog->type = $args["type"];
        $massiveUploadLog->entities = $args["entities"];
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

        // Filter date
        $this->generalService->filterDate($query, $args);

        // Order
        $order_field = !empty($args['order_field']) ? $args['order_field'] : 'updated_at';
        $order_sort = !empty($args['order_sort']) ? $args['order_sort'] : 'desc';
        $query->orderBy($order_field, $order_sort);

    }

}
