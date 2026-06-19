<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Incident;
use Illuminate\Validation\Rule;

class StoreIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->can('create', Incident::class);
    }

    public function rules(): array
    {
        return [
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'departement_id' => ['required', 'exists:departements,id'],
            'type_incident_id' => ['required', 'exists:type_incidents,id'],
            'cause_id' => [
                'required',
                Rule::exists('causes', 'id')->where(function ($query) {
                    $query->where('type_incident_id', $this->input('type_incident_id'));
                }),
            ],
            'priorite_id' => ['required', 'exists:priorites,id'],
            'localisation' => ['required', 'string', 'max:255'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
            'responsable_id' => ['nullable', 'exists:users,id'],
            'superviseur_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
