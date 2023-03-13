<?php

namespace Delfosti\Massive\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MassiveUploadLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'action' => $this->action,
            'friendly_name' => $this->friendly_name,
            'entities' => json_decode($this->entities),
            'upload_status' => $this->upload_status,
            'file_name' => $this->file_name,
            'user' => json_decode($this->user),
            'create_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s')
        ];
    }
}
