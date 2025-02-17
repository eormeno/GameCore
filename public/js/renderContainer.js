function renderGameContainer(game) {
    console.log(JSON.stringify(game, null, 2));
    const container = document.getElementById('gamesContainer');

    // Crear elemento de título
    const title = document.createElement('h1');
    title.textContent = game.game.title;

    // Crear contenedor del canvas
    const canvasContainer = document.createElement('div');
    canvasContainer.id = 'glCanvas';

     // Crear botón de cierre
     const closeButton = document.createElement('button');
     closeButton.innerHTML = `
         <svg viewBox="0 0 24 24" width="24" height="24">
             <path fill="currentColor" d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/>
         </svg>
     `;
     closeButton.setAttribute('aria-label', 'Close');
     closeButton.classList.add('close-btn');

    // Estilos base
    const styles = document.createElement('style');
    styles.textContent = `
          #gamesContainer {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 90vh;
            background: linear-gradient(45deg, #1a1a1a 0%, #0a0a0a 100%);
            color: #fff;
            font-family: 'Arial Black', sans-serif;
            position: relative;
            overflow: hidden;
        }

        .close-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 0, 0, 0.3);
            border: 2px solid #ff3355;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 15px rgba(255, 51, 85, 0.3);
        }

        .close-btn:hover {
            background: rgba(255, 51, 85, 0.6);
            transform: scale(1.1);
            box-shadow: 0 0 25px rgba(255, 51, 85, 0.5);
        }

        .close-btn:active {
            transform: scale(0.9);
        }

        .close-btn svg {
            filter: drop-shadow(0 0 3px rgba(255, 51, 85, 0.5));
        }

        #gamesContainer::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                rgba(255, 255, 255, 0.1) 0%,
                rgba(255, 255, 255, 0.05) 25%,
                transparent 50%,
                rgba(0, 0, 0, 0.3) 75%,
                rgba(0, 0, 0, 0.6) 100%
            );
            pointer-events: none;
        }

        h1 {
            font-size: 2em;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 20px;
            text-shadow: 0 0 10px rgba(0, 255, 255, 0.5),
                         0 0 20px rgba(0, 255, 255, 0.3),
                         0 0 30px rgba(0, 255, 255, 0.2);
            animation: title-glow 2s ease-in-out infinite alternate;
        }

        #glCanvas {
            background-color: rgba(0, 0, 0, 0.7);
            border: 2px solid #00ffff;
            border-radius: 5px;
            box-shadow: 0 0 20px rgba(0, 255, 255, 0.3),
                       inset 0 0 15px rgba(0, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        @keyframes scanlines {
            from { transform: translateY(-50%); }
            to { transform: translateY(0%); }
        }

        .stats-panel {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(0, 0, 0, 0.7);
            border: 1px solid #00ffff;
            border-radius: 5px;
            padding: 15px;
            box-shadow: 0 0 15px rgba(0, 255, 255, 0.2),
                      inset 0 0 10px rgba(0, 255, 255, 0.1);
            z-index: 100;
            backdrop-filter: blur(3px);
        }

        .stat-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 5px 0;
            padding: 5px;
            border-bottom: 1px solid rgba(0, 255, 255, 0.1);
        }

        .stat-row:last-child {
            border-bottom: none;
        }

        .stat-label {
            font-size: 0.9em;
            color: #00ffff;
            text-shadow: 0 0 8px rgba(0, 255, 255, 0.3);
        }

        .stat-value {
            font-size: 1.1em;
            color: #fff;
            font-weight: bold;
            text-align: right;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.4);
        }
    `;

    // Crear y añadir panel de estadísticas
    const statsPanel = createStatsPanel();

    // Aplicar dimensiones del JSON
    canvasContainer.style.width = `${game.game.width}px`;
    canvasContainer.style.height = `${game.game.height}px`;

     // Evento para el botón de cierre
     closeButton.addEventListener('click', () => {
        gamesContainer.innerHTML = '';
        document.head.removeChild(styles);
        // Aquí puedes agregar lógica adicional al cerrar
    });

    // Limpiar contenedor existente y añadir elementos
    container.innerHTML = '';
    document.head.appendChild(styles);
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

    // Crear estructura de estadísticas
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

function addStatsStyles(stylesElement) {
    const statsStyles = `
        .stats-panel {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(0, 0, 0, 0.7);
            border: 1px solid #00ffff;
            border-radius: 5px;
            padding: 15px;
            box-shadow: 0 0 15px rgba(0, 255, 255, 0.2),
                      inset 0 0 10px rgba(0, 255, 255, 0.1);
            z-index: 100;
            backdrop-filter: blur(3px);
        }

        .stat-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 8px 0;
            padding: 5px;
            border-bottom: 1px solid rgba(0, 255, 255, 0.1);
        }

        .stat-row:last-child {
            border-bottom: none;
        }

        .stat-label {
            font-size: 0.9em;
            color: #00ffff;
            text-shadow: 0 0 8px rgba(0, 255, 255, 0.3);
        }

        .stat-value {
            font-size: 1.1em;
            color: #fff;
            font-weight: bold;
            text-align: right;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.4);
        }
    `;

    stylesElement.textContent += statsStyles;
}
