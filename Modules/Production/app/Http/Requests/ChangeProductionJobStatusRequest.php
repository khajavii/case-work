<?php

namespace Modules\Production\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Production\Enums\JobStatus;

class ChangeProductionJobStatusRequest extends ProductionJobRequest
{
    protected function jobRouteParameter(): string
    {
        return 'jobId';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),

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

    /**
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'job_id' => $this->route('jobId'),
        ]);
    }
}
