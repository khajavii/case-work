<?php

namespace Modules\Orders\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Orders\Models\Order;

class StoreOrderRequest extends FormRequest
{
    /**
     * Get the validation rules.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:2000'],
            'owner_id' => ['prohibited'],
            'status' => ['prohibited'],
        ];
    }

    /**
     * Determine whether the authenticated user may create orders.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Order::class) ?? false;
    }
}
