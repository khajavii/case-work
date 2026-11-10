<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
  * @OA\Schema(
 *     schema="Pagination",
 *     type="object",
 *     @OA\Property(
 *         property="current_page",
 *         type="integer",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="per_page",
 *         type="integer",
 *         example=15
 *     ),
 *     @OA\Property(
 *         property="last_page",
 *         type="integer",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="total",
 *         type="integer",
 *         example=4
 *     )
 * )
 */
class PaginationSchema
{

}
