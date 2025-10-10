/**
 * GameStateHandlers - Handles different game states
 */
export class GameStateHandlers {
    constructor(uiManager, navigationService, gameRenderer) {
        this.uiManager = uiManager;
        this.navigationService = navigationService;
        this.gameRenderer = gameRenderer;
    }

    /**
     * Handle different game states
     */
    handle(stateName, data) {
        const handlers = {
            'first_screen': () => this.handleFirstScreen(data),
            'game': () => this.handleGameState(data),
            'auth_required': () => this.handleAuthRequired(),
            'game_not_found': () => this.handleGameNotFound(),
            'game_play': () => this.handleGamePlay(data)
        };

        const handler = handlers[stateName];
        if (handler) {
            handler();
        } else {
            console.warn(`Unknown game state: ${stateName}`);
        }
    }

    /**
     * Handle main game state
     */
    handleGameState(data) {
        this.uiManager.setPageTitle(data.title);
        this.uiManager.configureCanvas(data.width, data.height);
        this.uiManager.setupEventListeners(data);
        this.startGame(data);
    }

    /**
     * Handle authentication required
     */
    handleAuthRequired() {
        this.navigationService.navigateToLogin();
    }

    /**
     * Handle game not found
     */
    handleGameNotFound() {
        this.uiManager.showGameNotFoundMessage();
    }

    /**
     * Handle game play state
     */
    handleGamePlay(data) {
        this.uiManager.renderGameContent(data.html);
    }

    /**
     * Handle first screen state
     */
    handleFirstScreen(data) {
        this.uiManager.setPageTitle(data.game_app.name);
        this.uiManager.configureCanvas(data.game_app.width, data.game_app.height);
        this.uiManager.renderFirstScreen(data);
    }

    /**
     * Start the game
     */
    startGame(data) {
        if (this.gameRenderer?.startGame) {
            this.gameRenderer.startGame(data);
        }
    }
}
