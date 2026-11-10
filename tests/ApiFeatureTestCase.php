<?php

namespace Tests;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

abstract class ApiFeatureTestCase extends TestCase
{
    use RefreshDatabase;

    protected function beforeRefreshingDatabase(): void
    {
        $name = config('database.default');
        $connection = config("database.connections.{$name}", []);

        // Check configuration before opening any database connection.
        if (! app()->environment('testing')
            || ($connection['driver'] ?? null) !== 'sqlite'
            || ($connection['database'] ?? null) !== ':memory:'
            || ! empty($connection['read'])
            || ! empty($connection['write'])
            || ! empty($connection['url'])) {
            throw new RuntimeException('Tests require SQLite :memory: without a database URL.');
        }
    }

    protected function createUser(string $role): Authenticatable
    {
        $provider = config('auth.defaults.provider', 'users');
        $guard = config('auth.guards.' . $this->authGuard());
        $provider = $guard['provider'] ?? $provider;
        $class = config("auth.providers.{$provider}.model");

        if (! is_string($class) || ! is_subclass_of($class, Authenticatable::class)) {
            throw new RuntimeException('Configure the authentication user model for tests.');
        }

        $user = new $class();
        $user->forceFill([
            'name' => ucfirst($role) . ' User',
            'email' => Str::uuid() . '@example.test',
            'password' => Hash::make('password'),
            'role' => $role,
        ])->save();

        return $user;
    }

    protected function authGuard(): string
    {
        return env('TEST_AUTH_GUARD', 'api');
    }

    protected function signIn(Authenticatable $user): void
    {
        $guardName = $this->authGuard();

        if (config("auth.guards.{$guardName}.driver") !== 'jwt') {
            throw new RuntimeException(
                "The [{$guardName}] guard must use the JWT driver."
            );
        }

        $token = auth()->guard($guardName)->login($user);

        // Clear the cached authenticated user so requests authenticate via JWT.
        app('auth')->forgetGuards();

        $this->withToken($token);
    }
}
