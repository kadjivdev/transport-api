<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
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
        Log::debug("The request reglement updating :", request()->all());

        return [
            'location_id'    => 'sometimes|required|integer|exists:locations,id',
            'camions'    => 'required|array',
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
            'location_id.required' => "La location est obligatoire.",
            'location_id.integer' => "L'identifiant de la location doit être un nombre.",
            'location_id.exists' => "La location sélectionnée est invalide.",

            'camions.required' => "Veuillez sélectionner au moins un camion.",
            'camions.array' => "Le format des camions est invalide.",

            'montant.required' => "Le montant est obligatoire.",
            'montant.numeric' => "Le montant doit être un nombre.",

            'preuve.file' => "Le fichier doit être valide.",
            'preuve.mimes' => "Le fichier doit être de type : pdf, png, jpg, jpeg, doc ou docx.",
            'preuve.max' => "Le fichier ne doit pas dépasser 5 Mo.",

            'reference.unique' => "Cette référence est déjà utilisée.",
        ];
    }
}
