<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TvaRequest extends FormRequest
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
            'reference' => ['nullable', Rule::unique("tvas", 'reference')->ignore($this->route('tva'))],
            "location_id" => "required|integer|exists:locations,id",
            "montant" => 'required|numeric',
            "preuve" => "nullable|file|mimes:pdf,png,docx,doc,jpeg|max:5120", //max en KO (5Mo)
            "commentaire" => "nullable|string",
        ];
    }

    public function messages(): array
    {
        return [
            'reference.unique' => "La référence est déjà utilisée.",

            'location_id.required' => "La location est obligatoire.",
            'location_id.integer' => "La location doit être un identifiant valide.",
            'location_id.exists' => "La location sélectionnée n'existe pas.",

            'montant.required' => "Le montant est obligatoire.",
            'montant.numeric' => "Le montant doit être un nombre.",

            'preuve.file' => "Le fichier de preuve doit être un fichier valide.",
            'preuve.mimes' => "Le fichier de preuve doit être de type : pdf, png, docx, doc ou jpeg.",
            'preuve.max' => "Le fichier de preuve ne doit pas dépasser 5 Mo.",

            'commentaire.string' => "Le commentaire doit être une chaîne de caractères.",
        ];
    }
}
