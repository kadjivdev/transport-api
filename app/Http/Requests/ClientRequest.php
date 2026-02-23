<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientRequest extends FormRequest
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
            "nom" => "required|string",
            "prenom" => "required|string",
            "phone" => [
                "required",
                Rule::unique('clients', 'phone')->ignore($this->route("client")),
            ],
            "ifu" => ["required", Rule::unique('clients', 'phone')->ignore($this->route("client"))],
        ];
    }

    /***
     * 
     */
    public function messages(): array
    {
        return [
            'nom.required' => 'Le champ nom est obligatoire.',
            'nom.string' => 'Le champ nom doit être une chaîne de caractères.',

            'prenom.required' => 'Le champ prénom est obligatoire.',
            'prenom.string' => 'Le champ prénom doit être une chaîne de caractères.',

            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'phone.unique' => 'Ce numéro de téléphone est déjà utilisé.',

            'ifu.required' => 'L\'IFU est obligatoire.',
            'ifu.unique' => 'Cet IFU est déjà utilisé.',
        ];
    }
}
