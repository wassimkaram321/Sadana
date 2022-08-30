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
                'products.*.name' => ['required','string'],
                'products.*.description' => ['required','string'],
                'products.*.unit' => ['required','string'],
                'products.*.brand_id' =>  ['required', 'numeric'],
                'products.*.discount_type' => ['required', 'string'],

                'products.*.discount' =>  ['required', 'numeric'],
                'products.*.unit_price' => ['required', 'numeric'],
                //'products.*.lang' => ['required', 'string'],
                'products.*.store_id' => ['required', 'numeric'],

                'products.*.current_stock' => ['required', 'numeric'],
                'products.*.purchase_price' => ['required', 'numeric'],
                'products.*.tax' => ['required', 'numeric'],
                'products.*.tax_type' => ['required', 'string'],

                'products.*.shipping_cost' => ['required', 'numeric'],

                'products.*.production_date' => ['required', 'date'],
                'products.*.expiry_date' => ['required', 'date'],
                'products.*.num_id' => ['required', 'numeric'],

                'products.*.image' => ['file','mimes:jpg,jpeg,png', 'max:3000'],

                'products.*.thumbnail_image' => ['file','mimes:jpg,jpeg,png', 'max:3000'],
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
