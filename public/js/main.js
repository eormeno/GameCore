import { renderGamesCards } from './renderGameCards.js';
import { renderLoginForm} from './renderLoginForm.js';
import { renderGameContainer } from './renderGameContainer.js';
import { GameRenderer } from './GameRenderer.js';

import pageState from './modules/PageStateManager.js';

document.addEventListener("stateChanged", function (event) {
    // console.log(JSON.stringify(event.detail, null, 2));
    main(event.detail);
});

function main(state = pageState.initialState) {
    let data = state.data;
    // console.log('State:', state.name);

    switch (state.name) {
        case pageState.initialState.name:
            fetchApi('api/game-app', 'GET');
            break;
        case 'displaying_games_gallery':
            renderGamesCards(data);
            break;
        case 'fetching_game':
            pageState.previousState = state;
            fetchApi(`api/game-app/${data.id}/play`, 'GET');
            break;
        case 'game':
            let gameRenderer = new GameRenderer();
            renderGameContainer(data, gameRenderer);
            gameRenderer.startGame(data);
            break;
        case 'displaying_login':
            renderLoginForm(data);
            break;
        case 'trying_login':
            fetchApi(data.action, data.method, data.body);
            break;
        case 'successful_login':
            localStorage.setItem('token', data.token);
            let redirect = pageState.previousState ? pageState.previousState : pageState.initialState;
            pageState.setPageState(redirect.name, redirect.data);
            break;
        case 'register':
            renderRegisterForm();
            break;
        default:
            console.log('State not found:', state.name);
            break;
    }
}

function fetchApi(endpoint, method = 'GET', body = null) {
    let token = localStorage.getItem('token');
    fetch(endpoint, {
        method: method,
        body: body,
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        }
    }).then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta de la red');
        }
        // if response is not json, return response
        if (!response.headers.get('content-type').includes('application/json')) {
            console.log(response);
            return response;
        }
        return response.json();
    }).then(data => {
        let stateName = Object.keys(data)[0];
        //setPageState(stateName, data[stateName]);
        pageState.setPageState(stateName, data[stateName]);
    }).catch(error => {
        console.error('Error al cargar los juegos:', error);
        document.getElementById('gamesContainer').innerHTML = error;
    });
}

export { main };
