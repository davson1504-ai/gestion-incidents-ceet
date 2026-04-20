<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IncidentFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'departement_id' => ['nullable', 'integer', 'exists:departements,id'],
            'type_incident_id' => ['nullable', 'integer', 'exists:type_incidents,id'],
            'cause_id' => ['nullable', 'integer', 'exists:causes,id'],
            'operateur_id' => ['nullable', 'integer', 'exists:users,id'],
            'status_id' => ['nullable', 'integer', 'exists:statuses,id'],
            'statut' => ['nullable', 'string', Rule::exists('statuses', 'code')],
            'q' => ['nullable', 'string', 'max:200'],
            'sort_by' => ['nullable', Rule::in(['date_debut', 'date_fin', 'duree_minutes', 'created_at', 'code_incident'])],
            'sort_dir' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
