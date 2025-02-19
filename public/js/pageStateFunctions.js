const initialState = {
    name: 'fetch_games_gallery',
    data: {
        is_page: true,
        is_modal: false
    }
}

let previousState = initialState;

function setPageState(name, data = {}) {
    let state = {
        name: name,
        data: data
    };
    let isPage = data.hasOwnProperty('is_page') ? data.is_page : false;
    let isModal = data.hasOwnProperty('is_modal') ? data.is_modal : false;
    if (isPage && !isModal) {
        // previousState = getPageState();
        storeState(state);
    }
    document.dispatchEvent(new CustomEvent('stateChanged', { detail: state }));
    // window.history.pushState({pageState: state}, '', '?state=' + state);
}

function getPageState() {
    return JSON.parse(localStorage.getItem('state')) || initialState;
}

function storeState(state) {
    localStorage.setItem('state', JSON.stringify(state));
}

export { initialState, setPageState, getPageState, storeState };
