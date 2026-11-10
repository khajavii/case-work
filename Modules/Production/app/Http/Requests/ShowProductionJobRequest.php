<?php

namespace Modules\Production\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Orders\Models\Order;
use Modules\Production\Models\ProductionJob;

class ShowProductionJobRequest extends ProductionJobRequest
{
    protected function jobRouteParameter(): string
    {
        return 'production';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {

        return [
            ...parent::rules(),
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }


}
