(() => {
    const page = document.querySelector('[data-admin-dashboard]');

    if (!page) {
        return;
    }

    const body = document.body;
    const sidebar = page.querySelector('[data-dashboard-sidebar]');
    const toggleButton = page.querySelector('[data-dashboard-sidebar-toggle]');
    const overlay = page.querySelector('[data-dashboard-overlay]');
    const searchInput = page.querySelector('.ceet-admin-search input');

    const openSidebar = () => {
        body.classList.add('ceet-dashboard-sidebar-open');
        toggleButton?.setAttribute('aria-expanded', 'true');
    };

    const closeSidebar = () => {
        body.classList.remove('ceet-dashboard-sidebar-open');
        toggleButton?.setAttribute('aria-expanded', 'false');
    };

    toggleButton?.addEventListener('click', () => {
        if (body.classList.contains('ceet-dashboard-sidebar-open')) {
            closeSidebar();
            return;
        }

        openSidebar();
    });

    overlay?.addEventListener('click', closeSidebar);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeSidebar();
        }

        const isSearchShortcut = (event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k';

        if (isSearchShortcut && searchInput) {
            event.preventDefault();
            searchInput.focus();
        }
    });

    sidebar?.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 992) {
                closeSidebar();
            }
        });
    });
})();
/* Page 12 - Déclarer un incident
   À ajouter à la fin de resources/js/pages/admin-dashboard.js */

(() => {
    const page = document.querySelector('[data-incident-create-page]');

    if (!page) {
        return;
    }

    const form = page.querySelector('[data-incident-create-form]');

    if (!form) {
        return;
    }

    const typeSelect = form.querySelector('[data-type-select]');
    const causeSelect = form.querySelector('[data-cause-select]');
    const dateInput = form.querySelector('[data-date-debut-date]');
    const timeInput = form.querySelector('[data-date-debut-time]');
    const combinedDateInput = form.querySelector('[data-date-debut-combined]');
    const submitButton = form.querySelector('[data-submit-button]');
    const priorityOptions = Array.from(form.querySelectorAll('[data-priority-option]'));

    const syncDateDebut = () => {
        if (!dateInput || !timeInput || !combinedDateInput) {
            return;
        }

        const dateValue = dateInput.value;
        const timeValue = timeInput.value || '00:00';

        combinedDateInput.value = dateValue ? `${dateValue} ${timeValue}` : '';
    };

    const updatePrioritySelection = () => {
        priorityOptions.forEach((option) => {
            const input = option.querySelector('input[type="radio"]');
            option.classList.toggle('is-selected', Boolean(input?.checked));
        });
    };

    const filterCausesByType = () => {
        if (!typeSelect || !causeSelect) {
            return;
        }

        const selectedTypeId = typeSelect.value;
        let selectedCauseIsVisible = false;

        Array.from(causeSelect.options).forEach((option) => {
            if (!option.value) {
                option.hidden = false;
                option.disabled = false;
                return;
            }

            const optionTypeId = option.dataset.typeId;
            const isVisible = !selectedTypeId || optionTypeId === selectedTypeId;

            option.hidden = !isVisible;
            option.disabled = !isVisible;

            if (isVisible && option.selected) {
                selectedCauseIsVisible = true;
            }
        });

        if (!selectedCauseIsVisible) {
            causeSelect.value = '';
        }
    };

    priorityOptions.forEach((option) => {
        option.addEventListener('click', () => {
            const input = option.querySelector('input[type="radio"]');

            if (input) {
                input.checked = true;
                updatePrioritySelection();
            }
        });
    });

    typeSelect?.addEventListener('change', filterCausesByType);
    dateInput?.addEventListener('change', syncDateDebut);
    timeInput?.addEventListener('change', syncDateDebut);

    form.addEventListener('submit', () => {
        syncDateDebut();

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'Enregistrement...';
        }
    });

    filterCausesByType();
    syncDateDebut();
    updatePrioritySelection();
})();

/* Page 13 - Détail incident
   À ajouter à la fin de resources/js/pages/admin-dashboard.js */

