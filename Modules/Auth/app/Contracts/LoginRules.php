<?php

namespace Modules\Auth\Contracts;

use Modules\Auth\Enums\LoginMethod;

interface LoginRules
{
    public function method(): LoginMethod;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array;
}
