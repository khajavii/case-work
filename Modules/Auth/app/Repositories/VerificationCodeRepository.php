<?php

namespace Modules\Auth\Repositories;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Contracts\Repositories\VerificationCodeRepositoryInterface;
use Modules\Auth\Enums\VerificationPurpose;
use Modules\Auth\Models\UserVerificationCode;

class VerificationCodeRepository implements VerificationCodeRepositoryInterface
{
    public function latestFor(string $identifier, VerificationPurpose $purpose): ?UserVerificationCode
    {
        return UserVerificationCode::query()
            ->where('identifier', $identifier)
            ->where('purpose', $purpose->value)
            ->latest('id')
            ->first();
    }

    /**
     * @param  array{
     *     identifier: string,
     *     purpose: VerificationPurpose,
     *     code: string,
     *     expires_at: CarbonInterface
     * }  $data
     */
    public function replaceFor(array $data): void
    {
        DB::transaction(function () use ($data): void {
            UserVerificationCode::query()
                ->where('identifier', $data['identifier'])
                ->where('purpose', $data['purpose']->value)
                ->delete();

            UserVerificationCode::query()->create([
                'identifier' => $data['identifier'],
                'purpose' => $data['purpose'],
                'code' => $data['code'],
                'expires_at' => $data['expires_at'],
            ]);
        });
    }

    public function incrementAttempts(UserVerificationCode $verificationCode): void
    {
        $verificationCode->increment('attempts');
    }

    public function deleteCode(UserVerificationCode $verificationCode): void
    {
        $verificationCode->delete();
    }
}
