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
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var Incident|null $incident */
            $incident = $this->route('incident');
            $dateFin = $this->input('date_fin');

            if (! $incident) {
                return;
            }

            $incident->loadMissing(['status', 'report']);

            if ($incident->status?->code !== 'VALIDE') {
                $validator->errors()->add('status', 'La cloture exige un incident au statut VALIDE.');
            }

            if (! $incident->report) {
                $validator->errors()->add('rapport', 'La cloture est impossible sans rapport d intervention.');
            } elseif ($incident->report->statut_rapport !== \App\Models\IncidentReport::STATUS_VALIDATED) {
                $validator->errors()->add('rapport', 'La cloture exige un rapport d intervention valide.');
            }

            if (! $incident->date_debut || ! $dateFin) {
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
            'status' => 'La cloture exige un incident au statut VALIDE.',
        ];
    }
}
