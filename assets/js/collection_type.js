// Use event delegation on the document to support Turbo navigation
document.addEventListener('click', function(e) {
    // 1. Handle "Add" button
    const addBtn = e.target.closest('.add-collection-widget');
    if (addBtn) {
        e.preventDefault();
        const list = document.querySelector(addBtn.dataset.listSelector);
        if (!list) {
            console.error(`Element not found: ${addBtn.dataset.listSelector}`);
            return;
        }

        // Get prototype and counter
        const prototype = list.dataset.prototype;
        let counter = parseInt(list.dataset.widgetCounter) || list.children.length;

        // Create new element from prototype
        const newWidget = prototype.replace(/__name__/g, counter);

        // Increment counter
        list.dataset.widgetCounter = counter + 1;

        // Insert widget
        list.insertAdjacentHTML('beforeend', newWidget);
        return;
    }

    // 2. Handle "Remove" button
    const removeBtn = e.target.closest('.remove-collection-widget');
    if (removeBtn) {
        e.preventDefault();
        const item = removeBtn.closest('.list-group-item');
        if (item) {
            item.remove();
        }
        return;
    }
});