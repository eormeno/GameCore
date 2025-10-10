/**
 * UIManager - Handles DOM manipulations and UI updates
 */
export class UIManager {
    constructor(container, shareService, navigationService, gameRenderer) {
        this.container = container;
        this.shareService = shareService;
        this.navigationService = navigationService;
        this.gameRenderer = gameRenderer;
    }

    /**
     * Set page title
     */
    setPageTitle(title) {
        document.title = title;
    }

    /**
     * Configure canvas dimensions and visibility
     */
    configureCanvas(width, height) {
        const canvas = this.getCanvasContainer();
        if (canvas) {
            Object.assign(canvas.style, {
                width: `${width}px`,
                height: `${height}px`,
                display: 'block'
            });
        }
    }

    renderFirstScreen(data) {
        const canvas = this.getCanvasContainer();
        if (canvas) {
            const gamesList = document.createElement('div');
            gamesList.className = 'open-games-list';

            data.open_games.forEach(game => {
                const gameItem = document.createElement('div');
                gameItem.className = 'game-item';

                gameItem.textContent = `${game.createdAt} (${game.invitationCode})`;
                gamesList.appendChild(gameItem);
            });

            canvas.innerHTML = ''; // Clear previous content
            canvas.appendChild(gamesList);
        }
    }

    /**
     * Setup event listeners
     */
    setupEventListeners(data) {
        this.setupShareButton(data);
        this.setupCloseButton();
    }

    /**
     * Setup share button
     */
    setupShareButton(data) {
        const shareBtn = this.getShareButton();
        if (shareBtn) {
            shareBtn.addEventListener('click', () => this.shareService.share(data.invitationCode));
        }
    }

    /**
     * Setup close button
     */
    setupCloseButton() {
        const closeBtn = this.getCloseButton();
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.handleClose());
        }
    }

    /**
     * Handle close action
     */
    handleClose() {
        this.clearGamesContainer();
        this.stopGameLoop();
        this.navigationService.navigateToGames();
    }

    /**
     * Show game not found message
     */
    showGameNotFoundMessage() {
        const title = this.getGameTitle();
        const canvas = this.getCanvasContainer();

        if (title) {
            title.style.display = 'block';
            title.textContent = 'Game not found';
        }

        if (canvas) {
            canvas.style.display = 'none';
        }
    }

    /**
     * Render game content
     */
    renderGameContent(html) {
        const gameContainer = this.container?.querySelector('.game-container');
        if (gameContainer) {
            gameContainer.innerHTML = html;
        }
    }

    /**
     * Clear games container
     */
    clearGamesContainer() {
        const gamesContainer = document.getElementById('gamesContainer');
        if (gamesContainer) {
            gamesContainer.innerHTML = '';
        }
    }

    /**
     * Stop game loop
     */
    stopGameLoop() {
        if (this.gameRenderer?.stopGameLoop) {
            this.gameRenderer.stopGameLoop();
        }
    }

    // DOM Helper Methods
    getGameTitle() {
        return this.container?.querySelector('.game-title');
    }

    getCanvasContainer() {
        return document.getElementById('glCanvas');
    }

    getShareButton() {
        return document.querySelector('.share-btn');
    }

    getCloseButton() {
        return document.querySelector('.close-btn');
    }
}
