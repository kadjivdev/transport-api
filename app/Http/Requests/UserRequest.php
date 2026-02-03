<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
        $userId = $this->route('user'); // ou auth()->id() selon ton cas

        return [
            "name" => "sometimes|required|string|max:255",
            // "email" => "sometimes|required|email|max:255|unique:users,email",
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            "password" => "sometimes|required|string|min:8|confirmed", // "confirmed" pour password_confirmation
        ];
    }

    public function messages(): array
    {
        return [
            "name.required" => "Le nom est obligatoire.",
            "name.string" => "Le nom doit être une chaîne de caractères.",
            "name.max" => "Le nom ne peut pas dépasser 255 caractères.",

            "email.required" => "L'adresse email est obligatoire.",
            "email.email" => "Veuillez entrer une adresse email valide.",
            "email.max" => "L'adresse email ne peut pas dépasser 255 caractères.",
            "email.unique" => "Cette adresse email est déjà utilisée.",

            "password.required" => "Le mot de passe est obligatoire.",
            "password.string" => "Le mot de passe doit être une chaîne de caractères.",
            "password.min" => "Le mot de passe doit contenir au moins 8 caractères.",
            "password.confirmed" => "La confirmation du mot de passe ne correspond pas.",
        ];
    }
}
