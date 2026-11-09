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
     *                 example="09123456789"
     *             ),
     *             @OA\Property(
     *                 property="code",
     *                 type="string",
     *                 example="123456",
     *                 description="OTP verification code"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 format="password",
     *                 example="Secret123",
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

    /**
     * Send a login OTP or return the remaining time of an active OTP.
     *
     * @OA\Post(
     *     path="/api/v1/auth/send-otp",
     *     operationId="sendOtp",
     *     tags={"Authentication"},
     *     summary="Send login OTP",
     *     description="Send a one-time password to the user's mobile number. If an active OTP already exists, return its remaining expiration time.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"mobile"},
     *             @OA\Property(
     *                 property="mobile",
     *                 type="string",
     *                 example="09123456789"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent successfully or an active OTP expiration time was returned.",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Verification code sent successfully."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="second",
     *                     type="integer",
     *                     example=120,
     *                     description="Remaining seconds until the OTP expires or can be requested again."
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid mobile number or OTP request.",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The given data was invalid."
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object"
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
