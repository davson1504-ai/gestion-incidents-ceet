<?php

namespace App\Http\Requests\Api;

use App\Models\Incident;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreInterventionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $incident = $this->route('incident');

        return $this->user() !== null && $incident instanceof Incident && $this->user()->can('take', $incident);
    }

    public function rules(): array
    {
        return [
            'action_type' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string'],
            'started_at' => ['required', 'date'],
            'ended_at' => ['nullable', 'date'],
            'resultat' => ['nullable', 'string', 'max:190'],
            'statut' => ['nullable', 'string', 'max:80'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $startedAt = $this->input('started_at');
            $endedAt = $this->input('ended_at');

            if ($startedAt && $endedAt && strtotime((string) $endedAt) < strtotime((string) $startedAt)) {
                $validator->errors()->add('ended_at', 'La date de fin doit etre superieure ou egale a la date de debut.');
            }
        });
    }
}
