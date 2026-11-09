<?php

namespace Modules\Auth\Contracts;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use Modules\Auth\Enums\LoginMethod;

interface LoginStrategy
{
    public function method(): LoginMethod;

    /**
     * Authenticate the given credentials and return the matching user.
     *
     * @param  array<string, mixed>  $credentials
     *
     * @throws ValidationException
     */
    public function authenticate(array $credentials): User;
}
