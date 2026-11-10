# Case Work API

A modular Laravel API for cabinet orders and production/installation workflows, with JWT authentication, role-based authorization, status history, and Swagger documentation.

Repository: https://github.com/khajavii/case-work

## Technology Stack

The following version constraints come from the project's `composer.json`.

### Application packages

| Package | Constraint | Purpose |
| --- | --- | --- |
| PHP | `^8.3` | Composer PHP requirement; the supplied Dockerfile uses PHP 8.4 |
| `laravel/framework` | `^13.17` | Application framework |
| `nwidart/laravel-modules` | `^13.3` | Modular application structure |
| `tymon/jwt-auth` | `^2.3` | JWT authentication |
| `darkaonline/l5-swagger` | `^8.6` | Swagger / OpenAPI documentation |

### Development packages

- `fakerphp/faker`: `^1.23`
- `laravel/pail`: `^1.2.5`
- `laravel/pao`: `^1.0.6`
- `laravel/pint`: `^1.27`
- `mockery/mockery`: `^1.6`
- `nunomaduro/collision`: `^8.6`
- `phpunit/phpunit`: `^12.5.12`

PostgreSQL is used for application data; SQLite is used for isolated automated tests. The supplied Docker configuration includes PHP-FPM, Nginx, PostgreSQL 17, and Redis.

> Use PHP 8.4 for local development to match the Dockerfile. Although the root Composer constraint allows PHP 8.3, dependencies may require a higher version.

## Architecture

The application is organized into three modules:

- **Auth** — JWT authentication and authentication strategies.
- **Orders** — order creation and listing.
- **Production** — production jobs, scoped access, status transitions, and status history.

The code aims to follow clean-code and SOLID principles. Enums represent roles and workflow states, services hold reusable behavior, repositories encapsulate persistence queries, and actions coordinate specific use cases where appropriate.

Authentication uses the **Strategy pattern** to support password and OTP approaches. Password authentication is the currently configured approach and is documented in Swagger. The OTP strategy is not currently documented in Swagger; do not assume a fully configured OTP delivery service is available.

User-facing messages are intended to live in language files. Some remaining inline messages may still need to be moved there.

## Authorization Design

Roles are represented by an enum-backed user field rather than separate `roles` and `permissions` tables. Laravel Gates and Policies enforce access rules without a third-party permissions package.

This is a deliberate choice for the current project's small, fixed role set. For a larger application with dynamic permissions, a dedicated role/permission package would be considered.

Roles:

- `admin`
- `planner`
- `installer`

Installers can access only jobs assigned to them. Under the current ownership-based contract, admin and planner access to orders and related jobs is scoped to the order owner; the admin role does not imply unrestricted access to every record.

## Local Installation — Windows / PowerShell

### 1. Clone and install dependencies

```powershell
git clone https://github.com/khajavii/case-work.git
cd case-work
composer install
Copy-Item .env.example .env
```

Review `.env` before running database commands. The available environment settings are documented in `.env.example`.

### 2. Configure PostgreSQL

Create a dedicated local database and update `.env`:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=case_work
DB_USERNAME=your_local_user
DB_PASSWORD=your_local_password
```

If using the PostgreSQL service from Docker while running PHP on Windows, use `DB_HOST=127.0.0.1` and `DB_PORT=5433` instead.

Remove or clear any stale `DB_URL` value that would override these settings.

### 3. Generate application and JWT secrets

```powershell
php artisan key:generate
php artisan jwt:secret
php artisan config:clear
```

`jwt:secret` generates and writes `JWT_SECRET` to `.env`. Keep the generated value; do not replace it with a fixed example or commit it to Git. Each environment should have its own secrets.

### 4. Migrate and seed

Confirm that the active connection points to your local database, then run:

```powershell
php artisan migrate
php artisan db:seed
```

Seeders provide fake/demo data, including users for the three roles. Do not assume seeders are safe to rerun on valuable data without reviewing their implementation.

### 5. Start the API

```powershell
php artisan serve
```

Local URL: http://127.0.0.1:8000

This is an API project; no frontend or login UI is required. Node.js and an asset build are not needed for the API-only setup above.

## Demo Credentials

All listed demo accounts use the password `password`.

| Role | Email | Mobile | Password |
| --- | --- | --- | --- |
| Admin | `admin@example.com` | `09120000001` | `password` |
| Planner | `planner1@example.com` | `09120000002` | `password` |
| Installer | `installer1@example.com` | `09120000004` | `password` |

Use the identifier field accepted by the password login endpoint in Swagger. These accounts are for local/demo use only.

## API Documentation and Authentication

Swagger UI:

http://127.0.0.1:8000/api/documentation

Regenerate the documentation when annotations change:

```powershell
php artisan l5-swagger:generate
```

Authenticate using the documented password login endpoint and use the returned JWT for protected requests:

```http
Authorization: Bearer <token>
Accept: application/json
```

Use Swagger's authorization dialog according to the configured security scheme. OTP endpoints are not currently included in Swagger.

Inspect the actual registered API routes with:

```powershell
php artisan route:list --path=api -vv
```

## Production Workflow

Jobs progress sequentially through these stages:

```text
cutting
  → edge_banding
  → assembly
  → ready_for_installation
  → installation
  → completed
