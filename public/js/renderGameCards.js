import pageState from './modules/PageStateManager.js';
import { partialLoader } from './modules/PartialLoader.js';

async function renderGamesCards(games) {
    const container = document.getElementById('gamesContainer');
    container.innerHTML = '';

    for (let game of games) {
        const card = document.createElement('div');

        await partialLoader.loadPartial('game-card', card, {
            game: game,
            pageState: pageState
        });

        container.appendChild(card);
    }
}

export { renderGamesCards };
