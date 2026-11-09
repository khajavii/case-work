<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Modules\Auth\Enums\VerificationPurpose;

#[Fillable(['purpose', 'identifier', 'code', 'attempts', 'expires_at'])]
#[Hidden(['code'])]
class UserVerificationCode extends Model
{
    protected $table = 'user_verification_code';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purpose' => VerificationPurpose::class,
            'attempts' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    public function hasExceededAttempts(int $maxAttempts): bool
    {
        return $this->attempts >= $maxAttempts;
    }
}
