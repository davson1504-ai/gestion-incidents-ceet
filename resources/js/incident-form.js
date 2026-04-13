const initIncidentForm = () => {
    const form = document.querySelector('[data-incident-form]');

    if (!form) {
        return;
    }

    const typeSelect = form.querySelector('#incident-type-select');
    const causeSelect = form.querySelector('#incident-cause-select');
    const statusSelect = form.querySelector('#incident-status-select');
    const dateDebutInput = form.querySelector('#incident-date-debut');
    const dateFinInput = form.querySelector('#incident-date-fin');
    const durationPreview = form.querySelector('#incident-duration-preview');
    const causeLoadingIndicator = form.querySelector('#incident-cause-loading');

    if (!typeSelect || !causeSelect || !statusSelect || !dateDebutInput || !dateFinInput || !durationPreview) {
        return;
    }

    const allCauses = JSON.parse(causeSelect.dataset.allCauses || '[]');
    const finalIds = new Set((JSON.parse(statusSelect.dataset.finalIds || '[]')).map(String));
    const causesUrlTemplate = typeSelect.dataset.causesUrl || '';

    const nowAsLocalInput = () => {
        const value = new Date();
        const pad = (number) => String(number).padStart(2, '0');

        return `${value.getFullYear()}-${pad(value.getMonth() + 1)}-${pad(value.getDate())}T${pad(value.getHours())}:${pad(value.getMinutes())}`;
    };

    const setLoading = (loading) => {
        causeSelect.disabled = loading;

        if (causeSelect.tomselect) {
            if (loading) {
                causeSelect.tomselect.disable();
            } else {
                causeSelect.tomselect.enable();
            }
        }

        if (causeLoadingIndicator) {
            causeLoadingIndicator.classList.toggle('d-none', !loading);
        }
    };

    const hydrateCauseOptions = (causes, selectedId = '') => {
        const options = [{ id: '', libelle: 'Aucune' }, ...causes];

        if (causeSelect.tomselect) {
            const control = causeSelect.tomselect;
            control.clear(true);
            control.clearOptions();
            control.addOptions(options.map((item) => ({ value: String(item.id), text: item.libelle })));
            control.refreshOptions(false);
            control.setValue(String(selectedId || ''), true);
            return;
        }

        causeSelect.innerHTML = '';
        options.forEach((item) => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.libelle;
            option.selected = String(item.id) === String(selectedId || '');
            causeSelect.appendChild(option);
        });
    };

    const loadCauses = async (typeId, selectedId = '') => {
        if (!typeId) {
            hydrateCauseOptions(allCauses, selectedId);
            setLoading(false);
            return;
        }

        setLoading(true);
        hydrateCauseOptions([{ id: '', libelle: 'Chargement...' }]);

        try {
            const response = await fetch(
                causesUrlTemplate.replace('__TYPE__', encodeURIComponent(typeId)),
                {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                    },
                },
            );

            if (!response.ok) {
                throw new Error('Impossible de charger les causes.');
            }

            const causes = await response.json();
            hydrateCauseOptions(causes, selectedId);
        } catch (_error) {
            hydrateCauseOptions([], '');
        } finally {
            setLoading(false);
        }
    };

    const refreshDuration = () => {
        if (!dateDebutInput.value || !dateFinInput.value) {
            durationPreview.textContent = 'Durée estimée : -';
            return;
        }

        const start = new Date(dateDebutInput.value);
        const end = new Date(dateFinInput.value);

        if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime()) || end < start) {
            durationPreview.textContent = 'Durée estimée : -';
            return;
        }

        const minutes = Math.floor((end.getTime() - start.getTime()) / 60000);
        const hours = Math.floor(minutes / 60);
        const remainingMinutes = minutes % 60;

        durationPreview.textContent = `Durée estimée : ${hours}h ${String(remainingMinutes).padStart(2, '0')}min`;
    };

    const applyStatusBehaviour = () => {
        const isFinal = finalIds.has(String(statusSelect.value));

        if (isFinal) {
            if (!dateFinInput.value) {
                dateFinInput.value = nowAsLocalInput();
            }

            dateFinInput.disabled = true;
            dateFinInput.readOnly = true;
            refreshDuration();
            return;
        }

        dateFinInput.disabled = false;
        dateFinInput.readOnly = false;
        dateFinInput.value = '';
        refreshDuration();
    };

    typeSelect.addEventListener('change', () => {
        loadCauses(typeSelect.value, '');
    });

    statusSelect.addEventListener('change', applyStatusBehaviour);
    dateDebutInput.addEventListener('input', refreshDuration);
    dateFinInput.addEventListener('input', refreshDuration);
    dateDebutInput.addEventListener('change', refreshDuration);
    dateFinInput.addEventListener('change', refreshDuration);

    form.addEventListener('submit', (event) => {
        const isFinal = finalIds.has(String(statusSelect.value));

        if (isFinal && !window.confirm('Vous allez clôturer cet incident. Cette action est irréversible. Confirmer ?')) {
            event.preventDefault();
            return;
        }

        if (isFinal) {
            if (!dateFinInput.value) {
                dateFinInput.value = nowAsLocalInput();
            }

            dateFinInput.disabled = false;
            dateFinInput.readOnly = false;
        }
    });

    const initialCauseId = causeSelect.dataset.selectedCause || causeSelect.value || '';
    if (typeSelect.value) {
        loadCauses(typeSelect.value, initialCauseId);
    } else {
        hydrateCauseOptions(allCauses, initialCauseId);
        setLoading(false);
    }

    applyStatusBehaviour();
    refreshDuration();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initIncidentForm);
} else {
    initIncidentForm();
}
