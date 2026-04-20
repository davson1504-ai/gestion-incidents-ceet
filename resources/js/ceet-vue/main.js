import { createApp } from 'vue';
import IncidentQuickForm from './components/IncidentQuickForm.vue';
import IncidentsDashboard from './components/IncidentsDashboard.vue';
import IncidentHistory from './components/IncidentHistory.vue';
import CataloguesManager from './components/CataloguesManager.vue';

const mounts = [
    { id: '#incident-quick-form-app', component: IncidentQuickForm },
    { id: '#incidents-dashboard-app', component: IncidentsDashboard },
    { id: '#incident-history-app', component: IncidentHistory },
    { id: '#catalogues-manager-app', component: CataloguesManager },
];

mounts.forEach(({ id, component }) => {
    const target = document.querySelector(id);

    if (target) {
        createApp(component).mount(target);
    }
});
