<?php

namespace App\Http\Requests;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|exists:employees,id',
            'employee_name' => 'required|string|min:2|max:256',
            'email' => 'required|email',
            'phone' => 'required|regex:/^\+380\d{9}$/',
            'position_id' => 'required|exists:positions,id',
            'salary' => 'required|numeric|min:0|max:500000',
            'supervisor_id' => [
                'required',
                'exists:employees,id',
                function ($attribute, $value, $fail) {
                    $userId = $this->input('user_id');
                    $supervisorUserId = $this->input('supervisor_user_id');

                    if ($userId && $supervisorUserId && $userId === $supervisorUserId) {
                        $fail('Supervisor cannot be the same as the user.');
                    }
                },
            ],
            'employment_date' => 'required|date',
            'processed_image_path' => 'nullable|string',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $supervisor = Employee::select('level')->find($this->supervisor_id);

            if ($supervisor && $supervisor->level === 0) {
                $validator->errors()->add('supervisor_id', 'This supervisor cannot manage the employee because their level is 0.');
            }

            $employee = Employee::select('level')->find($this->id);

            if ($supervisor && $employee->level >= 4) {
                $validator->errors()->add(
                    'employee_name',
                    "This subordinate employee cannot be managed by anyone."
                );
                $validator->errors()->add(
                    'supervisor_id',
                    "The manager cannot manage this subordinate."
                );
                return;
            }

            if ($supervisor && $employee->level >= $supervisor->level) {
                $requiredLevel = $employee->level + 2;
                $validator->errors()->add(
                    'supervisor_id',
                    "Supervisor level must be at least {$requiredLevel}."
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'id.required' => 'User not found in the registry.',
            'supervisor_id.required' => 'Supervisor not found in the registry.',
        ];

    }
}
