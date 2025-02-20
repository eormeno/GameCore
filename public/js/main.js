import { renderGamesCards } from './renderGameCards.js';
import { renderLoginForm } from './renderLoginForm.js';
import { renderGameContainer } from './renderGameContainer.js';
import { GameRenderer } from './GameRenderer.js';

import pageState from './modules/PageStateManager.js';

document.addEventListener("stateChanged", async function (event) {
    await main(event.detail);
});

async function main(state = pageState.initialState) {
    let data = state.data;
    switch (state.name) {
        case pageState.initialState.name:
            await fetchApi('api/game-app', 'GET');
            break;
        case 'displaying_games_gallery':
            renderGamesCards(data);
            break;
        case 'fetching_game':
            pageState.previousState = state;
            await fetchApi(`api/game-app/${data.id}/play`, 'GET');
            break;
        case 'game':
            let gameRenderer = new GameRenderer();
            renderGameContainer(data, gameRenderer);
            gameRenderer.startGame(data);
            break;
        case 'displaying_login':
            //renderLoginForm(data);
            loadPartial('login-form', document.getElementById('gamesContainer'));
            break;
        case 'trying_login':
            await fetchApi(data.action, data.method, data.body);
            break;
        case 'successful_login':
            localStorage.setItem('token', data.token);
            let redirect = pageState.previousState ? pageState.previousState : pageState.initialState;
            pageState.setPageState(redirect.name, redirect.data);
            break;
        case 'failed_login':
            alert('Usuario o contraseña incorrectos');
            break;
        case 'register':
            renderRegisterForm();
            break;
        case 'error':
            break;
        default:
            console.log('State not found:', state.name);
            break;
    }
}

async function fetchApi(endpoint, method = 'GET', body = null) {
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
            throw new Error('Error en la respuesta de la red');
        }

        let data;
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            data = { error: await response.text() };
        } else {
            data = await response.json();
        }

        const stateName = Object.keys(data)[0];
        pageState.setPageState(stateName, data[stateName]);
    } catch (error) {
        console.error('Error al cargar los juegos:', error);
        document.getElementById('gamesContainer').innerHTML = error;
    }
}

async function loadPartial(file, container) {
    try {
        // Carga el fragmento HTML desde la carpeta "partials"
        const response = await fetch(`partials/${file}.html`);
        const html = await response.text();

        // Inserta el contenido en el contenedor predefinido
        container.innerHTML = html;

        // Selecciona todos los <script> insertados y reemplázalos para forzar su ejecución
        const scripts = container.querySelectorAll('script');
        for (const oldScript of scripts) {
            const newScript = document.createElement('script');
            // Copia los atributos del script (src, type, etc.)
            for (const attr of oldScript.attributes) {
                newScript.setAttribute(attr.name, attr.value);
            }
            // Copia el contenido del script
            newScript.textContent = oldScript.textContent;
            oldScript.parentNode.replaceChild(newScript, oldScript);
        }
    } catch (err) {
        console.error('Error al cargar el partial:', err);
    }
}

export { main };
