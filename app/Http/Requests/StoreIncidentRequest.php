<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La vérification de rôle est déjà faite au niveau de la route.
        // On délègue uniquement à l'authentification ici.
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'titre'              => ['required', 'string', 'max:255'],
            'description'        => ['nullable', 'string'],
            'departement_id'     => ['required', 'exists:departements,id'],
            'type_incident_id'   => ['required', 'exists:type_incidents,id'],
            'cause_id'           => [
                'nullable',
                Rule::exists('causes', 'id')->where(function ($query) {
                    $query->where('type_incident_id', $this->input('type_incident_id'));
                }),
            ],
            'status_id'          => ['required', 'exists:statuses,id'],
            'priorite_id'        => ['required', 'exists:priorites,id'],
            'localisation'       => ['nullable', 'string', 'max:255'],
            'date_debut'         => ['required', 'date'],
            'date_fin'           => ['nullable', 'date', 'after_or_equal:date_debut'],
            'responsable_id'     => ['nullable', 'exists:users,id'],
            'superviseur_id'     => ['nullable', 'exists:users,id'],
            'actions_menees'     => ['nullable', 'string'],
            'resolution_summary' => ['nullable', 'string'],
        ];
    }
}
