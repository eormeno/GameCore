class PageStateManager {
    constructor() {
      if (PageStateManager._instance) {
        return PageStateManager._instance;
      }
      this.initialState = {
        name: 'fetch_games_gallery',
        data: {
          is_page: true,
          is_modal: false
        }
      };
      PageStateManager._instance = this;
    }

    setPageState(name, data = {}) {
      const state = {
        name,
        data
      };
      const isPage = Object.prototype.hasOwnProperty.call(data, 'is_page') ? data.is_page : false;
      const isModal = Object.prototype.hasOwnProperty.call(data, 'is_modal') ? data.is_modal : false;
      if (isPage && !isModal) {
        this.storeState(state);
      }
      document.dispatchEvent(new CustomEvent('stateChanged', { detail: state }));
    }

    getPageState() {
      return JSON.parse(localStorage.getItem('state')) || this.initialState;
    }

    storeState(state) {
      localStorage.setItem('state', JSON.stringify(state));
    }
  }

  const pageStateInstance = new PageStateManager();
  export default pageStateInstance;
