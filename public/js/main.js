import { renderGamesCards } from './renderGameCards.js';
import { GameRenderer } from './GameRenderer.js';
import { partialLoader } from './modules/PartialLoader.js';

import pageState from './modules/PageStateManager.js';

document.addEventListener("stateChanged", async function (event) {
    await main(event.detail);
});

async function main(state = pageState.initialState) {
    const authMenuContainer = document.getElementById('auth-menu');
    const gamesContainer = document.getElementById('gamesContainer');
    let data = state.data;
    switch (state.name) {
        case pageState.initialState.name:
            pageState.previousState = state;
            await fetchApi('api/game-app', 'GET');
            break;
        case 'displaying_games_gallery':
            await updateAuthMenu(authMenuContainer);
            renderGamesCards(data);
            break;
        case 'fetching_game':
            pageState.previousState = state;
            await fetchApi(`api/game-app/${data.id}/play`, 'GET');
            break;
        case 'game':
            let gameRenderer = new GameRenderer();
            await partialLoader.loadPartial('game-container', gamesContainer, {
                game: data,
                gameRenderer: gameRenderer,
                pageState: pageState
            });
            gameRenderer.startGame(data);
            break;
        case 'auth_required':
            await partialLoader.loadPartial('login-form', gamesContainer);
            break;
        case 'trying_login':
            await fetchApi(data.action, data.method, data.body);
            break;
        case 'successful_login':
            localStorage.setItem('token', data.token);
            pageState.setPageState('auth_state_changed', {});
            let redirect = pageState.previousState ? pageState.previousState : pageState.initialState;
            pageState.setPageState(redirect.name, redirect.data);
            break;
        case 'failed_login':
            alert('Usuario o contraseña incorrectos');
            break;
        case 'register':
            renderRegisterForm();
            break;
        case 'logout':
            await closeSession();
            break;
        case 'auth_state_changed':
            await updateAuthMenu(authMenuContainer);
            break;
        case 'error':
            const errorContainer = document.getElementById('error-container');
            errorContainer.innerHTML = data.error;
            break;
        default:
            console.log('State not found:', state.name);
            break;
    }
}

async function updateAuthMenu(authContainer) {
    const token = localStorage.getItem('token');
    if (!token) {
        await partialLoader.loadPartial('auth-menu', authContainer, {
            user: {
                isLoggedIn: false,
                name: 'Invitado'
            },
            pageState: pageState
        });
        return;
    }
    await fetchApi('api/user', 'GET', null, (stateName, data) => {
        const user = {};
        if (stateName === 'auth_required') {
            user.isLoggedIn = false;
            user.name = 'Invitado';
        } else {
            user.isLoggedIn = true;
            user.name = data.name;
        }
        partialLoader.loadPartial('auth-menu', authContainer,
            {
                user: user, pageState: pageState
            });
    });
}

async function closeSession() {
    await fetchApi('api/logout', 'POST', null, (stateName, data) => {
        localStorage.removeItem('token');
        pageState.setPageState('auth_state_changed', {});
    });
}

async function fetchApi(endpoint, method = 'GET', body = null, callback = null) {
    // console.log(method, endpoint, body ? JSON.stringify(body) : 'no body');
    const token = localStorage.getItem('token');
    try {
        const response = await fetch(endpoint, {
            method,
            body,
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            const error = await response.text();
            pageState.setPageState('error', { error });
            return;
        }

        let data;
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            data = { error: await response.text() };
        } else {
            data = await response.json();
        }

        const stateName = Object.keys(data)[0];
        if (!callback) {
            pageState.setPageState(stateName, data[stateName]);
        } else {
            callback(stateName, data[stateName]);
        }
    } catch (error) {
        console.error('Error al cargar los juegos:', error);
        document.getElementById('gamesContainer').innerHTML = error;
    }
}

export { main };
