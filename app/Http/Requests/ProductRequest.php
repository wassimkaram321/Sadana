<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Traits\GeneralTrait;

class ProductRequest extends FormRequest
{

    use GeneralTrait;
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
            $rules = [
                'products.*.store_name' => ['required','string'],
                'products.*.brand' => ['required','string'],
                'products.*.brand_id' => ['numeric'],
                'products.*.store_id' => ['numeric'],
                'products.*.name' => ['required','string'],
                'products.*.unit_price' => ['required', 'numeric'],
                'products.*.purchase_price' => ['required', 'numeric'],
                'products.*.quantity' => ['required', 'numeric'],
                'products.*.notes' => ['required','string'],
                'products.*.Scientific_formula' => ['required','string'],
                'products.*.q_normal_offer' => ['required', 'numeric'],
                'products.*.q_featured_offer' => ['required', 'numeric'],
                'products.*.normal_offer' => ['required', 'numeric'],
                'products.*.featured_offer' => ['required', 'numeric'],
                'products.*.production_date' => ['required', 'date'],
                'products.*.expiry_date' => ['required', 'date'],
                'products.*.demand_limit' => ['required', 'numeric'],
                'products.*.num_id' => ['required', 'numeric'],

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
