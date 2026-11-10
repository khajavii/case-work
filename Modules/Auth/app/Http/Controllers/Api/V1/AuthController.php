<?php

namespace Modules\Auth\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\Controllers\ApiJsonResponse;
use Illuminate\Http\JsonResponse;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\SendOtpRequest;
use Modules\Auth\Services\AuthService;
use Modules\Auth\Services\OtpService;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="Authentication and access token management"
 * )
 */
class AuthController extends Controller
{
    use ApiJsonResponse;

    public function __construct(
        private readonly AuthService $authService,
        private readonly OtpService $otpService,
    ) {}

    /**
     * Authenticate using the configured login strategy.
     *
     * OTP authentication requires mobile and code.
     * Password authentication requires mobile and password.
     *
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     operationId="login",
     *     tags={"Authentication"},
     *     summary="Authenticate user",
     *     description="Authenticate a user using the active login strategy and return a JWT access token.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"mobile"},
     *             @OA\Property(
     *                 property="mobile",
     *                 type="string",
     *                 example="09120000001"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 format="password",
     *                 example="password",
     *                 description="User password"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Authentication successful.",
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
     *                     property="access_token",
     *                     type="string",
     *                     example="eyJ0eXAiOiJKV1QiLCJhbGci..."
     *                 ),
     *                 @OA\Property(
     *                     property="token_type",
     *                     type="string",
     *                     example="bearer"
     *                 ),
     *                 @OA\Property(
     *                     property="expires_in",
     *                     type="integer",
     *                     example=28800
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid input or incorrect authentication credentials.",
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
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Too many requests.",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Too many requests."
     *             )
     *         )
     *     )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return $this->success($result);
    }

    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = $this->otpService->send($data['mobile']);

        if ($result['status'] !== 'success') {
            return $this->error($result['message'], 422);
        }

        return $this->success($result['data'], $result['message']);
    }

    /**
     * Invalidate the access token associated with the current request.
     *
     * @OA\Post(
     *     path="/api/v1/auth/logout",
     *     operationId="logout",
     *     tags={"Authentication"},
     *     summary="Logout user",
     *     description="Invalidate the JWT access token associated with the current request.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful.",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="You have been logged out successfully."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthenticated."
     *             )
     *         )
     *     )
     * )
     */
    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return $this->success(message: __('auth::messages.logged_out'));
    }
}
