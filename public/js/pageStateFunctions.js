const defaultState = {
    state: 'init',
    parameters: {}
}

function setPageState(state, parameters = {}) {
    localStorage.setItem('state', JSON.stringify({
        state: state,
        parameters: parameters
    }));
    // pageState = state;
    // window.history.pushState({pageState: state}, '', '?state=' + state);
}

function getPageState() {
    return JSON.parse(localStorage.getItem('state')) || defaultState;
}
