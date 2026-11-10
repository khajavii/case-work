<?php

namespace Modules\Auth\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Modules\Auth\Contracts\LoginStrategy;
use Modules\Auth\Repositories\User\UserRepository;
use Modules\Auth\Repositories\User\UserRepositoryInterface;
use Modules\Auth\Repositories\VerificationCode\VerificationCodeRepository;
use Modules\Auth\Repositories\VerificationCode\VerificationCodeRepositoryInterface;
use Modules\Auth\Rules\LoginRulesResolver;
use Modules\Auth\Rules\OtpLoginRules;
use Modules\Auth\Rules\PasswordLoginRules;
use Modules\Auth\Strategies\PasswordLoginStrategy;
use Nwidart\Modules\Support\ModuleServiceProvider;

class AuthServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Auth';

    protected string $nameLower = 'auth';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );

        $this->app->bind(
            VerificationCodeRepositoryInterface::class,
            VerificationCodeRepository::class
        );

        // Active login strategy.
        $this->app->bind(
            LoginStrategy::class,
            PasswordLoginStrategy::class
        );

        $this->app->tag([
            OtpLoginRules::class,
            PasswordLoginRules::class,
        ], 'auth.login_rules');

        $this->app->singleton(
            LoginRulesResolver::class,
            function ($app): LoginRulesResolver {
                return new LoginRulesResolver(
                    $app->make(LoginStrategy::class),
                    $app->tagged('auth.login_rules'),
                );
            }
        );
    }

    public function boot(): void
    {
        parent::boot();

        $this->registerRateLimiters();
    }

    private function registerRateLimiters(): void
    {
        $this->registerMobileLimiter('auth-login', 5);

        RateLimiter::for('auth-otp', function (Request $request): Limit {
            return Limit::perMinute(3)->by($request->ip());
        });
    }

    private function registerMobileLimiter(
        string $name,
        int $perMinute
    ): void {
        RateLimiter::for($name, function (Request $request) use ($perMinute): Limit {
            $identifier = (string) $request->input('mobile', '');

            return Limit::perMinute($perMinute)
                ->by(Str::lower($identifier).'|'.$request->ip());
        });
    }
}
