<template>
    <section class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="card-title mb-0">Saisie rapide incident</h5>
                <span class="badge text-bg-info">API v1</span>
            </div>

            <form @submit.prevent="submitIncident" class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label">Titre</label>
                    <input v-model="form.titre" class="form-control" placeholder="Ex: Coupure secteur Agoe" />
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Localisation</label>
                    <input v-model="form.localisation" class="form-control" placeholder="Poste / quartier" />
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Departement</label>
                    <select v-model="form.departement_id" class="form-select" required>
                        <option value="">Selectionner</option>
                        <option v-for="dep in departements" :key="dep.id" :value="dep.id">{{ dep.nom }}</option>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Type incident</label>
                    <select v-model="form.type_incident_id" class="form-select" required @change="loadCauses">
                        <option value="">Selectionner</option>
                        <option v-for="type in types" :key="type.id" :value="type.id">{{ type.libelle }}</option>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Cause</label>
                    <select v-model="form.cause_id" class="form-select">
                        <option value="">Non renseignee</option>
                        <option v-for="cause in causes" :key="cause.id" :value="cause.id">{{ cause.libelle }}</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea v-model="form.description" class="form-control" rows="2" placeholder="Symptomes, impact, contexte..."></textarea>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Date debut</label>
                    <input v-model="form.date_debut" type="datetime-local" class="form-control" required />
                </div>
                <div class="col-12 col-md-8 d-flex align-items-end justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" @click="resetForm" :disabled="submitting">Reinitialiser</button>
                    <button type="submit" class="btn btn-primary" :disabled="submitting">
                        <span v-if="submitting" class="spinner-border spinner-border-sm me-2" role="status"></span>
                        Enregistrer
                    </button>
                </div>
            </form>

            <div v-if="successMessage" class="alert alert-success mt-3 mb-0">{{ successMessage }}</div>
            <div v-if="errorMessage" class="alert alert-danger mt-3 mb-0">{{ errorMessage }}</div>
        </div>
    </section>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import api, { unwrapPayload } from '../services/api';

const departements = ref([]);
const types = ref([]);
const causes = ref([]);
const submitting = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

const form = reactive({
    titre: '',
    description: '',
    departement_id: '',
    type_incident_id: '',
    cause_id: '',
    localisation: '',
    date_debut: new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 16),
});

const resetForm = () => {
    form.titre = '';
    form.description = '';
    form.departement_id = '';
    form.type_incident_id = '';
    form.cause_id = '';
    form.localisation = '';
    form.date_debut = new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    causes.value = [];
};

const loadCatalogues = async () => {
    const [depsResp, typesResp] = await Promise.all([
        api.get('/catalogues/departements', { params: { per_page: 200, is_active: 1 } }),
        api.get('/catalogues/types-incidents', { params: { per_page: 200, is_active: 1 } }),
    ]);

    departements.value = unwrapPayload(depsResp).data ?? [];
    types.value = unwrapPayload(typesResp).data ?? [];
};

const loadCauses = async () => {
    form.cause_id = '';

    if (!form.type_incident_id) {
        causes.value = [];
        return;
    }

    const response = await api.get('/catalogues/causes', {
        params: {
            type_incident_id: form.type_incident_id,
            is_active: 1,
            per_page: 200,
        },
    });

    causes.value = unwrapPayload(response).data ?? [];
};

const submitIncident = async () => {
    submitting.value = true;
    successMessage.value = '';
    errorMessage.value = '';

    try {
        const payload = {
            ...form,
            cause_id: form.cause_id || null,
            date_debut: form.date_debut.replace('T', ' '),
        };

        await api.post('/incidents', payload);
        successMessage.value = 'Incident enregistre avec succes.';
        resetForm();
    } catch (error) {
        errorMessage.value = error?.response?.data?.message ?? 'Echec lors de la creation de l incident.';
    } finally {
        submitting.value = false;
    }
};

onMounted(async () => {
    try {
        await loadCatalogues();
    } catch (_error) {
        errorMessage.value = 'Impossible de charger les catalogues.';
    }
});
</script>
