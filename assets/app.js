import '@hotwired/turbo';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';
import './js/collection_type.js';
import './js/category_selection.js';

// Fix for Symfony Web Profiler + Turbo
document.addEventListener('turbo:before-cache', () => {
    const toolbar = document.querySelector('.sf-toolbar');
    if (toolbar) {
        toolbar.remove();
    }
});
