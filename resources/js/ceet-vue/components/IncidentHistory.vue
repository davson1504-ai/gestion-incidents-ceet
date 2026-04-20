<template>
    <section class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-3">Historique des incidents</h5>

            <form class="row g-2 mb-3" @submit.prevent="fetchIncidents">
                <div class="col-12 col-md-3">
                    <label class="form-label">Date debut</label>
                    <input v-model="filters.date_from" type="date" class="form-control" />
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Date fin</label>
                    <input v-model="filters.date_to" type="date" class="form-control" />
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Departement</label>
                    <select v-model="filters.departement_id" class="form-select">
                        <option value="">Tous</option>
                        <option v-for="dep in departements" :key="dep.id" :value="dep.id">{{ dep.nom }}</option>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Recherche</label>
                    <input v-model="filters.q" class="form-control" placeholder="Code, titre, description" />
                </div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" @click="resetFilters">Reinitialiser</button>
                    <button type="submit" class="btn btn-primary" :disabled="loading">Filtrer</button>
                </div>
            </form>

            <div v-if="loading" class="text-center py-4">
                <div class="spinner-border" role="status"></div>
            </div>
            <div v-else-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
            <div v-else-if="incidents.length === 0" class="alert alert-secondary mb-0">Aucun incident trouve.</div>
            <div v-else class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Titre</th>
                            <th>Departement</th>
                            <th>Type</th>
                            <th>Cause</th>
                            <th>Statut</th>
                            <th>Debut</th>
                            <th>Duree</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="incident in incidents" :key="incident.id">
                            <td class="fw-semibold">{{ incident.code_incident }}</td>
                            <td>{{ incident.titre || 'Sans titre' }}</td>
                            <td>{{ incident.departement?.nom || '-' }}</td>
                            <td>{{ incident.type_incident?.libelle || '-' }}</td>
                            <td>{{ incident.cause?.libelle || '-' }}</td>
                            <td>{{ incident.statut?.libelle || '-' }}</td>
                            <td>{{ formatDate(incident.date_debut) }}</td>
                            <td>{{ incident.duree_minutes ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import api, { unwrapPayload } from '../services/api';

const loading = ref(false);
const errorMessage = ref('');
const incidents = ref([]);
const departements = ref([]);

const filters = reactive({
    date_from: '',
    date_to: '',
    departement_id: '',
    q: '',
});

const formatDate = (value) => {
    if (!value) {
        return '-';
    }

    const date = new Date(value);

    return Number.isNaN(date.getTime()) ? value : date.toLocaleString('fr-FR');
};

const resetFilters = () => {
    filters.date_from = '';
    filters.date_to = '';
    filters.departement_id = '';
    filters.q = '';
    fetchIncidents();
};

const fetchIncidents = async () => {
    loading.value = true;
    errorMessage.value = '';

    try {
        const response = await api.get('/incidents', {
            params: {
                ...filters,
                per_page: 50,
                sort_by: 'date_debut',
                sort_dir: 'desc',
            },
        });

        incidents.value = unwrapPayload(response).data ?? [];
    } catch (_error) {
        errorMessage.value = 'Impossible de charger l historique.';
    } finally {
        loading.value = false;
    }
};

const loadDepartements = async () => {
    const response = await api.get('/catalogues/departements', { params: { per_page: 200 } });
    departements.value = unwrapPayload(response).data ?? [];
};

onMounted(async () => {
    try {
        await Promise.all([loadDepartements(), fetchIncidents()]);
    } catch (_error) {
        errorMessage.value = 'Erreur de chargement initial.';
    }
});
</script>
