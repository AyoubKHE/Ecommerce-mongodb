<?php

namespace App\Http\Requests\Users;

use App\Models\SystemPermission;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class FirstAdminRegistrationRequest extends FormRequest
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
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-zA-ZÀ-ÿ]+$/u'
            ],


            'lastName' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-zA-ZÀ-ÿ]+$/u'
            ],


            'username' => [
                'required',
                'unique:users',
                'string',
                'min:3',
                'max:30',
            ],


            'email' => [
                'required',
                'unique:users',
                'email',
                'max:255'
            ],


            'password' => [
                'required',
                'string',
                'min:1',
                // 'min:8',
                'max:50',
                // 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#]).{8,30}$/'
            ],


            'phone' => [
                'required',
                'unique:users',
                'digits:10',
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
                'required',
                'date'
            ],


            'profileImage' => [
                'required',
                'image',
                'mimes:jpg,png,jpeg,svg',
                'max:5000'
            ],
        ];
    }
}
