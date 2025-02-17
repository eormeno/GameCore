document.addEventListener("stateChanged", function (event) {
    console.log(JSON.stringify(event.detail, null, 2));
    // state is the first key name in the JSON object
    let state = Object.keys(event.detail)[0];
    main(state, event.detail[state]);
});

function main(state = 'init', data = {}) {

    console.log('State:', state);

    switch (state) {
        case 'init':
            fetchApi('api/game-app', 'GET');
            break;
        case 'gameApps':
            renderGamesCards(data);
            break;
        case 'game':
            let gameAppId = getPageState().parameters.id;
            fetchApi(`api/game-app/${gameAppId}/play`, 'GET');
            break;
        case 'login':
            renderLoginForm(data);
            break;
        case 'register':
            renderRegisterForm();
            break;
        default:
            console.log('State not found:', state);
            break;
    }
}

function fetchApi(endpoint, method = 'GET', body = null) {
    let token = localStorage.getItem('token');
    fetch(endpoint, {
        method: method,
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        }
    }).then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta de la red');
        }
        return response.json();
    }).then(data => {
        document.dispatchEvent(new CustomEvent('stateChanged', { detail: data }));
    }).catch(error => {
        console.error('Error al cargar los juegos:', error);
        document.getElementById('gamesContainer').innerHTML =
            '<p>Error al cargar los juegos. Por favor intenta nuevamente más tarde.</p>';
    });
}
