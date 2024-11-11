<?php

namespace App\Http\Requests\Users;

use Illuminate\Validation\Rule;
use App\Models\SystemPermission;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRoleRequest extends FormRequest
{

    /**
     * Indicates if the validator should stop on the first rule failure.
     *
     * @var bool
     */
    protected $stopOnFirstFailure = true;

    private $systemPermissions;

    public function __construct()
    {
        $this->systemPermissions = SystemPermission::all(['name'])->pluck('name')->toArray();
    }

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
            'role' => [
                'required',
                Rule::in(['Admin', 'User'])
            ],


            'permissions' => [
                'required_if:role,User',
                'prohibited_if:role,Admin',
                'array',
                function ($attribute, $value, $fail) {
                    $systemPermissionsCount = count($this->systemPermissions);
                    $sentPermissionsCount = count($this->input('permissions'));
                    if ($sentPermissionsCount < $systemPermissionsCount) {
                        $fail("Missing permissions. Expected $systemPermissionsCount, but received $sentPermissionsCount.");
                    } else if ($sentPermissionsCount > $systemPermissionsCount) {
                        $fail("Too many permissions provided. Expected $systemPermissionsCount, but received $sentPermissionsCount.");
                    }
                },
            ],
            'permissions.*.name' => [
                'required_with:permissions.*',
                'string',
                'regex:/^[a-zA-ZÀ-ÿ\s]+$/u',
                'distinct',
                function ($attribute, $value, $fail) {
                    if (!in_array($value, $this->systemPermissions)) {
                        $fail("The $attribute: '$value' is not a valid permission name. Please refer to the following list of available system permissions: [" . implode(', ', $this->systemPermissions) . "]");
                    }
                },
            ],
            'permissions.*.value' => [
                'required_with:permissions.*',
                'integer',
                'regex:/^(-1|\d+)$/',
            ],
        ];

    }
}
