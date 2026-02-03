<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LocationRequest extends FormRequest
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
    public  function rules(): array
    {
        return [
            'client_id'           => 'required|integer|exists:clients,id',
            'location_type_id'    => 'required|integer|exists:location_types,id',
            'type_location_price' => 'required',
            'montant_total'       => 'nullable',
            'date_location'       => 'required|date',
            'contrat'             => 'required|file|mimes:pdf,doc,docx|max:5120', // max en Ko (5 Mo)
            "commentaire"         => "nullable",

            "reference"           => ["nullable", Rule::unique("locations", "reference")->ignore($this->route("location"))],

            // details
            "details"             => "nullable|array|min:1",

            "details.*.camion_id"  => "required|exists:camions,id",
            "details.*.price"  => "required",
        ];
    }

    /**
     * Handle the messages
     */
    public  function messages(): array
    {
        return [
            // reference
            'reference.unique' => 'Cette référence existe déjà.',

            // client_id
            'client_id.required' => 'Le client est obligatoire.',
            'client_id.integer'  => 'Le client doit être un identifiant valide.',
            'client_id.exists'   => 'Le client sélectionné est introuvable.',

            // location_type_id
            'location_type_id.required' => 'Le type de location est obligatoire.',
            'location_type_id.integer'  => 'Le type de location doit être un identifiant valide.',
            'location_type_id.exists'   => 'Le type de location sélectionné est introuvable.',

            // type_location_price
            'type_location_price.required' => 'Le prix du type de location est obligatoire.',
            'type_location_price.decimal'  => 'Le prix du type de location doit être un nombre décimal valide.',

            // montant_total
            // 'montant_total.required' => 'Le montant total est obligatoire.',
            'montant_total.decimal'  => 'Le montant total doit être un nombre décimal valide.',

            // date_location
            'date_location.required' => 'La date de location est obligatoire.',
            'date_location.date'     => 'La date de location doit être une date valide.',

            // contrat
            'contrat.required' => 'Le contrat est obligatoire.',
            'contrat.file'     => 'Le contrat doit être un fichier valide.',
            'contrat.mimes'    => 'Le contrat doit être au format PDF ou Word (doc, docx).',
            'contrat.max'      => 'Le contrat ne peut pas dépasser 5 Mo.',

            // details
            'details.required'        => 'Le champ détails est obligatoire.',
            'details.array'           => 'Le champ détails doit être un tableau.',
            'details.min'             => 'Vous devez ajouter au moins un détail.',

            'details.*.camion_id.required' => 'Le camion est obligatoire pour chaque détail.',
            'details.*.camion_id.exists'   => 'Le camion sélectionné est invalide.',

            'details.*.price.required' => 'Le prix est obligatoire pour chaque détail.',
            'details.*.price.decimal'  => 'Le prix doit être un nombre décimal valide.',
        ];
    }
}
