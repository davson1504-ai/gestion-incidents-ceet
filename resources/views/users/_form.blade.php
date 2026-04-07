@php
    $isEdit = isset($userToEdit);
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nom *</label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $userToEdit->name ?? '') }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Email *</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $userToEdit->email ?? '') }}" required>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Telephone</label>
        <input type="text" name="telephone" class="form-control @error('telephone') is-invalid @enderror"
               value="{{ old('telephone', $userToEdit->telephone ?? '') }}">
        @error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Departement</label>
        <select name="departement_id" class="form-select @error('departement_id') is-invalid @enderror">
            <option value="">-- Aucun --</option>
            @foreach($departements as $dep)
                <option value="{{ $dep->id }}" @selected((string) old('departement_id', $userToEdit->departement_id ?? '') === (string) $dep->id)>
                    {{ $dep->nom }}
                </option>
            @endforeach
        </select>
        @error('departement_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Role *</label>
        <select name="role" class="form-select @error('role') is-invalid @enderror" required>
            @foreach($roles as $role)
                <option value="{{ $role }}" @selected(old('role', $selectedRole ?? '') === $role)>{{ $role }}</option>
            @endforeach
        </select>
        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">{{ $isEdit ? 'Nouveau mot de passe' : 'Mot de passe *' }}</label>
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
               {{ $isEdit ? '' : 'required' }}>
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">{{ $isEdit ? 'Confirmer nouveau mot de passe' : 'Confirmation mot de passe *' }}</label>
        <input type="password" name="password_confirmation" class="form-control"
               {{ $isEdit ? '' : 'required' }}>
    </div>

    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                   {{ old('is_active', $userToEdit->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Actif</label>
        </div>
    </div>

    <div class="col-12">
        <button class="btn btn-primary" type="submit">{{ $isEdit ? 'Mettre a jour' : 'Creer' }}</button>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Annuler</a>
    </div>
</div>

