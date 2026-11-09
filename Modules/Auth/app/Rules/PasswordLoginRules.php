<?php

namespace Modules\Auth\Rules;

use Modules\Auth\Contracts\LoginRules;
use Modules\Auth\Enums\LoginMethod;

class PasswordLoginRules implements LoginRules
{
    public function method(): LoginMethod
    {
        return LoginMethod::Password;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'mobile' => ['required', 'ir_mobile:zero'],
            'password' => ['required', 'string'],
        ];
    }
}
