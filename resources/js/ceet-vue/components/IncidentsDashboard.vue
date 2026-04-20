<template>
    <section class="card border-0 shadow-sm h-100">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">Incidents en cours</h5>
                <button class="btn btn-sm btn-outline-primary" @click="fetchData" :disabled="loading">Actualiser</button>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <div class="p-3 rounded-3 bg-light border">
                        <small class="text-muted d-block">Total incidents</small>
                        <strong class="fs-4">{{ overview.total_incidents }}</strong>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 bg-light border">
                        <small class="text-muted d-block">Ouverts</small>
                        <strong class="fs-4 text-danger">{{ overview.incidents_ouverts }}</strong>
                    </div>
                </div>
            </div>

            <div v-if="loading" class="text-center py-4">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
            <div v-else-if="errorMessage" class="alert alert-danger mb-0">{{ errorMessage }}</div>
            <div v-else-if="openIncidents.length === 0" class="alert alert-secondary mb-0">Aucun incident en cours.</div>
            <div v-else class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Titre</th>
                            <th>Departement</th>
                            <th>Priorite</th>
                            <th>Debut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="incident in openIncidents" :key="incident.id">
                            <td class="fw-semibold">{{ incident.code_incident }}</td>
                            <td>{{ incident.titre || 'Sans titre' }}</td>
                            <td>{{ incident.departement?.nom || '-' }}</td>
                            <td>{{ incident.priorite?.libelle || '-' }}</td>
                            <td>{{ formatDate(incident.date_debut) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import api, { unwrapPayload } from '../services/api';

const loading = ref(false);
const errorMessage = ref('');
const overview = ref({
    total_incidents: 0,
    duree_moyenne_minutes: 0,
    incidents_ouverts: 0,
    incidents_fermes: 0,
});
const openIncidents = ref([]);

const formatDate = (value) => {
    if (!value) {
        return '-';
    }

    const date = new Date(value);

    return Number.isNaN(date.getTime()) ? value : date.toLocaleString('fr-FR');
};

const fetchData = async () => {
    loading.value = true;
    errorMessage.value = '';

    try {
        const [overviewResp, incidentsResp] = await Promise.all([
            api.get('/reports/overview'),
            api.get('/incidents', { params: { sort_by: 'date_debut', sort_dir: 'desc', per_page: 15 } }),
        ]);

        overview.value = unwrapPayload(overviewResp);

        const list = unwrapPayload(incidentsResp).data ?? [];
        openIncidents.value = list.filter((incident) => !(incident.statut?.is_final ?? false));
    } catch (_error) {
        errorMessage.value = 'Impossible de recuperer le tableau de bord.';
    } finally {
        loading.value = false;
    }
};

onMounted(fetchData);
</script>
