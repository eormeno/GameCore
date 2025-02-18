function renderGameContainer(game) {
    const container = document.getElementById('gamesContainer');

    const title = document.createElement('h1');
    title.textContent = game.title;

    const canvasContainer = document.createElement('div');
    canvasContainer.id = 'glCanvas';

     const closeButton = document.createElement('button');
     closeButton.innerHTML = `
         <svg viewBox="0 0 24 24" width="24" height="24">
             <path fill="currentColor" d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/>
         </svg>
     `;
     closeButton.setAttribute('aria-label', 'Close');
     closeButton.classList.add('close-btn');

    const statsPanel = createStatsPanel();

    canvasContainer.style.width = `${game.width}px`;
    canvasContainer.style.height = `${game.height}px`;

     closeButton.addEventListener('click', () => {
        gamesContainer.innerHTML = '';
        stopGameLoop();
        setPageState(initialState.name, initialState.data);
    });

    container.innerHTML = '';
    container.appendChild(statsPanel);
    container.appendChild(closeButton);
    container.appendChild(title);
    container.appendChild(canvasContainer);
}

function createStatsPanel() {
    const statsPanel = document.createElement('div');
    statsPanel.className = 'stats-panel';

    const statsData = [
        { label: 'Ping Avg:', id: 'pingAvg' },
        { label: 'Ping Min:', id: 'pingMin' },
        { label: 'Ping Max:', id: 'pingMax' },
        { label: 'Back Avg:', id: 'backendAvg' },
        { label: 'Back Max:', id: 'backendMax' },
        { label: 'Back Min:', id: 'backendMin' }
    ];

    statsData.forEach(({ label, id }) => {
        const container = document.createElement('div');
        container.className = 'stat-row';

        const labelElement = document.createElement('div');
        labelElement.className = 'stat-label';
        labelElement.textContent = label;

        const valueElement = document.createElement('div');
        valueElement.className = 'stat-value';
        valueElement.id = id;
        valueElement.textContent = '0';

        container.appendChild(labelElement);
        container.appendChild(valueElement);
        statsPanel.appendChild(container);
    });

    return statsPanel;
}
