import { renderGamesCards } from './renderGameCards.js';
import { GameRenderer } from './GameRenderer.js';

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
            await loadPartial('game-container', gamesContainer, {
                game: data,
                gameRenderer: gameRenderer,
                pageState: pageState
            });
            gameRenderer.startGame(data);
            break;
        case 'auth_required':
            loadPartial('login-form', gamesContainer);
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
        loadPartial('auth-menu', authContainer, {
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
        loadPartial('auth-menu', authContainer,
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

// async function loadPartial(file, container, params = {}) {
//     try {
//         const response = await fetch(`partials/${file}.html`);
//         const html = await response.text();
//         container.innerHTML = html;
//         container._partialParams = params;

//         // Selecciona todos los <script> insertados y reemplázalos para forzar su ejecución
//         const scripts = container.querySelectorAll('script');
//         for (const oldScript of scripts) {
//             const newScript = document.createElement('script');
//             // Copia los atributos del script (src, type, etc.)
//             for (const attr of oldScript.attributes) {
//                 newScript.setAttribute(attr.name, attr.value);
//             }
//             // Copia el contenido del script
//             newScript.textContent = oldScript.textContent;
//             oldScript.parentNode.replaceChild(newScript, oldScript);
//         }
//     } catch (err) {
//         console.error('Error al cargar el partial:', err);
//     }
// }

async function loadPartial(file, container, params = {}) {
    try {
        // Generate a unique cache key based on file and parameters
        const cacheKey = `partial_${file}}`;

        // Check cached content
        const cachedData = localStorage.getItem(cacheKey);
        let html = null;

        if (cachedData) {
            const { content, timestamp } = JSON.parse(cachedData);
            // Check if cache is younger than 1 hour (3600000 ms)
            if (Date.now() - timestamp < 3600000) {
                html = content;
            }
        }

        // Fetch fresh content if no valid cache
        if (!html) {
            const response = await fetch(`partials/${file}.html`);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            html = await response.text();

            // Update cache with timestamp
            localStorage.setItem(cacheKey, JSON.stringify({
                content: html,
                timestamp: Date.now()
            }));
        }

        // Insert content and execute scripts
        container.innerHTML = html;
        container._partialParams = params;

        // Script execution logic (existing code)
        const scripts = container.querySelectorAll('script');
        for (const oldScript of scripts) {
            const newScript = document.createElement('script');
            for (const attr of oldScript.attributes) {
                newScript.setAttribute(attr.name, attr.value);
            }
            newScript.textContent = oldScript.textContent;
            oldScript.parentNode.replaceChild(newScript, oldScript);
        }
    } catch (err) {
        console.error('Error loading partial:', err);
        // Optional: Add fallback to stale cache here if needed
    }
}

export { main };
