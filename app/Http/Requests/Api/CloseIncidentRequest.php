<?php

namespace App\Http\Requests\Api;

use App\Models\Incident;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CloseIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $incident = $this->route('incident');

        return $this->user() !== null && $incident instanceof Incident && $this->user()->can('close', $incident);
    }

    public function rules(): array
    {
        return [
            'date_fin' => ['nullable', 'date'],
            'resolution_summary' => ['required', 'string'],
            'resume_resolution' => ['nullable', 'string'],
            'actions_menees' => ['nullable', 'string'],
            'status_id' => [
                'nullable',
                Rule::exists('statuses', 'id')->where(fn ($query) => $query->where('is_final', true)),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var Incident|null $incident */
            $incident = $this->route('incident');
            $dateFin = $this->input('date_fin');

            if (! $incident || ! $incident->date_debut || ! $dateFin) {
                return;
            }

            if (strtotime((string) $dateFin) < $incident->date_debut->getTimestamp()) {
                $validator->errors()->add('date_fin', 'La date de fin doit etre superieure ou egale a la date de debut.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'status_id.exists' => 'Le statut de cloture doit etre un statut final.',
        ];
    }
}
