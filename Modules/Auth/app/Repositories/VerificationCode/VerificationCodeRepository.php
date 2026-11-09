<?php

namespace Modules\Auth\Repositories\VerificationCode;

use Illuminate\Support\Facades\DB;
use Modules\Auth\Enums\VerificationPurpose;
use Modules\Auth\Models\UserVerificationCode;

class VerificationCodeRepository implements VerificationCodeRepositoryInterface
{
    public function __construct(protected UserVerificationCode $model)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function latestFor(string $identifier, VerificationPurpose $purpose): ?UserVerificationCode
    {
        return $this->model->query()
            ->where('identifier', $identifier)
            ->where('purpose', $purpose->value)
            ->latest('id')
            ->first();
    }

    /**
     * {@inheritdoc}
     */
    public function replaceFor(array $data): void
    {
        DB::transaction(function () use ($data): void {
            $this->model->query()
                ->where('identifier', $data['identifier'])
                ->where('purpose', $data['purpose']->value)
                ->delete();

            $this->model->query()->create([
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
