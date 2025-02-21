import pageState from './modules/PageStateManager.js';

function renderGamesCards(games) {
    const container = document.getElementById('gamesContainer');
    container.innerHTML = '';
    // renderAuthButtons(container);

    games.forEach(game => {
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
        playButton.onclick = () => pageState.setPageState('fetching_game', { id: game.id });

        content.appendChild(title);
        content.appendChild(description);
        content.appendChild(playButton);

        card.appendChild(image);
        card.appendChild(content);

        container.appendChild(card);
    });
}

function renderAuthButtons(container) {
    // Create auth container
    const authContainer = document.createElement('div');
    authContainer.id = 'authContainer';
    authContainer.className = 'auth-container';
    container.appendChild(authContainer);
    authContainer.innerHTML = '';

    // Display the user's name
    const usernameDisplay = document.createElement('p');
    usernameDisplay.className = 'username-display';
    // If window.userName is not defined, default to 'Invitado'
    usernameDisplay.textContent = 'Usuario: ' + (window.userName || 'Invitado');
    authContainer.appendChild(usernameDisplay);

    // Create auth buttons
    const loginButton = document.createElement('button');
    loginButton.className = 'auth-button';
    loginButton.textContent = 'Login';
    loginButton.onclick = () => alert('Login clicked');

    const logoutButton = document.createElement('button');
    logoutButton.className = 'auth-button';
    logoutButton.textContent = 'Logout';
    logoutButton.onclick = () => alert('Logout clicked');

    const registerButton = document.createElement('button');
    registerButton.className = 'auth-button';
    registerButton.textContent = 'Register';
    registerButton.onclick = () => alert('Register clicked');

    authContainer.appendChild(loginButton);
    authContainer.appendChild(logoutButton);
    authContainer.appendChild(registerButton);
}

export { renderGamesCards };
