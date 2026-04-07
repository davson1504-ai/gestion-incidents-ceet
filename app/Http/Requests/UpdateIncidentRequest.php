<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'titre'              => ['required', 'string', 'max:255'],
            'description'        => ['nullable', 'string'],
            'departement_id'     => ['required', 'exists:departements,id'],
            'type_incident_id'   => ['required', 'exists:type_incidents,id'],
            'cause_id'           => ['nullable', 'exists:causes,id'],
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