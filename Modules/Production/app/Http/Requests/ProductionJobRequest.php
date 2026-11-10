<?php

namespace Modules\Production\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Orders\Models\Order;
use Modules\Production\Models\ProductionJob;

abstract class ProductionJobRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $actor = $this->user();

        return [
            'job_id' => [
                'bail',
                'required',
                'integer',
                'min:1',

                Rule::exists(ProductionJob::class, 'id')
                    ->where(function (Builder $query) use ($actor): void {
                        if ($actor?->role === UserRole::INSTALLER) {
                            $query->where(
                                'assigned_installer_id',
                                $actor->id
                            );

                            return;
                        }

                        if (in_array($actor?->role, [
                            UserRole::ADMIN,
                            UserRole::PLANNER,
                        ], true)) {
                            $query->whereIn(
                                'order_id',
                                Order::query()
                                    ->select('id')
                                    ->where('owner_id', $actor->id)
                            );

                            return;
                        }

                        $query->whereRaw('1 = 0');
                    }),
            ],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function jobRouteParameter(): string
    {
        return 'jobId';
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'job_id' => $this->route($this->jobRouteParameter()),
        ]);
    }
}
