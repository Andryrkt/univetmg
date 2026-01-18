document.addEventListener('DOMContentLoaded', () => {
    const addButtons = document.querySelectorAll('.add-collection-widget');

    addButtons.forEach(btn => {
        btn.addEventListener("click", function() {
            const list = document.querySelector(this.dataset.listSelector);
            if (!list) {
                console.error(`Element not found: ${this.dataset.listSelector}`);
                return;
            }

            // Récupérer le prototype et le compteur
            const prototype = list.dataset.prototype;
            let counter = parseInt(list.dataset.widgetCounter) || list.children.length;

            // Créer le nouvel élément à partir du prototype
            // Remplacer l'identifiant de substitution par le compteur actuel
            const newWidget = prototype.replace(/__name__/g, counter);

            // Incrémenter le compteur pour le prochain ajout
            list.dataset.widgetCounter = counter + 1;

            // Créer un nouvel élément de liste pour y insérer le widget
            list.insertAdjacentHTML('beforeend', newWidget);
        });
    });

    // Gérer la suppression d'un élément de la collection
    document.addEventListener('click', function (e) {
        // Vérifier si le bouton de suppression a été cliqué
        if (e.target && e.target.matches('.remove-collection-widget')) {
            e.preventDefault();
            const item = e.target.closest('.list-group-item');
            if (item) {
                item.remove();
            }
        }
    });
});