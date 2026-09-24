<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VenteRequest extends FormRequest
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
            'achat_id' => 'nullable|integer|exists:achats,id',
            'client_id' => 'nullable|integer|exists:clients,id',
            'camion_id' => 'nullable|integer|exists:camions,id',
            'qte' => 'required|numeric|min:0.01',
            'unite_price' => 'required|numeric|min:0',
            'lieu_dechargement' => 'nullable|string',
            'observation' => 'nullable|string',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
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
            'achat_id.integer' => 'L\'achat sélectionné est invalide.',
            'achat_id.exists' => 'L\'achat sélectionné n\'existe pas.',
            'client_id.integer' => 'Le client sélectionné est invalide.',
            'client_id.exists' => 'Le client sélectionné n\'existe pas.',
            'camion_id.integer' => 'Le camion sélectionné est invalide.',
            'camion_id.exists' => 'Le camion sélectionné n\'existe pas.',
            'qte.required' => 'La quantité est obligatoire.',
            'qte.numeric' => 'La quantité doit être un nombre.',
            'qte.min' => 'La quantité doit être supérieure à 0.',
            'unite_price.required' => 'Le prix unitaire est obligatoire.',
            'unite_price.numeric' => 'Le prix unitaire doit être un nombre.',
            'unite_price.min' => 'Le prix unitaire ne peut pas être négatif.',
            'document.file' => 'Le document doit être un fichier.',
            'document.mimes' => 'Le document doit être un fichier PDF, JPG ou PNG.',
            'document.max' => 'Le document ne doit pas dépasser 5 Mo.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'achat_id' => 'achat',
            'client_id' => 'client',
            'camion_id' => 'camion',
            'qte' => 'quantité',
            'unite_price' => 'prix unitaire',
            'lieu_dechargement' => 'lieu de déchargement',
            'observation' => 'observation',
            'document' => 'document',
        ];
    }
}
