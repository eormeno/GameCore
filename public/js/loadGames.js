function loadGames() {
    fetch('api/game-app')
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta de la red');
            }
            return response.json();
        })
        .then(games => {
            createGameCards(games);
        })
        .catch(error => {
            console.error('Error al cargar los juegos:', error);
            document.getElementById('gamesContainer').innerHTML =
                '<p>Error al cargar los juegos. Por favor intenta nuevamente más tarde.</p>';
        });
}

// Función para manejar el clic en el botón Jugar
function playGame(prefix) {
    let token = localStorage.getItem('token');
    fetch('api/game-app/' + prefix + '/play', {
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${token}`
        }
    }).then(response => {
        if (!response.ok) {
            console.error('Error en la respuesta de la red:', response);
        }
        return response.json();
    }).then(game => {
        // Si el json comienza con una key 'form', renderizar el formulario
        if (game.form) {
            renderLoginForm(game);
        } else {
            console.log(JSON.stringify(game, null, 2));
        }
    }).catch(error => {
        console.error('Error al cargar el juego:', error);
    });

}

// Función para crear las tarjetas de juego
function createGameCards(games) {
    const container = document.getElementById('gamesContainer');
    // remover contenido previo
    container.innerHTML = '';

    games.forEach(game => {
        // Crear elementos HTML
        const card = document.createElement('div');
        card.className = 'game-card';

        const image = document.createElement('img');
        image.className = 'game-image';
        image.src = 'storage/' + game.image;
        image.alt = game.name;

        const content = document.createElement('div');
        content.className = 'game-content';

        const title = document.createElement('h2');
        title.className = 'game-title';
        title.textContent = game.name;

        const description = document.createElement('p');
        description.className = 'game-description';
        description.textContent = game.description;

        const playButton = document.createElement('button');
        playButton.className = 'play-button';
        playButton.textContent = 'Jugar';
        playButton.onclick = () => playGame(game.id);

        // Ensamblar la tarjeta
        content.appendChild(title);
        content.appendChild(description);
        content.appendChild(playButton);

        card.appendChild(image);
        card.appendChild(content);

        container.appendChild(card);
    });
}
