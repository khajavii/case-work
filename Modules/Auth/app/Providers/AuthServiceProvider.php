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
use Modules\Auth\Strategies\OtpLoginStrategy;
use Nwidart\Modules\Support\ModuleServiceProvider;

class AuthServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Auth';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'auth';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(VerificationCodeRepositoryInterface::class, VerificationCodeRepository::class);

        /*
         * The active login strategy.
         *
         * In a crisis, switch the concrete class below from OtpLoginStrategy
         * to PasswordLoginStrategy. No controller or route changes are needed;
         * the validation rules follow the active strategy automatically.
         */
        $this->app->bind(LoginStrategy::class, OtpLoginStrategy::class);

        $this->app->tag([
            OtpLoginRules::class,
            PasswordLoginRules::class,
        ], 'auth.login_rules');

        $this->app->singleton(LoginRulesResolver::class, function ($app): LoginRulesResolver {
            return new LoginRulesResolver(
                $app->make(LoginStrategy::class),
                $app->tagged('auth.login_rules'),
            );
        });
    }

    /**
     * Boot the module services.
     */
    public function boot(): void
    {
        parent::boot();

        $this->registerRateLimiters();
    }

    /**
     * Register the login and OTP rate limiters.
     */
    private function registerRateLimiters(): void
    {
        $this->registerMobileLimiter('auth-login', 5);

        RateLimiter::for('auth-otp', function (Request $request): Limit {
            return Limit::perMinute(3)->by($request->ip());
        });
    }

    /**
     * Rate limit by mobile number and IP address.
     */
    private function registerMobileLimiter(string $name, int $perMinute): void
    {
        RateLimiter::for($name, function (Request $request) use ($perMinute): Limit {
            $identifier = (string) $request->input('mobile', '');

            return Limit::perMinute($perMinute)->by(Str::lower($identifier).'|'.$request->ip());
        });
    }

    /**
     * Define module schedules.
     *
     * @param  $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
