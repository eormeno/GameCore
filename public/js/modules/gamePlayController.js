import { GameStateHandlers } from './gameStateHandlers.js';
import { UIManager } from './uiManager.js';
import { ShareService } from './shareService.js';
import { NavigationService } from './navigationService.js';

/**
 * GamePlayController - Main orchestrator for game play functionality
 */
export class GamePlayController {
    constructor() {
        this.gameData = null;
        this.stateHandlers = null;
    }

    /**
     * Initialize the controller with dependencies
     */
    async initialize(router, gameRenderer, container) {
        // Create services
        const shareService = new ShareService();
        const navigationService = new NavigationService(router);
        const uiManager = new UIManager(container, shareService, navigationService, gameRenderer);

        // Create state handlers
        this.stateHandlers = new GameStateHandlers(uiManager, navigationService, gameRenderer);
    }

    /**
     * Handle different game states from API response
     */
    handleGameState(stateName, data) {
        this.gameData = data;
        this.stateHandlers.handle(stateName, data);
    }
}
