<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Auth\Rules\LoginRulesResolver;

/**
 * Credentials for the active login strategy.
 *
 * OTP mode: mobile + code
 * Password mode: mobile + password
 */
class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return app(LoginRulesResolver::class)->rules();
    }
}
