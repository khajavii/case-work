<?php

namespace Modules\Orders\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaginationResource;
use App\Traits\Controllers\ApiJsonResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Orders\Http\Requests\IndexOrderRequest;
use Modules\Orders\Http\Requests\StoreOrderRequest;
use Modules\Orders\Services\OrderService;
use Modules\Orders\Transformers\OrderResource;

class OrderController extends Controller
{
    use ApiJsonResponse;

    public function __construct(
        private readonly OrderService $orderService
    ) {
    }

    /**
     * List orders owned by the authenticated admin or planner.
     *
     * @OA\Get(
     *     path="/api/v1/orders",
     *     operationId="listOrders",
     *     tags={"Orders"},
     *     summary="List owned orders",
     *     description="Return only orders owned by the authenticated admin or planner.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         description="Filter by order status.",
     *         @OA\Schema(
     *             type="string",
     *             enum={"pending", "in_progress", "completed"}
     *         )
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
     *         description="Orders retrieved successfully.",
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
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Order")
     *                 ),
     *                 @OA\Property(
     *                     property="pagination",
     *                     type="object",
     *                     @OA\Property(
     *                         property="current_page",
     *                         type="integer",
     *                         example=1
     *                     ),
     *                     @OA\Property(
     *                         property="per_page",
     *                         type="integer",
     *                         example=15
     *                     ),
     *                     @OA\Property(
     *                         property="last_page",
     *                         type="integer",
     *                         example=1
     *                     ),
     *                     @OA\Property(
     *                         property="total",
     *                         type="integer",
     *                         example=6
     *                     )
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
     *         description="The user is not allowed to list orders."
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid status or pagination parameters.",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The given data was invalid."
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\AdditionalProperties(
     *                     type="array",
     *                     @OA\Items(type="string")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(IndexOrderRequest $request): JsonResponse
    {
        $orders = $this->orderService->paginate(
            $request->user(),
            $request->validated()
        );

        return $this->success([
            'items' => OrderResource::collection(
                $orders->getCollection()
            )->resolve($request),
            'pagination' => PaginationResource::make($orders),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('orders::create');
    }

    /**
     * Create a pending order for the authenticated admin or planner.
     *
     * @OA\Post(
     *     path="/api/v1/orders",
     *     operationId="createOrder",
     *     tags={"Orders"},
     *     summary="Create an order",
     *     description="Create a pending order. Ownership is assigned from the authenticated user.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"customer_name", "address"},
     *             @OA\Property(
     *                 property="customer_name",
     *                 type="string",
     *                 maxLength=255,
     *                 example="Oliver Anderson"
     *             ),
     *             @OA\Property(
     *                 property="address",
     *                 type="string",
     *                 maxLength=2000,
     *                 example="12 Oak Street, Springfield"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Order created successfully."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Order"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated."
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="The user is not allowed to create orders."
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid order data.",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The given data was invalid."
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\AdditionalProperties(
     *                     type="array",
     *                     @OA\Items(type="string")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->create(
            $request->user(),
            $request->validated()
        );

        return $this->success(
            (new OrderResource($order))->resolve($request),
            'Order created successfully.'
        )->setStatusCode(201);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('orders::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('orders::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
