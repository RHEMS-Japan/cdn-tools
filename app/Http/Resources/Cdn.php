<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Cdn extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'cdnType' => $this->dnType,
            'accountName' => $this->ccountName,
            'estimatedSeconds' => $this->stimatedSeconds,
            'progressUri' => $this->progressUri,
            'purgeId' => $this->purgeId,
            'supportId' => $this->supportId,
            'httpStatus' => $this->httpStatus,
            'detail' => $this->detail,
            'pingAfterSeconds' => $this->pingAfterSeconds,
            'done' => $this->done,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
