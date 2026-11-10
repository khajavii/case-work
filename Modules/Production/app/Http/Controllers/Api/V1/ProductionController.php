<?php

namespace Modules\Production\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\Controllers\ApiJsonResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Production\app\Actions\ChangeProductionJobStatusAction;
use Modules\Production\app\Services\ProductionJobService;
use Modules\Production\Enums\JobStatus;
use Modules\Production\Http\Requests\ChangeProductionJobStatusRequest;
use Modules\Production\Http\Requests\IndexProductionJobRequest;
use Modules\Production\Http\Requests\StoreProductionJobRequest;
use Modules\Production\Transformers\ProductionJobResource;
use OpenApi\Annotations as OA;

class ProductionController extends Controller
{
    use ApiJsonResponse;

    public function __construct(
        private readonly ProductionJobService $jobService
    ) {
    }

    /**
     * List jobs accessible to the authenticated user.
     *
     * @OA\Get(
     *     path="/api/v1/jobs",
     *     operationId="listProductionJobs",
     *     tags={"Production"},
     *     summary="List accessible production jobs",
     *     description="Admins and planners see jobs under their own orders. Installers see only jobs assigned to them.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={
     *                 "cutting",
     *                 "edge_banding",
     *                 "assembly",
     *                 "ready_for_installation",
     *                 "installation",
     *                 "completed"
     *             }
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="order_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             minimum=1,
     *             maximum=100,
     *             default=15
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Jobs retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="result",
     *                     type="array",
     *                     @OA\Items(
     *                         ref="#/components/schemas/ProductionJob"
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="pagination",
     *                     ref="#/components/schemas/Pagination"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated."
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="The user is not allowed to list jobs."
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid filters or pagination parameters."
     *     )
     * )
     */
    public function index(IndexProductionJobRequest $request): JsonResponse
    {
        $jobs = $this->jobService->paginate(
            $request->user(),
            $request->validated()
        );

        return $this->success([
            'items' => ProductionJobResource::collection(
                $jobs->getCollection()
            )->resolve($request),
            'pagination' => [
                'current_page' => $jobs->currentPage(),
                'per_page' => $jobs->perPage(),
                'last_page' => $jobs->lastPage(),
                'total' => $jobs->total(),
            ],
        ]);
    }


    /**
     * Create a job under an owned order.
     *
     * @OA\Post(
     *     path="/api/v1/jobs",
     *     operationId="createProductionJob",
     *     tags={"Production"},
     *     summary="Create a production job",
     *     description="Only admins and planners may create jobs under their own orders. The initial status is always cutting. An optional assignee must have the installer role.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"order_id", "title"},
     *             @OA\Property(
     *                 property="order_id",
     *                 type="integer",
     *                 minimum=1,
     *                 example=3
     *             ),
     *             @OA\Property(
     *                 property="title",
     *                 type="string",
     *                 maxLength=200,
     *                 example="Kitchen base cabinets"
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 maxLength=10000,
     *                 nullable=true,
     *                 example="Produce and install the kitchen base cabinets."
     *             ),
     *             @OA\Property(
     *                 property="assigned_installer_id",
     *                 type="integer",
     *                 minimum=1,
     *                 nullable=true,
     *                 example=5
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Job created successfully.",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/ProductionJobResponse"
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated."
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="The user is not allowed to create jobs."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="The order does not exist or is outside the user's scope."
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid job data or the selected user is not an installer."
     *     )
     * )
     */
    public function store(StoreProductionJobRequest $request): JsonResponse
    {
        $job = $this->jobService->create(
            $request->user(),
            $request->validated()
        );

        return $this->success(
            (new ProductionJobResource($job))->resolve($request),
            'Production job created successfully.'
        )->setStatusCode(201);
    }

    /**
     * Show a job inside the authenticated user's scope.
     *
     * @OA\Get(
     *     path="/api/v1/jobs/{jobId}",
     *     operationId="showProductionJob",
     *     tags={"Production"},
     *     summary="Show an accessible production job",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="jobId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", minimum=1, example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Job retrieved successfully.",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/ProductionJobResponse"
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated."
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="The user is not allowed to view jobs."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="The job does not exist or is outside the user's scope."
     *     )
     * )
     */
    public function show(Request $request, int $jobId): JsonResponse
    {
        $job = $this->jobService->show(
            $request->user(),
            $jobId
        );

        return $this->success(
            (new ProductionJobResource($job))->resolve($request)
        );
    }

    /**
     * @OA\Patch(
     *     path="/api/production/jobs/{jobId}/status",
     *     operationId="changeProductionJobStatus",
     *     tags={"Production"},
     *     summary="Change a production job status",
     *     description="Changes the job status and records its history atomically. Only the next workflow stage is allowed.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="jobId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", minimum=1)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"expected_status", "status"},
     *             @OA\Property(
     *                 property="expected_status",
     *                 type="string",
     *                 enum={
     *                     "cutting",
     *                     "edge_banding",
     *                     "assembly",
     *                     "ready_for_installation",
     *                     "installation",
     *                     "completed"
     *                 },
     *                 example="cutting"
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={
     *                     "cutting",
     *                     "edge_banding",
     *                     "assembly",
     *                     "ready_for_installation",
     *                     "installation",
     *                     "completed"
     *                 },
     *                 example="edge_banding"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Job status updated successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="The operation is forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="The job does not exist or is outside the user's scope"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="The expected status does not match the current status"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid input or disallowed status transition"
     *     )
     * )
     */

    public function changeStatus(
        ChangeProductionJobStatusRequest $request,
        int $jobId,
        ChangeProductionJobStatusAction $action,
    ): JsonResponse {
        $data = $request->validated();

        $job = $action->execute(
            actor: $request->user(),
            jobId: $jobId,
            expectedStatus: JobStatus::from($data['expected_status']),
            nextStatus: JobStatus::from($data['status']),
        );

        return $this->success(
            new ProductionJobResource($job),
            'Job status updated successfully.'
        );
    }
}
