<?php

namespace App\Http\Requests\Api;

use App\Models\Incident;
use Illuminate\Foundation\Http\FormRequest;

class AssignIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $incident = $this->route('incident');

        return $this->user() !== null && $incident instanceof Incident && $this->user()->can('assign', $incident);
    }

    public function rules(): array
    {
        return [
            'responsable_id' => ['required', 'integer', 'exists:users,id'],
            'superviseur_id' => ['nullable', 'integer', 'exists:users,id'],
            'commentaire' => ['nullable', 'string', 'max:500'],
        ];
    }
}
