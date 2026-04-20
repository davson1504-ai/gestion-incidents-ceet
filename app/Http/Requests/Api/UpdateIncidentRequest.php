<?php

namespace App\Http\Requests\Api;

use App\Models\Incident;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $incident = $this->route('incident');

        return $this->user() !== null && $incident instanceof Incident && $this->user()->can('update', $incident);
    }

    public function rules(): array
    {
        $incident = $this->route('incident');
        $typeIncidentId = (int) ($this->input('type_incident_id') ?: $incident?->type_incident_id);

        return [
            'titre' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'departement_id' => ['sometimes', 'integer', 'exists:departements,id'],
            'type_incident_id' => ['sometimes', 'integer', 'exists:type_incidents,id'],
            'cause_id' => [
                'sometimes',
                'nullable',
                Rule::exists('causes', 'id')->where(fn ($query) => $query->where('type_incident_id', $typeIncidentId)),
            ],
            'status_id' => ['sometimes', 'integer', 'exists:statuses,id'],
            'priorite_id' => ['sometimes', 'integer', 'exists:priorites,id'],
            'localisation' => ['sometimes', 'nullable', 'string', 'max:255'],
            'date_debut' => ['sometimes', 'date'],
            'date_fin' => ['sometimes', 'nullable', 'date', 'after_or_equal:date_debut'],
            'responsable_id' => ['sometimes', 'nullable', 'exists:users,id'],
            'superviseur_id' => ['sometimes', 'nullable', 'exists:users,id'],
            'actions_menees' => ['sometimes', 'nullable', 'string'],
            'resolution_summary' => ['sometimes', 'nullable', 'string'],
            'resume_resolution' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
