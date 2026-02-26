<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReglementRequest extends FormRequest
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
            'location_id'    => 'sometimes|required|integer|exists:locations,id',
            'montant'       => 'sometimes|required|numeric',
            'preuve'             => 'nullable|file|mimes:pdf,png,jpg,jpeg,doc,docx|max:5120', // max en Ko (5 Mo)
            "commentaire"         => "nullable",
            "reference"           => ["nullable", Rule::unique("reglement_locations", "reference")->ignore($this->route("reglement"))],
        ];
    }

    /**
     * Handle the messages
     */
    public function messages(): array
    {
        return [
            // location_id
            'location_id.required' => 'La location est obligatoire.',
            'location_id.integer'  => 'La location doit être un identifiant valide.',
            'location_id.exists'   => 'La location sélectionnée est invalide.',

            // montant
            'montant.required'      => 'Le montant est réquis!.',
            'montant.numeric'      => 'Le montant doit être un nombre valide.',

            // preuve
            'preuve.required'      => 'La preuve est obligatoire.',
            'preuve.file'          => 'La preuve doit être un fichier.',
            'preuve.mimes'         => 'La preuve doit être un fichier de type : pdf,png,jpg,jpeg, doc ou docx.',
            'preuve.max'           => 'La preuve ne doit pas dépasser 5 Mo.',

            // reference
            'reference.unique'     => 'Cette référence existe déjà.',

            // commentaire
            // (nullable → pas de message nécessaire)
        ];
    }
}
