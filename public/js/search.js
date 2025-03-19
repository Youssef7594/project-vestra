document.getElementById('user-search').addEventListener('input', function () {
    let query = this.value.trim();

    // Si la recherche est vide, effacer les résultats
    if (query.length === 0) {
        document.getElementById('searchResults').innerHTML = '';
        return;
    }

    // Faire une requête AJAX vers l'API de recherche
    fetch(`/search?q=${query}`)
        .then(response => response.json())
        .then(data => {
            let resultsContainer = document.getElementById('searchResults');
            resultsContainer.innerHTML = ''; // Vider les résultats précédents

            // Affichage des résultats des catégories
            if (data.categories.length > 0) {
                resultsContainer.innerHTML += '<strong>Catégories</strong>';
                data.categories.forEach(cat => {
                    // Ajouter la classe "search-item" pour appliquer le fond blanc
                    resultsContainer.innerHTML += `<p class="search-item"><a href="/category/${cat.id}">${cat.name}</a></p>`;
                });
            }

            // Affichage des résultats des projets
            if (data.projects.length > 0) {
                resultsContainer.innerHTML += '<strong>Projets</strong>';
                data.projects.forEach(proj => {
                    // Ajouter la classe "search-item" pour appliquer le fond blanc
                    resultsContainer.innerHTML += `<p class="search-item"><a href="/projects/${proj.slug}">${proj.title}</a></p>`;
                });
            }

            // Affichage des résultats des utilisateurs
            if (data.users.length > 0) {
                resultsContainer.innerHTML += '<strong>Utilisateurs</strong>';
                data.users.forEach(user => {
                    // Ajouter la classe "search-item" pour appliquer le fond blanc
                    resultsContainer.innerHTML += `<p class="search-item"><a href="/profile/${user.id}">${user.username}</a></p>`;
                });
            }
        });
});






    // Fonction pour activer/désactiver le menu hamburger
    document.querySelector('.hamburger').addEventListener('click', function() {
        var menuItems = document.querySelectorAll('.ul-un, .ul-deux');
        menuItems.forEach(function(menu) {
            menu.classList.toggle('show');  // Ajoute ou retire la classe 'show'
        });
    });
    
