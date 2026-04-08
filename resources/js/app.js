import './bootstrap';

import Alpine from 'alpinejs';
import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.bootstrap5.css';

window.Alpine = Alpine;

Alpine.start();

const initTomSelect = () => {
    document.querySelectorAll('select.js-tom-select').forEach((select) => {
        if (select.tomselect) {
            return;
        }

        const placeholder = select.dataset.placeholder ?? 'Rechercher...';

        new TomSelect(select, {
            create: false,
            allowEmptyOption: true,
            maxOptions: 500,
            closeAfterSelect: true,
            placeholder,
            sortField: [{ field: 'text', direction: 'asc' }],
        });
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTomSelect);
} else {
    initTomSelect();
}
