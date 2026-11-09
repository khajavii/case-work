<?php

namespace Modules\Auth\Rules;

use Modules\Auth\Contracts\LoginRules;
use Modules\Auth\Enums\LoginMethod;

class OtpLoginRules implements LoginRules
{
    public function method(): LoginMethod
    {
        return LoginMethod::Otp;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'mobile' => ['required', 'ir_mobile:zero'],
            'code' => ['required', 'string', 'digits:6'],
        ];
    }
}