(() => {
    const page = document.querySelector('[data-incident-show-page]');

    if (!page) {
        return;
    }

    page.querySelectorAll('[data-print-incident]').forEach((button) => {
        button.addEventListener('click', () => window.print());
    });

    const quickForm = page.querySelector('[data-quick-intervention-form]');
    const quickToggle = page.querySelector('[data-quick-intervention-toggle]');

    quickToggle?.addEventListener('click', () => {
        if (!quickForm) {
            return;
        }

        quickForm.hidden = !quickForm.hidden;
        quickForm.querySelector('textarea')?.focus();
    });

    page.querySelectorAll('[data-intervention-detail]').forEach((button) => {
        button.addEventListener('click', () => {
            const row = document.getElementById(button.dataset.interventionDetail);

            if (row) {
                row.hidden = !row.hidden;
            }
        });
    });
})();
/* Page 14 - Création utilisateur */

(() => {
    const page = document.querySelector('[data-user-create-page]');

    if (!page) {
        return;
    }

    const form = page.querySelector('[data-user-create-form]');
    const submitButton = page.querySelector('[data-user-create-submit]');
    const passwordInput = page.querySelector('[data-password-input]');
    const passwordToggle = page.querySelector('[data-password-toggle]');
    const departementSelect = page.querySelector('[data-departement-select]');

    const previewLabel = page.querySelector('[data-departement-preview-label]');
    const previewZone = page.querySelector('[data-departement-preview-zone]');
    const previewZoneLine = page.querySelector('[data-departement-preview-zone-line]');
    const previewPoste = page.querySelector('[data-departement-preview-poste]');

    passwordToggle?.addEventListener('click', () => {
        if (!passwordInput) {
            return;
        }

        const shouldShow = passwordInput.type === 'password';
        passwordInput.type = shouldShow ? 'text' : 'password';

        const icon = passwordToggle.querySelector('.material-symbols-outlined');
        if (icon) {
            icon.textContent = shouldShow ? 'visibility_off' : 'visibility';
        }
    });

    const updateDepartementPreview = () => {
        if (!departementSelect) {
            return;
        }

        const option = departementSelect.selectedOptions[0];

        const label = option?.dataset.label || 'Aperçu du secteur sélectionné';
        const zone = option?.dataset.zone || 'Sélectionnez un département pour afficher son secteur.';
        const poste = option?.dataset.poste || 'N/A';

        if (previewLabel) {
            previewLabel.textContent = label;
        }

        if (previewZone) {
            previewZone.textContent = zone;
        }

        if (previewZoneLine) {
            previewZoneLine.textContent = zone;
        }

        if (previewPoste) {
            previewPoste.textContent = poste;
        }
    };

    departementSelect?.addEventListener('change', updateDepartementPreview);
    updateDepartementPreview();

    form?.addEventListener('submit', () => {
        if (!submitButton) {
            return;
        }

        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="material-symbols-outlined">progress_activity</span> Enregistrement...';
    });
})();
/* Page 15 - Vue console réseau */

