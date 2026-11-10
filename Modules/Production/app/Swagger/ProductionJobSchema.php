<?php

namespace Modules\Production\Swagger;

use OpenApi\Annotations as OA;

/**
 * Handle production job API requests.
 *
 * @OA\Schema(
 *     schema="ProductionJob",
 *     type="object",
 *     required={
 *         "id",
 *         "order_id",
 *         "assigned_installer_id",
 *         "title",
 *         "description",
 *         "status"
 *     },
 *     @OA\Property(property="id", type="integer", example=10),
 *     @OA\Property(property="order_id", type="integer", example=3),
 *     @OA\Property(
 *         property="assigned_installer_id",
 *         type="integer",
 *         nullable=true,
 *         example=5
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         example="Kitchen base cabinets"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         nullable=true,
 *         example="Produce and install the kitchen base cabinets."
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={
 *             "cutting",
 *             "edge_banding",
 *             "assembly",
 *             "ready_for_installation",
 *             "installation",
 *             "completed"
 *         },
 *         example="cutting"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         nullable=true
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ProductionJobResponse",
 *     type="object",
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         example="success"
 *     ),
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="data",
 *         ref="#/components/schemas/ProductionJob"
 *     )
 * )
 *
 */
class ProductionJobSchema
{

}
