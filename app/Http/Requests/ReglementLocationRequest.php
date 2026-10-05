<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReglementLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('PATCH') ? 'sometimes|required|' : 'required|';

        return [
            "location_id" => $required . "integer|exists:locations,id",
            "montant" => $required . "numeric|min:0.01",
            "commentaire" => "nullable|string",
            "preuve" => "nullable|file|mimes:pdf,png,jpg,jpeg,doc,docx|max:5120",
            "camions" => "nullable|array",
        ];
    }

    public function messages(): array
    {
        return [
            'location_id.required' => "La location est obligatoire.",
            'location_id.integer' => "L'identifiant de la location doit être un nombre.",
            'location_id.exists' => "La location sélectionnée est invalide.",
            'montant.required' => "Le montant est obligatoire.",
            'montant.numeric' => "Le montant doit être un nombre.",
            'montant.min' => "Le montant doit être supérieur ou égal à 0,01.",
            'commentaire.string' => "Le commentaire doit être une chaîne de caractères.",
            'preuve.file' => "La preuve doit être un fichier valide.",
            'preuve.mimes' => "La preuve doit être de type : pdf, png, jpg, jpeg, doc ou docx.",
            'preuve.max' => "La preuve ne doit pas dépasser 5 Mo.",
            'camions.array' => "Les camions doivent être transmis sous forme de liste.",
        ];
    }
}