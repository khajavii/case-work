<?php

namespace Modules\Orders\Swagger\Schema;

/**
 * Handle order API requests.
 *
 * @OA\Schema(
 *     schema="Order",
 *     type="object",
 *     required={
 *         "id",
 *         "owner_id",
 *         "customer_name",
 *         "address",
 *         "status"
 *     },
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="owner_id", type="integer", example=2),
 *     @OA\Property(
 *         property="customer_name",
 *         type="string",
 *         example="Oliver Anderson"
 *     ),
 *     @OA\Property(
 *         property="address",
 *         type="string",
 *         example="12 Oak Street, Springfield"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"pending", "in_progress", "completed"},
 *         example="pending"
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
 */
class OrderSchema
{

}
