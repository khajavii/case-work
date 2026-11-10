<?php

namespace Modules\Production\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Production\Models\ProductionJob;

class StoreProductionJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'create',
            ProductionJob::class
        ) ?? false;
    }

    /**
     * Get the validation rules.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', 'min:1'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:10000'],

            'assigned_installer_id' => [
                'nullable',
                'integer',
                'min:1',
                Rule::exists('users', 'id')
                    ->where('role', UserRole::INSTALLER->value),
            ],

            'status' => ['prohibited'],
            'owner_id' => ['prohibited'],
        ];
    }
}
