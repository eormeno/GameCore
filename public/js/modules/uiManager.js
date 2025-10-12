/**
 * UIManager - Handles DOM manipulations and UI updates
 */
export class UIManager {
    constructor(container, shareService, navigationService, gameRenderer) {
        this.container = container;
        this.shareService = shareService;
        this.navigationService = navigationService;
        this.gameRenderer = gameRenderer;
        this.currentGameApp = null; // Store current game app data
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

    async renderFirstScreen(data) {
        const canvas = this.getCanvasContainer();
        if (!canvas) {
            console.error('Canvas container not found');
            return;
        }

        try {
            // Load the first-screen template
            const templateHtml = await this.loadTemplate('first-screen.html');

            canvas.innerHTML = templateHtml;

            // Load CSS if not already loaded
            this.loadCSS('first-screen.css');

            // Populate the template with data
            this.populateFirstScreenData(data);

            // Setup event listeners for the first screen
            this.setupFirstScreenEventListeners(data);

        } catch (error) {
            console.error('Error rendering first screen:', error);
            this.renderFirstScreenFallback(data);
        }
    }

    /**
     * Load HTML template from partials
     */
    async loadTemplate(templateName) {
        const response = await fetch(`/partials/${templateName}`);
        if (!response.ok) {
            throw new Error(`Failed to load template: ${templateName}`);
        }
        return await response.text();
    }

    /**
     * Load CSS file dynamically
     */
    loadCSS(cssFileName) {
        // Remove existing link to force reload
        const existingLink = document.querySelector(`link[href*="${cssFileName}"]`);
        if (existingLink) {
            existingLink.remove();
        }

        const link = document.createElement('link');
        link.rel = 'stylesheet';
        // Add timestamp to force cache busting
        link.href = `/css/${cssFileName}?v=${Date.now()}`;
        document.head.appendChild(link);
    }

    /**
     * Populate the first screen template with actual data
     */
    populateFirstScreenData(data) {
        const container = this.getCanvasContainer();
        if (!container) {
            console.error('Container not found in populateFirstScreenData');
            return;
        }

        // Try both structures to be safe
        const gameAppData = data.first_screen?.game_app || data.game_app;
        const defaultGameData = data.first_screen?.default_game || data.default_game;
        const openGamesData = data.first_screen?.open_games || data.open_games;

        // Populate game app data
        this.populateGameAppData(gameAppData);

        // Populate default game data
        this.populateDefaultGameData(defaultGameData);

        // Populate open games data
        this.populateOpenGamesData(openGamesData);
    }

    /**
     * Populate game app information
     */
    populateGameAppData(gameApp) {

        if (!gameApp) {
            console.log('No game app data - using defaults');
            return;
        }

        // Store game app data for later use
        this.currentGameApp = gameApp;

        // Update game name in header
        const nameElement = document.querySelector('[data-field="game-name"]');
        if (nameElement) {
            nameElement.textContent = gameApp.name || 'Unknown Game';
            console.log('Set game name to:', nameElement.textContent);
        } else {
            console.log('Game name element not found');
        }

        // Update game players info
        const playersElement = document.querySelector('[data-field="game-players"]');
        if (playersElement) {
            const minUsers = gameApp.min_users_per_instance || 1;
            const maxUsers = gameApp.max_users_per_instance || 4;
            const playersText = minUsers === maxUsers ? `👥 ${minUsers} jugador${minUsers > 1 ? 'es' : ''}` : `👥 ${minUsers}-${maxUsers} jugadores`;
            playersElement.textContent = playersText;
        }
    }

    /**
     * Populate default game information
     */
    populateDefaultGameData(defaultGame) {

        if (!defaultGame) {
            // Hide the games selection section if no default game
            const section = document.querySelector('.games-selection-section');
            if (section) {
                section.style.display = 'none';
                console.log('Hidden games selection section - no default game');
            }
            return;
        }

        // Populate game info
        this.setElementText('.game-name[data-field="name"]', defaultGame.name || 'Unknown Game');
        this.setElementText('[data-field="state"]', defaultGame.state || 'unknown');
        this.setElementText('[data-field="invitation_code"]', `🎮 ${defaultGame.invitation_code || 'No Code'}`);

        // Set game state styling
        const stateElement = document.querySelector('[data-field="state"]');
        if (stateElement) {
            stateElement.className = `game-state ${(defaultGame.state || 'unknown').toLowerCase()}`;
        }

        // Populate user info
        if (defaultGame.game_user) {
            const user = defaultGame.game_user;
            this.setElementText('[data-field="role"]', user.role || 'player');
            this.setElementText('[data-field="status"]', user.status || 'active');

            // Format relative time for last played
            const lastPlayedRelative = this.formatRelativeTime(user.last_played_at);
            this.setElementText('[data-field="last_played_relative"]', lastPlayedRelative || 'hace un tiempo');
        }

        // Set button data attributes
        const continueBtn = document.querySelector('.continue-game-btn');
        if (continueBtn) {
            continueBtn.setAttribute('data-game-id', defaultGame.id || '');
            continueBtn.setAttribute('data-events-url', defaultGame.events_url || '');
        }

        const shareBtn = document.querySelector('.default-game-section .share-game-btn');
        if (shareBtn) {
            shareBtn.setAttribute('data-invitation-code', defaultGame.invitation_code || '');
        }
    }

    /**
     * Populate open games list with dynamic number of slots based on max_instances_per_user
     */
    async populateOpenGamesData(openGames) {
        const openGamesSection = document.querySelector('.open-games-section');
        const gamesList = document.querySelector('.open-games-list');
        const gamesCount = document.querySelector('[data-field="count"]');

        // Use max_instances_per_user from current game app, fallback to 10 if not available
        const MAX_SLOTS = this.currentGameApp?.max_instances_per_user || 10;
        const actualGames = openGames || [];
        const filledSlots = Math.min(actualGames.length, MAX_SLOTS);
        const emptySlots = MAX_SLOTS - filledSlots;

        // Show games count
        if (gamesCount) {
            gamesCount.textContent = `${filledSlots}/${MAX_SLOTS} partidas`;
        }

        // Load templates
        try {
            const gameItemTemplate = await this.loadTemplate('open-game-item.html');
            const emptySlotTemplate = await this.loadTemplate('empty-game-slot.html');

            if (gamesList) {
                gamesList.innerHTML = '';

                // Add filled slots
                for (let i = 0; i < filledSlots; i++) {
                    const game = actualGames[i];
                    const gameElement = this.createGameItemElement(gameItemTemplate, game, i + 1);
                    gamesList.appendChild(gameElement);
                }

                // Add empty slots
                for (let i = filledSlots; i < MAX_SLOTS; i++) {
                    const emptyElement = this.createEmptySlotElement(emptySlotTemplate, i + 1);
                    gamesList.appendChild(emptyElement);
                }
            }
        } catch (error) {
            console.error('Error loading game templates:', error);
            // Fallback to simple rendering
            this.renderOpenGamesSimple(actualGames);
        }
    }

    /**
     * Create a game item element from template and data
     */
    createGameItemElement(template, game, slotNumber) {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = template;
        const gameElement = tempDiv.firstElementChild;

        // Populate game data
        gameElement.setAttribute('data-game-id', game.id);

        // Set slot number
        this.setElementTextInParent(gameElement, '[data-field="slot"]', slotNumber);

        this.setElementTextInParent(gameElement, '[data-field="name"]', game.name || 'Sin nombre');
        this.setElementTextInParent(gameElement, '[data-field="invitation_code"]', `🎮 ${game.invitation_code || 'N/A'}`);
        this.setElementTextInParent(gameElement, '[data-field="state"]', game.state);

        // Use last_played_at_human from game_user if available
        const lastPlayedHuman = game.game_user?.last_played_at_human || 'Nunca jugado';
        this.setElementTextInParent(gameElement, '[data-field="last_played_at_human"]', lastPlayedHuman);

        // Set button attributes
        const continueBtn = gameElement.querySelector('.continue-btn');
        if (continueBtn) {
            continueBtn.setAttribute('data-game-id', game.id);
            continueBtn.setAttribute('data-invitation-code', game.invitation_code);
            continueBtn.title = 'Continuar partida';
        }

        const shareBtn = gameElement.querySelector('.share-btn');
        if (shareBtn) {
            shareBtn.setAttribute('data-invitation-code', game.invitation_code);
            shareBtn.title = 'Compartir partida';
        }

        const deleteBtn = gameElement.querySelector('.delete-btn');
        if (deleteBtn) {
            deleteBtn.setAttribute('data-game-id', game.id);
            deleteBtn.title = 'Eliminar partida';
        }

        return gameElement;
    }

    /**
     * Create an empty slot element from template
     */
    createEmptySlotElement(template, slotNumber) {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = template;
        const slotElement = tempDiv.firstElementChild;

        // Set slot number
        this.setElementTextInParent(slotElement, '[data-field="slot"]', slotNumber);

        // Set button attributes
        const newGameBtn = slotElement.querySelector('.new-game-btn');
        if (newGameBtn) {
            newGameBtn.setAttribute('data-slot-number', slotNumber);
            newGameBtn.title = 'Crear nueva partida';
        }

        return slotElement;
    }

    /**
     * Fallback rendering for first screen
     */
    renderFirstScreenFallback(data) {
        const canvas = this.getCanvasContainer();
        if (!canvas) return;

        const container = document.createElement('div');
        container.className = 'first-screen-fallback';

        // Simple fallback rendering
        if (data?.open_games) {
            const gamesList = document.createElement('div');
            gamesList.className = 'open-games-list';

            data.open_games.forEach(game => {
                const gameItem = document.createElement('div');
                gameItem.className = 'game-item';
                gameItem.textContent = `${game.name} - ${game.createdAt} (${game.invitationCode})`;
                gamesList.appendChild(gameItem);
            });

            container.appendChild(gamesList);
        }

        canvas.innerHTML = '';
        canvas.appendChild(container);
    }

    /**
     * Utility method to set text content of an element
     */
    setElementText(selector, text) {
        const element = document.querySelector(selector);
        if (element && text !== undefined && text !== null) {
            element.textContent = text;
        }
    }

    /**
     * Utility method to set text content of an element within a parent
     */
    setElementTextInParent(parent, selector, text) {
        const element = parent.querySelector(selector);
        if (element && text !== undefined && text !== null) {
            element.textContent = text;
        }
    }

    /**
     * Format date for display
     */
    formatDate(dateString) {
        if (!dateString) return '';

        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (error) {
            return dateString;
        }
    }

    /**
     * Format relative time in a friendly way (hace X tiempo)
     */
    formatRelativeTime(dateString) {
        if (!dateString) return '';

        try {
            const date = new Date(dateString);
            const now = new Date();
            const diffMs = now - date;
            const diffSeconds = Math.floor(diffMs / 1000);
            const diffMinutes = Math.floor(diffSeconds / 60);
            const diffHours = Math.floor(diffMinutes / 60);
            const diffDays = Math.floor(diffHours / 24);
            const diffWeeks = Math.floor(diffDays / 7);
            const diffMonths = Math.floor(diffDays / 30);
            const diffYears = Math.floor(diffDays / 365);

            // Casos especiales para tiempos muy recientes
            if (diffSeconds < 10) return 'recién';
            if (diffSeconds < 60) return 'hace unos segundos';
            if (diffMinutes === 1) return 'hace un minuto';
            if (diffMinutes < 60) return `hace ${diffMinutes} minutos`;
            if (diffHours === 1) return 'hace una hora';
            if (diffHours < 24) return `hace ${diffHours} horas`;
            if (diffDays === 1) return 'ayer';
            if (diffDays === 2) return 'anteayer';
            if (diffDays < 7) return `hace ${diffDays} días`;
            if (diffWeeks === 1) return 'hace una semana';
            if (diffWeeks < 4) return `hace ${diffWeeks} semanas`;
            if (diffMonths === 1) return 'hace un mes';
            if (diffMonths < 12) return `hace ${diffMonths} meses`;
            if (diffYears === 1) return 'hace un año';
            return `hace ${diffYears} años`;

        } catch (error) {
            console.error('Error formatting relative time:', error);
            return this.formatDate(dateString);
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
     * Setup first screen specific event listeners
     */
    setupFirstScreenEventListeners(data) {
        // Continue game button
        const continueBtn = document.querySelector('.continue-game-btn');
        if (continueBtn) {
            continueBtn.addEventListener('click', (e) => this.handleContinueGame(e));
        }

        // New game button
        const newGameBtn = document.querySelector('.new-game-btn');
        if (newGameBtn) {
            newGameBtn.addEventListener('click', (e) => this.handleNewGame(e));
        }

        // Continue game buttons (from open games list)
        const continueBtns = document.querySelectorAll('.continue-game-btn');
        continueBtns.forEach(btn => {
            btn.addEventListener('click', (e) => this.handleContinueGame(e));
        });

        // Share game buttons
        const shareBtns = document.querySelectorAll('.share-game-btn');
        shareBtns.forEach(btn => {
            btn.addEventListener('click', (e) => this.handleShareGame(e));
        });

        // End game buttons
        const endBtns = document.querySelectorAll('.end-game-btn');
        endBtns.forEach(btn => {
            btn.addEventListener('click', (e) => this.handleEndGame(e));
        });

        // New game buttons (empty slots)
        const newGameBtns = document.querySelectorAll('.new-game-btn');
        newGameBtns.forEach(btn => {
            btn.addEventListener('click', (e) => this.handleNewGame(e));
        });

        // Game item click handlers (only for filled slots)
        const gameItems = document.querySelectorAll('.open-game-item:not(.empty-slot)');
        gameItems.forEach(item => {
            item.addEventListener('click', (e) => this.handleGameItemClick(e));
        });
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

    // First Screen Event Handlers

    /**
     * Handle continue game button click
     */
    handleContinueGame(event) {
        event.preventDefault();
        event.stopPropagation();

        const button = event.target;
        const gameId = button.getAttribute('data-game-id');
        const eventsUrl = button.getAttribute('data-events-url');

        if (gameId) {
            // Disable button to prevent double clicks
            button.disabled = true;
            button.textContent = 'Cargando...';

            // Navigate to the game
            this.continueGame(gameId, eventsUrl);
        }
    }

    /**
     * Handle new game button click
     */
    handleNewGame(event) {
        event.preventDefault();
        event.stopPropagation();

        // Navigate to new game creation
        if (this.navigationService?.createNewGame) {
            this.navigationService.createNewGame();
        } else {
            // Fallback: reload or navigate to games
            this.navigationService?.navigateToGames();
        }
    }

    /**
     * Handle join game button click
     */
    handleJoinGame(event) {
        event.preventDefault();
        event.stopPropagation();

        const button = event.target;
        const gameId = button.getAttribute('data-game-id');
        const invitationCode = button.getAttribute('data-invitation-code');

        if (gameId || invitationCode) {
            // Disable button to prevent double clicks
            button.disabled = true;
            button.textContent = 'Uniéndose...';

            // Join the game
            this.joinGame(gameId, invitationCode);
        }
    }

    /**
     * Handle share game button click
     */
    handleShareGame(event) {
        event.preventDefault();
        event.stopPropagation();

        const button = event.target;
        const invitationCode = button.getAttribute('data-invitation-code');

        if (invitationCode && this.shareService) {
            this.shareService.share(invitationCode);
        }
    }

    /**
     * Handle end game button click
     */
    handleEndGame(event) {
        event.preventDefault();
        event.stopPropagation();

        const button = event.target;
        const gameId = button.getAttribute('data-game-id');

        if (gameId) {
            // Show confirmation dialog
            const confirmed = confirm('¿Estás seguro de que quieres terminar esta partida? Esta acción no se puede deshacer.');

            if (confirmed) {
                // Disable button to prevent double clicks
                button.disabled = true;
                button.textContent = '⏳';

                // End the game
                this.endGame(gameId);
            }
        }
    }

    /**
     * Handle game item click
     */
    handleGameItemClick(event) {
        // Only handle click if it's not on a button
        if (event.target.closest('button')) return;

        const gameItem = event.target.closest('.open-game-item');
        if (gameItem) {
            const gameId = gameItem.getAttribute('data-game-id');
            if (gameId) {
                // Add visual feedback
                gameItem.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    gameItem.style.transform = '';
                }, 150);

                // Navigate to game details or join directly
                this.showGameDetails(gameId);
            }
        }
    }

    /**
     * Continue playing an existing game
     */
    continueGame(gameId, eventsUrl) {
        // This would typically make an API call to continue the game
        // For now, we'll use the navigation service
        if (this.navigationService?.continueGame) {
            this.navigationService.continueGame(gameId, eventsUrl);
        } else {
            // Fallback: construct URL and navigate
            const gameUrl = `/games/${gameId}/play`;
            window.location.href = gameUrl;
        }
    }

    /**
     * Join an existing game
     */
    joinGame(gameId, invitationCode) {
        // This would typically make an API call to join the game
        if (this.navigationService?.joinGame) {
            this.navigationService.joinGame(gameId, invitationCode);
        } else {
            // Fallback: construct URL and navigate
            const joinUrl = invitationCode
                ? `/games/join/${invitationCode}`
                : `/games/${gameId}/join`;
            window.location.href = joinUrl;
        }
    }

    /**
     * Show game details modal or page
     */
    showGameDetails(gameId) {
        // This could show a modal with game details
        // or navigate to a game details page
        if (this.navigationService?.showGameDetails) {
            this.navigationService.showGameDetails(gameId);
        } else {
            // Fallback: navigate to game page
            window.location.href = `/games/${gameId}`;
        }
    }

    /**
     * Fallback simple rendering for open games
     */
    renderOpenGamesSimple(openGames) {
        const gamesList = document.querySelector('.open-games-list');
        if (!gamesList) return;

        gamesList.innerHTML = '';

        openGames.forEach(game => {
            const gameItem = document.createElement('div');
            gameItem.className = 'open-game-item simple';
            gameItem.setAttribute('data-game-id', game.id);

            gameItem.innerHTML = `
                <div class="game-item-header">
                    <div class="game-item-info">
                        <span class="game-item-name">${game.name}</span>
                        <span class="game-item-code">${game.invitationCode}</span>
                    </div>
                    <div class="game-item-meta">
                        <span class="game-item-state">${game.state}</span>
                        <span class="game-item-users">👥 ${game.users}</span>
                    </div>
                </div>
                <div class="game-item-footer">
                    <span class="game-item-date">${this.formatDate(game.createdAt)}</span>
                    <div class="game-item-actions">
                        <button class="btn btn-sm btn-primary join-game-btn" 
                                data-game-id="${game.id}" 
                                data-invitation-code="${game.invitationCode}">
                            Unirse
                        </button>
                        <button class="btn btn-sm btn-secondary share-game-btn" 
                                data-invitation-code="${game.invitationCode}">
                            Compartir
                        </button>
                    </div>
                </div>
            `;

            gamesList.appendChild(gameItem);
        });

        // Re-setup event listeners for the simple rendered elements
        this.setupFirstScreenEventListeners();
    }

    /**
     * End a game (terminate/delete)
     */
    endGame(gameId) {
        // This would typically make an API call to end the game
        // For now, we'll use a placeholder implementation

        if (this.navigationService?.endGame) {
            this.navigationService.endGame(gameId);
        } else {
            // Fallback: make API call directly
            this.makeEndGameRequest(gameId);
        }
    }

    /**
     * Make API request to end game
     */
    async makeEndGameRequest(gameId) {
        try {
            const response = await fetch(`/api/games/${gameId}/end`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                // Remove the game item from the UI and replace with empty slot
                this.removeGameFromUI(gameId);

                // Show success message
                this.showTemporaryMessage('Partida terminada exitosamente', 'success');
            } else {
                throw new Error('Failed to end game');
            }
        } catch (error) {
            console.error('Error ending game:', error);

            // Re-enable button on error
            const endBtn = document.querySelector(`[data-game-id="${gameId}"].end-game-btn`);
            if (endBtn) {
                endBtn.disabled = false;
                endBtn.textContent = '🗑️';
            }

            this.showTemporaryMessage('Error al terminar la partida', 'error');
        }
    }

    /**
     * Remove game from UI and replace with empty slot
     */
    async removeGameFromUI(gameId) {
        const gameItem = document.querySelector(`.open-game-item[data-game-id="${gameId}"]`);
        if (gameItem) {
            // Get slot number
            const slotNumberElement = gameItem.querySelector('.game-slot-number');
            const slotNumber = slotNumberElement ? slotNumberElement.textContent : '1';

            try {
                // Load empty slot template
                const emptySlotTemplate = await this.loadTemplate('empty-game-slot.html');
                const emptyElement = this.createEmptySlotElement(emptySlotTemplate, slotNumber);

                // Replace game item with empty slot
                gameItem.parentNode.replaceChild(emptyElement, gameItem);

                // Re-setup event listeners for the new empty slot
                this.setupFirstScreenEventListeners();

                // Update games count
                this.updateGamesCount();

            } catch (error) {
                console.error('Error replacing with empty slot:', error);
                // Fallback: just remove the item
                gameItem.remove();
            }
        }
    }

    /**
     * Update games count display
     */
    updateGamesCount() {
        const gamesCount = document.querySelector('[data-field="count"]');
        if (gamesCount) {
            const filledSlots = document.querySelectorAll('.open-game-item:not(.empty-slot)').length;
            gamesCount.textContent = `${filledSlots}/10 partidas`;
        }
    }

    /**
     * Show temporary message to user
     */
    showTemporaryMessage(message, type = 'info') {
        // Create message element
        const messageEl = document.createElement('div');
        messageEl.className = `temp-message temp-message-${type}`;
        messageEl.textContent = message;

        // Style the message
        Object.assign(messageEl.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            padding: '12px 20px',
            borderRadius: '6px',
            color: 'white',
            fontWeight: '500',
            zIndex: '9999',
            transition: 'all 0.3s ease'
        });

        // Set color based on type
        switch (type) {
            case 'success':
                messageEl.style.background = 'linear-gradient(90deg, #4caf50, #45a049)';
                break;
            case 'error':
                messageEl.style.background = 'linear-gradient(90deg, #f44336, #d32f2f)';
                break;
            default:
                messageEl.style.background = 'linear-gradient(90deg, #ff5722, #e64a19)';
        }

        // Add to document
        document.body.appendChild(messageEl);

        // Remove after 3 seconds
        setTimeout(() => {
            messageEl.style.opacity = '0';
            messageEl.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (messageEl.parentNode) {
                    messageEl.parentNode.removeChild(messageEl);
                }
            }, 300);
        }, 3000);
    }
}