(() => {
    const page = document.querySelector('[data-vue-console-page]');

    if (!page) {
        return;
    }

    const clock = page.querySelector('[data-console-clock]');
    const elapsedItems = page.querySelectorAll('[data-incident-elapsed]');

    const pad = (value) => String(value).padStart(2, '0');

    const updateClock = () => {
        if (!clock) {
            return;
        }

        const now = new Date();
        clock.textContent = `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
    };

    const formatElapsed = (minutes) => {
        if (!Number.isFinite(minutes) || minutes < 0) {
            return 'N/A';
        }

        if (minutes < 60) {
            return `${minutes} min`;
        }

        const hours = Math.floor(minutes / 60);
        const rest = minutes % 60;

        return rest > 0 ? `${hours} h ${rest} min` : `${hours} h`;
    };

    const updateElapsedDurations = () => {
        const now = Date.now();

        elapsedItems.forEach((item) => {
            const startedAt = item.dataset.startedAt;

            if (!startedAt) {
                return;
            }

            const startedAtTime = new Date(startedAt).getTime();

            if (Number.isNaN(startedAtTime)) {
                return;
            }

            const minutes = Math.floor((now - startedAtTime) / 60000);
            item.textContent = formatElapsed(minutes);
        });
    };

    updateClock();
    updateElapsedDurations();

    window.setInterval(updateClock, 1000);
    window.setInterval(updateElapsedDurations, 30000);
})();
/* Page 17 - Historique du système */

(() => {
    const page = document.querySelector('[data-history-page]');

    if (!page) {
        return;
    }

    page.querySelectorAll('[data-history-detail-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const row = document.getElementById(button.dataset.historyDetailToggle);

            if (!row) {
                return;
            }

            row.hidden = !row.hidden;
        });
    });
})();
/* Page 17 - Historique du système */

(() => {
    const page = document.querySelector('[data-history-page]');

    if (!page) {
        return;
    }

    page.querySelectorAll('[data-history-detail-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const row = document.getElementById(button.dataset.historyDetailToggle);

            if (!row) {
                return;
            }

            row.hidden = !row.hidden;
        });
    });
})()
/* Page 18 - État technique du système
   À ajouter à la fin de resources/js/pages/admin-dashboard.js */

(() => {
    const page = document.querySelector('[data-system-status-page]');

    if (!page) {
        return;
    }

    page.querySelectorAll('[data-system-log-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const row = document.getElementById(button.dataset.systemLogToggle);

            if (!row) {
                return;
            }

            row.hidden = !row.hidden;
        });
    });
})();
/* Page 20 - Profil utilisateur
   À ajouter à la fin de resources/js/pages/admin-dashboard.js */

(() => {
    const page = document.querySelector('[data-profile-page]');

    if (!page) {
        return;
    }

    const infoForm = page.querySelector('[data-profile-info-form]');
    const firstNameInput = page.querySelector('[data-profile-first-name]');
    const lastNameInput = page.querySelector('[data-profile-last-name]');
    const fullNameInput = page.querySelector('[data-profile-full-name]');
    const avatarPreview = page.querySelector('[data-profile-avatar-preview]');
    const infoSubmit = page.querySelector('[data-profile-info-submit]');
    const passwordForm = page.querySelector('[data-profile-password-form]');
    const passwordSubmit = page.querySelector('[data-profile-password-submit]');
    const avatarNoteButton = page.querySelector('[data-avatar-note]');
    const avatarNoteTarget = page.querySelector('[data-avatar-note-target]');

    const buildInitials = (firstName, lastName) => {
        const parts = [firstName, lastName]
            .map((value) => String(value || '').trim())
            .filter(Boolean);

        if (parts.length === 0) {
            return 'US';
        }

        return parts
            .map((part) => part.charAt(0))
            .join('')
            .slice(0, 2)
            .toUpperCase();
    };

    const syncIdentity = () => {
        const firstName = firstNameInput?.value || '';
        const lastName = lastNameInput?.value || '';
        const fullName = `${firstName} ${lastName}`.trim();

        if (fullNameInput) {
            fullNameInput.value = fullName;
        }

        if (avatarPreview) {
            avatarPreview.textContent = buildInitials(firstName, lastName);
        }
    };

    firstNameInput?.addEventListener('input', syncIdentity);
    lastNameInput?.addEventListener('input', syncIdentity);
    syncIdentity();

    avatarNoteButton?.addEventListener('click', () => {
        if (!avatarNoteTarget) {
            return;
        }

        avatarNoteTarget.hidden = !avatarNoteTarget.hidden;
    });

    page.querySelectorAll('[data-profile-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = button.closest('.ceet-profile-password-control')?.querySelector('input');

            if (!input) {
                return;
            }

            const shouldShow = input.type === 'password';
            input.type = shouldShow ? 'text' : 'password';

            const icon = button.querySelector('.material-symbols-outlined');
            if (icon) {
                icon.textContent = shouldShow ? 'visibility_off' : 'visibility';
            }
        });
    });

    infoForm?.addEventListener('submit', () => {
        syncIdentity();

        if (infoSubmit) {
            infoSubmit.disabled = true;
            infoSubmit.textContent = 'Enregistrement...';
        }
    });

    passwordForm?.addEventListener('submit', () => {
        if (passwordSubmit) {
            passwordSubmit.disabled = true;
            passwordSubmit.textContent = 'Mise à jour...';
        }
    });
})();
/* Page 23 - Catalogues techniques
   À ajouter à la fin de resources/js/pages/admin-dashboard.js */

(() => {
    const page = document.querySelector('[data-catalogues-page]');

    if (!page) {
        return;
    }

    const searchInput = page.querySelector('[data-catalogue-search]');
    const cards = [...page.querySelectorAll('[data-catalogue-card]')];
    const emptyState = page.querySelector('[data-catalogue-empty]');
    const createMenu = page.querySelector('[data-catalogue-create-menu]');
    const createToggle = page.querySelector('[data-catalogue-create-toggle]');
    const createPanel = page.querySelector('[data-catalogue-create-panel]');
    const printButton = page.querySelector('[data-catalogue-print]');

    const normalize = (value) => String(value || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');

    const filterCards = () => {
        const query = normalize(searchInput?.value || '');
        let visibleCount = 0;

        cards.forEach((card) => {
            const content = normalize(card.dataset.catalogueKeywords || card.textContent || '');
            const isVisible = !query || content.includes(query);

            card.classList.toggle('is-hidden', !isVisible);

            if (isVisible) {
                visibleCount += 1;
            }
        });

        if (emptyState) {
            emptyState.hidden = visibleCount > 0;
        }
    };

    searchInput?.addEventListener('input', filterCards);
    filterCards();

    const closeCreatePanel = () => {
        if (!createPanel || !createToggle) {
            return;
        }

        createPanel.hidden = true;
        createToggle.setAttribute('aria-expanded', 'false');
    };

    createToggle?.addEventListener('click', () => {
        if (!createPanel) {
            return;
        }

        const shouldOpen = createPanel.hidden;
        createPanel.hidden = !shouldOpen;
        createToggle.setAttribute('aria-expanded', String(shouldOpen));
    });

    document.addEventListener('click', (event) => {
        if (!createMenu || createMenu.contains(event.target)) {
            return;
        }

        closeCreatePanel();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeCreatePanel();
        }
    });

    printButton?.addEventListener('click', (event) => {
        event.preventDefault();
        window.print();
    });
})();
/* Page 25 - Nouveau départ électrique
   À ajouter à la fin de resources/js/pages/admin-dashboard.js */

(() => {
    const page = document.querySelector('[data-depart-create-page]');

    if (!page) {
        return;
    }

    const form = page.querySelector('[data-depart-create-form]');
    const activeToggle = page.querySelector('[data-depart-active-toggle]');
    const activeLabel = page.querySelector('[data-depart-active-label]');
    const sessionClock = page.querySelector('[data-depart-session-clock]');
    const resetButton = page.querySelector('[data-depart-reset]');
    const submitButtons = [
        page.querySelector('[data-depart-submit-top]'),
        page.querySelector('[data-depart-submit-bottom]'),
    ].filter(Boolean);

    const pad = (value) => String(value).padStart(2, '0');

    const updateClock = () => {
        if (!sessionClock) {
            return;
        }

        const now = new Date();
        sessionClock.textContent = `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
    };

    const updateActiveLabel = () => {
        if (!activeToggle || !activeLabel) {
            return;
        }

        activeLabel.textContent = activeToggle.checked ? 'Statut actif' : 'Statut inactif';
    };

    activeToggle?.addEventListener('change', updateActiveLabel);

    resetButton?.addEventListener('click', () => {
        window.requestAnimationFrame(() => {
            updateActiveLabel();
        });
    });

    form?.addEventListener('submit', () => {
        submitButtons.forEach((button) => {
            button.disabled = true;
        });

        if (submitButtons[0]) {
            submitButtons[0].textContent = 'Enregistrement...';
        }

        if (submitButtons[1]) {
            submitButtons[1].textContent = 'Enregistrement du départ...';
        }
    });

    updateClock();
    updateActiveLabel();

    window.setInterval(updateClock, 1000);
})();
