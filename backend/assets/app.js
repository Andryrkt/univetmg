import '@hotwired/turbo';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';
import 'tom-select/dist/css/tom-select.bootstrap5.min.css';
import TomSelect from 'tom-select';
import './js/collection_type.js';
import './js/category_selection.js';
import './js/numeric_input_validation.js';

// Initialize Tom Select
document.addEventListener('turbo:load', () => {
    document.querySelectorAll('.tom-select').forEach((el) => {
        if (el.tomselect) return; // Avoid double initialization
        new TomSelect(el, {
            create: false,
            plugins: ['dropdown_input'],
        });
    });
});

// Fix for Symfony Web Profiler + Turbo
document.addEventListener('turbo:before-cache', () => {
    const toolbar = document.querySelector('.sf-toolbar');
    if (toolbar) {
        toolbar.remove();
    }
});
