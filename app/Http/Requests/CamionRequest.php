<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CamionRequest extends FormRequest
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
            "libelle" => "required|string",
            "immatriculation" => [
                "required",
                Rule::unique('camions', 'immatriculation')->ignore($this->route("camion")),
            ],
        ];
    }

    /***
     * 
     */
    public function messages(): array
    {
        return [
            // libelle
            'libelle.required' => 'Le libellé est obligatoire.',
            'libelle.string'   => 'Le libellé doit être une chaîne de caractères.',

            // immatriculation
            'immatriculation.required' => "L'immatriculation est obligatoire.",
            'immatriculation.unique'   => "Cette immatriculation existe déjà.",
        ];
    }
}
