<?php

namespace Modules\Production\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Production\Enums\JobStatus;

class ChangeProductionJobStatusRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'expected_status' => [
                'required',
                Rule::enum(JobStatus::class),
            ],

            'status' => [
                'required',
                Rule::enum(JobStatus::class),
            ],

            'changed_by' => ['prohibited'],
            'created_at' => ['prohibited'],
            'job_id' => ['prohibited'],
            'owner_id' => ['prohibited'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
