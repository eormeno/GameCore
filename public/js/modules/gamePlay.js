/**
 * GamePlay module - Main entry point for game play functionality
 */
import { GamePlayController } from './gamePlayController.js';

/**
 * Load required dependencies
 */
async function loadDependencies() {
    const baseUrl = window.location.origin;

    const [apiModule, routerModule, gameRendererModule] = await Promise.all([
        import(`${baseUrl}/js/services/api.js`),
        import(`${baseUrl}/js/router.js`),
        import(`${baseUrl}/js/GameRenderer.js`)
    ]);

    return {
        fetchApi: apiModule.fetchApi,
        router: routerModule.getRouter(),
        GameRenderer: gameRendererModule.GameRenderer
    };
}

/**
 * Main render function for game play
 */
export async function render(container) {
    try {
        // Extract game ID from container parameters
        const gameId = extractGameId(container);
        if (!gameId) {
            console.error('No game ID provided');
            return;
        }

        // Load dependencies
        const { fetchApi, router, GameRenderer } = await loadDependencies();

        // Initialize controller
        const controller = new GamePlayController();
        const gameRenderer = new GameRenderer();
        await controller.initialize(router, gameRenderer, container);

        // Fetch game data and handle response
        await fetchGameData(fetchApi, gameId, controller);

    } catch (error) {
        console.error('Error rendering game play:', error);
        handleRenderError(container, error);
    }
}

/**
 * Extract game ID from container parameters
 */
function extractGameId(container) {
    return container._partialParams?.id;
}

/**
 * Fetch game data from API
 */
async function fetchGameData(fetchApi, gameId, controller) {
    await fetchApi(
        `/api/game-app/${gameId}/play`,
        'GET',
        null,
        (stateName, data) => controller.handleGameState(stateName, data)
    );
}

/**
 * Handle render errors
 */
function handleRenderError(container, error) {
    const title = container?.querySelector('.game-title');
    if (title) {
        title.style.display = "block";
        title.textContent = "Error loading game";
    }

    const canvasContainer = document.getElementById('glCanvas');
    if (canvasContainer) {
        canvasContainer.style.display = "none";
    }
}
