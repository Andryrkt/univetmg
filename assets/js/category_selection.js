// Use event delegation on the document to support Turbo navigation
document.addEventListener('change', (event) => {
    const select = event.target;

    // Check if this is a category select (either main or sub-category)
    if (select.tagName !== 'SELECT' || (!select.id.includes('categorie') && !select.id.includes('categorieParent'))) {
        return;
    }

    // Find necessary elements in the current page
    const categoryContainer = document.getElementById('category-container');
    const finalCategoryInput = document.getElementById('produit_categorie');
    const mainCategorySelect = document.getElementById('produit_categorieParent');

    if (!mainCategorySelect || !categoryContainer || !finalCategoryInput) {
        return;
    }

    handleCategoryChange(select, categoryContainer, finalCategoryInput, mainCategorySelect);
});

function handleCategoryChange(select, categoryContainer, finalCategoryInput, mainCategorySelect) {
    const parentId = select.value;
    console.log(`Category changed. Selected ID: ${parentId}`, select);

    // --- Removal Logic ---
    if (select.id === 'produit_categorieParent') {
        // If main select changed, clear the whole sub-category container
        categoryContainer.innerHTML = '';
        console.log('Main category changed, clearing sub-category container.');
    } else {
        // If a sub-category select changed, remove selects that follow it
        let currentWrapper = select.closest('.mb-3');
        if (currentWrapper) {
            let nextSibling = currentWrapper.nextElementSibling;
            while (nextSibling) {
                const toRemove = nextSibling;
                nextSibling = nextSibling.nextElementSibling;
                toRemove.remove();
                console.log('Removing subsequent sub-category select.');
            }
        }
    }

    // --- Update Final Value Logic ---
    if (parentId) {
        finalCategoryInput.value = parentId;
        console.log(`Final category value set to: ${parentId}`);
    } else {
        // Find the previous select and set its value to the hidden input
        let previousSelect = null;
        const currentWrapper = select.closest('.mb-3');
        if (select.id === 'produit_categorieParent') {
            previousSelect = null; // No previous
        } else if (currentWrapper && currentWrapper.previousElementSibling) {
            previousSelect = currentWrapper.previousElementSibling.querySelector('select');
        }

        if (!previousSelect) { // Fallback to main select
            previousSelect = mainCategorySelect;
        }

        finalCategoryInput.value = previousSelect ? previousSelect.value : '';
        console.log(`Selection cleared. Final category value set to: ${finalCategoryInput.value}`);
    }

    if (!parentId) {
        return;
    }

    const fetchUrl = `/produit/categorie/subcategories/${parentId}`;
    console.log(`Fetching subcategories from: ${fetchUrl}`);

    fetch(fetchUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Network response was not ok: ${response.statusText}`);
            }
            return response.json();
        })
        .then(subCategories => {
            if (subCategories.length > 0) {
                createSubCategorySelect(subCategories, categoryContainer, finalCategoryInput);
                // We have children, so the current selection is not final
                finalCategoryInput.value = '';
                console.log('Subcategories found. Final category value cleared.');
            } else {
                console.log('No subcategories found. This is the final selection.');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
        });
}

function createSubCategorySelect(categories, categoryContainer, finalCategoryInput) {
    const level = categoryContainer.children.length + 1;
    console.log(`Creating sub-category select for level ${level}`);

    const selectWrapper = document.createElement('div');
    selectWrapper.classList.add('mb-3');

    const newSelect = document.createElement('select');
    newSelect.classList.add('form-select');
    newSelect.id = `produit_categorie_level_${level}`;

    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = `Sélectionnez une sous-catégorie (Niveau ${level})`;
    newSelect.appendChild(placeholder);

    categories.forEach(category => {
        const option = document.createElement('option');
        option.value = category.id;
        option.textContent = category.nom;
        newSelect.appendChild(option);
    });

    selectWrapper.appendChild(newSelect);
    categoryContainer.appendChild(selectWrapper);
}
