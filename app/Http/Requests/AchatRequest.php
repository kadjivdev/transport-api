<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AchatRequest extends FormRequest
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
        $id = $this->route('achat');

        return [
            'code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('achats', 'code')->ignore($id),
            ],
            'product_id' => 'required|integer|exists:products,id',
            'fournisseur_id' => 'required|integer|exists:fournisseurs,id',
            'camion_id' => 'required|integer|exists:camions,id',
            'qte' => 'required|numeric|min:0.01',
            'paiement_preuve' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'bordereau' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
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
            'code.string' => 'Le code doit être une chaîne de caractères.',
            'code.unique' => 'Ce code d\'achat existe déjà.',
            'product_id.required' => 'Le produit est obligatoire.',
            'product_id.integer' => 'Le produit sélectionné est invalide.',
            'product_id.exists' => 'Le produit sélectionné n\'existe pas.',
            'fournisseur_id.required' => 'Le fournisseur est obligatoire.',
            'fournisseur_id.integer' => 'Le fournisseur sélectionné est invalide.',
            'fournisseur_id.exists' => 'Le fournisseur sélectionné n\'existe pas.',
            'camion_id.required' => 'Le camion est obligatoire.',
            'camion_id.integer' => 'Le camion sélectionné est invalide.',
            'camion_id.exists' => 'Le camion sélectionné n\'existe pas.',
            'qte.required' => 'La quantité est obligatoire.',
            'qte.numeric' => 'La quantité doit être un nombre.',
            'qte.min' => 'La quantité doit être supérieure à 0.',
            // 'paiement_preuve.required' => 'La preuve de paiement est obligatoire.',
            'paiement_preuve.file' => 'La preuve de paiement doit être un fichier.',
            'paiement_preuve.mimes' => 'La preuve de paiement doit être un fichier PDF, JPG ou PNG.',
            'paiement_preuve.max' => 'La preuve de paiement ne doit pas dépasser 5 Mo.',
            // 'bordereau.required' => 'Le bordereau est obligatoire.',
            'bordereau.file' => 'Le bordereau doit être un fichier.',
            'bordereau.mimes' => 'Le bordereau doit être un fichier PDF, JPG ou PNG.',
            'bordereau.max' => 'Le bordereau ne doit pas dépasser 5 Mo.',
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
            'code' => 'code',
            'product_id' => 'produit',
            'fournisseur_id' => 'fournisseur',
            'camion_id' => 'camion',
            'qte' => 'quantité',
            'paiement_preuve' => 'preuve de paiement',
            'bordereau' => 'bordereau',
        ];
    }
}