<?php

namespace Modules\Production\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Production\Enums\JobStatus;
use Modules\Production\Models\ProductionJob;

class ProductionJobResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        /** @var ProductionJob $job */
        $job = $this->resource;

        return [
            'id' => $job->id,
            'order_id' => $job->order_id,
            'assigned_installer_id' => $job->assigned_installer_id,
            'title' => $job->title,
            'description' => $job->description,
            'status' => $job->status instanceof JobStatus
                ? $job->status->value
                : $job->status,
            'created_at' => $job->created_at?->toISOString(),
            'updated_at' => $job->updated_at?->toISOString(),
        ];
    }
}
