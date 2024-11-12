<?php

namespace App\Http\Requests\Users;

use App\Models\User;
use Illuminate\Validation\Rule;
use App\Models\SystemPermission;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMyAccountRequest extends FormRequest
{

    /**
     * Indicates if the validator should stop on the first rule failure.
     *
     * @var bool
     */
    protected $stopOnFirstFailure = true;

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
            'firstName' => [
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-zA-ZÀ-ÿ]+$/u'
            ],


            'lastName' => [
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-zA-ZÀ-ÿ]+$/u'
            ],


            'username' => [
                'string',
                'min:3',
                'max:30',
                function ($attribute, $value, $fail) {
                    $existingUser = User::where(
                        'username',
                        $value
                    )->where(
                            'id',
                            '!=',
                            $this->get('loggedInUser')->id
                        )->first();

                    if ($existingUser) {
                        $fail("The username has already been taken.");
                    }
                },
            ],


            'phone' => [
                'digits:10',
                function ($attribute, $value, $fail) {
                    $existingUser = User::where(
                        'phone',
                        $value
                    )->where(
                            'id',
                            '!=',
                            $this->get('loggedInUser')->id
                        )->first();
                        
                    if ($existingUser) {
                        $fail("The phone has already been taken.");
                    }
                },
            ],


            'address' => [
                'nullable',
                'required_array_keys:city,zip,streetNumber,addressLine',
                function ($attribute, $value, $fail) {
                    if (count($this->input('address')) != 4) {
                        $fail('The address field must contain entries for: city, zip, streetNumber, addressLine.');
                    }
                },
            ],
            'address.city' => [
                'string',
                'min:2',
                'max:100',
                'regex:/^[a-zA-ZÀ-ÿ\s]+$/u'
            ],
            'address.zip' => [
                'string',
                'size:5',
                'regex:/^\d+$/u'
            ],
            'address.streetNumber' => [
                'string',
                'regex:/^\d+$/u'
            ],
            'address.addressLine' => [
                'string',
                'max:255'
            ],


            'birthDate' => [
                'date'
            ],
        ];

    }
}
