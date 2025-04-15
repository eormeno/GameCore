import pageState from './modules/PageStateManager.js';
import { partialLoader } from './modules/PartialLoader.js';

function renderGamesCards(games) {
    const container = document.getElementById('gamesContainer');
    container.innerHTML = '';

    games.forEach(game => {
        const card = document.createElement('div');
        card.className = 'game-card';

        const image = document.createElement('img');
        image.className = 'game-image';
        image.src = 'api/game-app/' + game.id + '/public/' + game.card_image;
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
        playButton.disabled = game.prefab_name === null;
        playButton.onclick = () => pageState.setPageState('fetching_game', { id: game.id });

        content.appendChild(title);
        content.appendChild(description);
        content.appendChild(playButton);

        card.appendChild(image);
        card.appendChild(content);

        container.appendChild(card);
        console.log('game', game);

        partialLoader.loadPartial('game-card', card, {
            game: game,
            pageState: pageState
        });


    });
}

export { renderGamesCards };
