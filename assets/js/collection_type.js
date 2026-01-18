document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.add-collection-widget').forEach(btn => {
        btn.addEventListener("click", (e) => {
            const list = document.querySelector(e.currentTarget.dataset.listSelector);
            const counter = list.dataset.widgetCounter || list.children.length;
            const newWidget = list.dataset.prototype.replace(/__name__/g, counter);
            
            const newDiv = document.createElement('div');
            newDiv.innerHTML = newWidget;

            const item = document.createElement('div');
            item.classList.add('list-group-item');
            
            const newWidgetContent = newWidget.replace(/<label class="required">/g, '<label>');
            item.innerHTML = newWidgetContent;

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'btn btn-danger remove-collection-widget';
            removeButton.innerText = 'Supprimer';
            
            const row = item.querySelector('.row');
            if (row) {
                const col = document.createElement('div');
                col.className = 'col-auto d-flex align-items-end';
                col.appendChild(removeButton);
                row.appendChild(col);
            } else {
                 item.appendChild(removeButton);
            }

            list.appendChild(item);
            list.dataset.widgetCounter = parseInt(counter) + 1;
        });
    });

    document.addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('remove-collection-widget')) {
            e.preventDefault();
            const item = e.target.closest('.list-group-item');
            if (item) {
                item.remove();
            }
        }
    });
});