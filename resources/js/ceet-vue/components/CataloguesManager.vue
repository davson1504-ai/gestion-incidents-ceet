<template>
    <section class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-3">Gestion des catalogues</h5>

            <ul class="nav nav-pills mb-3">
                <li class="nav-item"><button class="nav-link" :class="{ active: tab === 'departements' }" @click="tab = 'departements'">Departements</button></li>
                <li class="nav-item"><button class="nav-link" :class="{ active: tab === 'types' }" @click="tab = 'types'">Types</button></li>
                <li class="nav-item"><button class="nav-link" :class="{ active: tab === 'causes' }" @click="tab = 'causes'">Causes</button></li>
            </ul>

            <div class="row g-2 mb-3" v-if="tab === 'departements'">
                <div class="col-12 col-md-4"><input v-model="forms.departement.code" class="form-control" placeholder="Code" /></div>
                <div class="col-12 col-md-4"><input v-model="forms.departement.nom" class="form-control" placeholder="Nom" /></div>
                <div class="col-12 col-md-3"><input v-model="forms.departement.zone" class="form-control" placeholder="Zone" /></div>
                <div class="col-12 col-md-1 d-grid"><button class="btn btn-primary" @click="createDepartement">+</button></div>
            </div>

            <div class="row g-2 mb-3" v-if="tab === 'types'">
                <div class="col-12 col-md-4"><input v-model="forms.type.code" class="form-control" placeholder="Code" /></div>
                <div class="col-12 col-md-7"><input v-model="forms.type.libelle" class="form-control" placeholder="Libelle" /></div>
                <div class="col-12 col-md-1 d-grid"><button class="btn btn-primary" @click="createType">+</button></div>
            </div>

            <div class="row g-2 mb-3" v-if="tab === 'causes'">
                <div class="col-12 col-md-3">
                    <select v-model="forms.cause.type_incident_id" class="form-select">
                        <option value="">Type</option>
                        <option v-for="type in types" :key="type.id" :value="type.id">{{ type.libelle }}</option>
                    </select>
                </div>
                <div class="col-12 col-md-3"><input v-model="forms.cause.code" class="form-control" placeholder="Code" /></div>
                <div class="col-12 col-md-5"><input v-model="forms.cause.libelle" class="form-control" placeholder="Libelle" /></div>
                <div class="col-12 col-md-1 d-grid"><button class="btn btn-primary" @click="createCause">+</button></div>
            </div>

            <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>

            <div class="table-responsive" v-if="tab === 'departements'">
                <table class="table table-sm table-striped mb-0">
                    <thead><tr><th>Code</th><th>Nom</th><th>Zone</th></tr></thead>
                    <tbody><tr v-for="dep in departements" :key="dep.id"><td>{{ dep.code }}</td><td>{{ dep.nom }}</td><td>{{ dep.zone || '-' }}</td></tr></tbody>
                </table>
            </div>

            <div class="table-responsive" v-if="tab === 'types'">
                <table class="table table-sm table-striped mb-0">
                    <thead><tr><th>Code</th><th>Libelle</th></tr></thead>
                    <tbody><tr v-for="type in types" :key="type.id"><td>{{ type.code || '-' }}</td><td>{{ type.libelle }}</td></tr></tbody>
                </table>
            </div>

            <div class="table-responsive" v-if="tab === 'causes'">
                <table class="table table-sm table-striped mb-0">
                    <thead><tr><th>Code</th><th>Libelle</th><th>Type</th></tr></thead>
                    <tbody><tr v-for="cause in causes" :key="cause.id"><td>{{ cause.code || '-' }}</td><td>{{ cause.libelle }}</td><td>{{ cause.type_incident?.libelle || '-' }}</td></tr></tbody>
                </table>
            </div>
        </div>
    </section>
</template>

<script setup>
import { onMounted, reactive, ref, watch } from 'vue';
import api, { unwrapPayload } from '../services/api';

const tab = ref('departements');
const errorMessage = ref('');
const departements = ref([]);
const types = ref([]);
const causes = ref([]);

const forms = reactive({
    departement: { code: '', nom: '', zone: '' },
    type: { code: '', libelle: '' },
    cause: { type_incident_id: '', code: '', libelle: '' },
});

const loadAll = async () => {
    const [depResp, typeResp, causeResp] = await Promise.all([
        api.get('/catalogues/departements', { params: { per_page: 200 } }),
        api.get('/catalogues/types-incidents', { params: { per_page: 200 } }),
        api.get('/catalogues/causes', { params: { per_page: 200 } }),
    ]);

    departements.value = unwrapPayload(depResp).data ?? [];
    types.value = unwrapPayload(typeResp).data ?? [];
    causes.value = unwrapPayload(causeResp).data ?? [];
};

const createDepartement = async () => {
    if (!forms.departement.code || !forms.departement.nom) {
        errorMessage.value = 'Code et nom departement sont obligatoires.';
        return;
    }

    await api.post('/catalogues/departements', forms.departement);
    forms.departement = { code: '', nom: '', zone: '' };
    await loadAll();
};

const createType = async () => {
    if (!forms.type.libelle) {
        errorMessage.value = 'Le libelle du type est obligatoire.';
        return;
    }

    await api.post('/catalogues/types-incidents', forms.type);
    forms.type = { code: '', libelle: '' };
    await loadAll();
};

const createCause = async () => {
    if (!forms.cause.type_incident_id || !forms.cause.libelle) {
        errorMessage.value = 'Le type et le libelle de la cause sont obligatoires.';
        return;
    }

    await api.post('/catalogues/causes', forms.cause);
    forms.cause = { type_incident_id: '', code: '', libelle: '' };
    await loadAll();
};

watch(tab, () => {
    errorMessage.value = '';
});

onMounted(async () => {
    try {
        await loadAll();
    } catch (_error) {
        errorMessage.value = 'Impossible de charger les catalogues.';
    }
});
</script>
