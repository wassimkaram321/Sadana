<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Traits\GeneralTrait;

class PharmicesRequest extends FormRequest
{

    use GeneralTrait;
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'Pharmacies.*.f_name' => ['required', 'string'],
            'Pharmacies.*.l_name' => ['required', 'string'],
            'Pharmacies.*.phone' => ['required', 'numeric'],
            'Pharmacies.*.email' => ['required', 'string','email'],
            'Pharmacies.*.password' => ['required', 'string'],
            'Pharmacies.*.name' => ['required', 'string'],
            'Pharmacies.*.land_number' => ['required', 'numeric'],
            'Pharmacies.*.from' => ['required', 'string'],
            'Pharmacies.*.to' => ['required', 'string'],
            'Pharmacies.*.statusToday' => ['required', 'string'],
            'Pharmacies.*.Address' => ['required', 'string'],
            'Pharmacies.*.city' => ['required', 'string'],
            'Pharmacies.*.lat' => ['required', 'string'],
            'Pharmacies.*.lan' => ['required', 'string'],
            'Pharmacies.*.region' => ['required', 'string'],

        ];

        switch ($this->method()) {
            case 'POST': {
                    return $rules;
                }
            case 'PUT':
            case 'PATCH': {
                    return $rules;
                }
            default:
                break;
        }
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->returnError($validator->errors()));
    }
}