```

Status changes include an `expected_status` to detect stale requests. The action performs authorization, loads the visible job with a row lock, validates the expected status and transition, and persists the new status and history within one database transaction.

- A stale expected status returns `409 Conflict`.
- An invalid transition returns `422 Unprocessable Entity`.
- Status history records the previous state, new state, actor, and timestamp.
- Unauthorized or invalid requests must not create status history.

## Ready-for-Installation Queue Job

When a job enters `ready_for_installation`, a queued job logs the readiness event. Dispatch is registered with `afterCommit()` so it occurs only after the status/history transaction commits successfully. A rolled-back transaction does not dispatch the job.

This feature currently writes a log; it does not send an email, SMS, or push notification.

### Queue configuration

For asynchronous processing on Windows, a database queue can be used:

```dotenv
QUEUE_CONNECTION=database
CACHE_STORE=file
```

The database queue requires a `jobs` table. Only if its migration is missing, generate it:

```powershell
php artisan make:queue-table
```

Then, against your local database:

```powershell
php artisan migrate
php artisan config:clear
```

Run a continuous worker manually:

```powershell
php artisan queue:work database --queue=default --tries=3 --timeout=60
```

### Scheduled processing

The queue-processing schedule runs once per minute. The corresponding registration in `routes/console.php` is:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('queue:work database --queue=default --stop-when-empty --tries=3 --timeout=60')
    ->everyMinute()
    ->withoutOverlapping(10);
```

Run the scheduler locally in a separate terminal:

```powershell
php artisan schedule:work
```

Inspect registered tasks:

```powershell
php artisan schedule:list
```

Choose either continuous manual queue processing or scheduled processing; both are not required for local use. Scheduled processing may delay the log by approximately one minute, plus any queue backlog. Use a persistent cache store for the scheduler's overlap lock, not `array`.

With `QUEUE_CONNECTION=sync`, the job executes in-process after commit rather than in a background worker.

On a deployed server, configure a process manager or a system scheduler as appropriate; merely defining a schedule does not execute it automatically. After changing job code, restart long-running workers:

```powershell
php artisan queue:restart
```

## Automated Tests

Tests are currently run locally on Windows. Docker configuration is also provided, but a verified clean Docker installation should not be inferred from its presence.

Tests use SQLite `:memory:` with a dedicated testing environment. Never point tests at an application or production database.

Create `.env.testing` from an appropriate example or create it manually with:

## Automated Tests

Tests use an isolated SQLite in-memory database. Enable `pdo_sqlite`
in your PHP installation and configure `.env.testing` with:
```dotenv
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
DB_URL=
CACHE_STORE=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
CACHE_STORE=file
```

Generate separate testing secrets:

```powershell
php artisan key:generate --env=testing
php artisan jwt:secret --env=testing
php artisan config:clear
composer dump-autoload
```
## Docker Configuration

The supplied Compose stack contains:

- PHP 8.4 FPM
- Nginx on http://localhost:8080
- PostgreSQL 17, exposed on `127.0.0.1:5433`
- Redis

The PHP Dockerfile includes PostgreSQL extensions, SQLite PDO support for tests, Xdebug, and other project extensions.

Build and start:

```powershell
docker compose build php
docker compose up -d
```

For PHP running inside Docker, database networking differs from host networking:

```dotenv
DB_HOST=postgres
DB_PORT=5432
REDIS_HOST=redis
REDIS_PORT=6379
```

## Useful Maintenance Commands

```powershell
composer validate
composer check-platform-reqs
composer show --direct
composer dump-autoload
php artisan module:list
php artisan config:clear
php artisan route:clear
php artisan l5-swagger:generate
php artisan db:seed
php artisan queue:failed
php artisan schedule:list
vendor/bin/pint
```
