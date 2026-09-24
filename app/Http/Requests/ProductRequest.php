<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
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
        $id = $this->route('product');

        return [
            'libele' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'libele')->ignore($id),
            ],
            'description' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'libele.required' => 'Le libellé du produit est obligatoire.',
            'libele.string' => 'Le libellé du produit doit être une chaîne de caractères.',
            'libele.max' => 'Le libellé du produit ne doit pas dépasser :max caractères.',
            'libele.unique' => 'Ce libellé de produit existe déjà.',
            'description.string' => 'La description doit être une chaîne de caractères.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'libele' => 'libellé',
            'description' => 'description',
        ];
    }
}