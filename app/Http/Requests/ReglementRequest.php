<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            "vente_id" => "required|integer|exists:ventes,id",
            "montant" => "required|numeric|min:0.01",
            "observation" => "nullable|string",
            "document" => "nullable|file|mimes:pdf,png,jpg,jpeg,doc,docx|max:5120",
        ];
    }

    /**
     * Handle the messages
     */
    public function messages(): array
    {
        return [
            'vente_id.required' => "La vente est obligatoire.",
            'vente_id.integer' => "L'identifiant de la vente doit être un nombre.",
            'vente_id.exists' => "La vente sélectionnée est invalide.",
            'montant.required' => "Le montant est obligatoire.",
            'montant.numeric' => "Le montant doit être un nombre.",
            'montant.min' => "Le montant doit être supérieur à 0.",
            'document.file' => "Le fichier doit être valide.",
            'document.mimes' => "Le fichier doit être de type : pdf, png, jpg, jpeg, doc ou docx.",
            'document.max' => "Le fichier ne doit pas dépasser 5 Mo.",
        ];
    }

    public function attributes(): array
    {
        return [
            "vente_id" => "vente",
            "montant" => "montant",
            "observation" => "observation",
            "document" => "document",
        ];
    }
}
