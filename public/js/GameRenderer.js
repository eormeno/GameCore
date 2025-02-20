class GameRenderer {
    constructor() {
        this.rootId = '';
        this.eventSent = false;
        this.currentMillis = 0;
        this.arrCachedViews = {};
        this.arrClientRenderings = [];
        this.previousMillis = 500;
        this.lowPing = 1000;
        this.highPing = 0;
        this.backendMin = 1000;
        this.backendMax = 0;
        this.stopGame = false;

        this.elementsMap = new Map();
        this.eventQueue = [];
        this.pings = [];
        this.backendMs = [];
        this.eventsAlreadyPending = [];

        this.resourceUrl = '';
        this.eventUrl = '';

        this.pingAvgElement = null;
        this.pingMinElement = null;
        this.pingMaxElement = null;
        this.pingBackendElement = null;
        this.backendMinElement = null;
        this.backendMaxElement = null;
    }

    startGame(game) {
        this.pingAvgElement = document.getElementById('pingAvg');
        this.pingMinElement = document.getElementById('pingMin');
        this.pingMaxElement = document.getElementById('pingMax');
        this.pingBackendElement = document.getElementById('backendAvg');
        this.backendMinElement = document.getElementById('backendMin');
        this.backendMaxElement = document.getElementById('backendMax');
        this.resourceUrl = game.resourcesUrl;
        this.eventUrl = game.eventUrl;
        this.pushEvent('reload', {});
        this.pullWithTimeout(100);
    }

    stopGameLoop() {
        this.stopGame = true;
    }

    resetValues() {
        this.elementsMap.clear();
        this.eventQueue.length = 0;
        this.pings.length = 0;
        this.backendMs.length = 0;
        this.eventsAlreadyPending.length = 0;
        this.arrCachedViews = {};
        this.arrClientRenderings.length = 0;
        this.previousMillis = 500;
        this.lowPing = 1000;
        this.highPing = 0;
        this.backendMin = 1000;
        this.backendMax = 0;
        this.stopGame = false;
    }

    async sendEvent(event, formData = {}, destination = null) {
        if (this.eventSent) return -1;
        this.eventSent = true;
        this.currentMillis = Date.now();
        let backendElapsed = -1;
        const token = localStorage.getItem('token');

        try {
            const eventInfo = {
                event: event || '',
                data: formData,
                rendered: this.allIdsFromMapAndVersions(),
            };
            if (destination) eventInfo.destination = destination;

            const response = await fetch(this.eventUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Authorization': `Bearer ${token}`,
                },
                body: JSON.stringify(eventInfo),
            });

            const data = await response.text();
            if (data.startsWith('<')) {
                document.write(data);
            } else {
                const json = JSON.parse(data);
                backendElapsed = json.elapsed || -1;
                delete json.elapsed;
                if (Object.keys(json).length > 0) this.renderComponents(json, 'glCanvas');
            }
        } catch (error) {
            console.error(error);
        } finally {
            this.eventSent = false;
        }
        return backendElapsed;
    }

    allIdsFromMapAndVersions() {
        return Array.from(this.elementsMap.entries()).reduce((acc, [id, element]) => {
            acc[id] = element.version || 0;
            return acc;
        }, {});
    }

    createComponent(data, mainContainer) {
        Object.entries(data).forEach(([id, component]) => {
            if (this.elementsMap.has(id)) {
                const element = this.elementsMap.get(id);
                if (component.type === 'sprite') {
                    this.fetchResourceWithCacheAndBearer(`${this.resourceUrl}/${component.texture}`, (url) => {
                        element.src = url;
                    });
                    element.style.position = 'absolute';
                    element.style.width = `${component.width * component.scale}px`;
                    element.style.height = `${component.height * component.scale}px`;
                    let x = component.x - (element.width * component.scale * component.pivot_x);
                    let y = component.y - (element.height * component.scale * component.pivot_y);
                    element.style.left = `${x}px`;
                    element.style.top = `${y}px`;
                    element.style.transform = `rotate(${component.rotation}deg)`;
                }
                if (component.updatable) this.pushEvent('update', {}, id);
                return;
            }

            let element;
            switch (component.type) {
                case 'container':
                    element = document.createElement('div');
                    element.className = component.layout || 'vertical';
                    if (component.width) element.style.width = `${component.width}px`;
                    if (component.height) element.style.height = `${component.height}px`;
                    if (component.image) {
                        this.fetchResourceWithCacheAndBearer(`${this.resourceUrl}/${component.image}`, (url) => {
                            element.style.backgroundImage = `url(${url})`;
                        });
                        element.style.backgroundSize = '100% 100%';
                        element.style.backgroundPosition = 'center';
                        element.style.backgroundRepeat = 'no-repeat';
                    }
                    if (component.x || component.y) {
                        element.style.position = 'absolute';
                        element.style.left = `${component.x}px`;
                        element.style.top = `${component.y}px`;
                    }
                    break;
                case 'label':
                    element = document.createElement('span');
                    element.textContent = component.text;
                    if (component.style) element.className = component.style;
                    break;
                case 'button':
                    element = document.createElement('button');
                    element.textContent = component.text;
                    if (component.event) element.addEventListener('click', () => this.pushEvent(component.event, {}));
                    break;
                case 'sprite':
                    element = document.createElement('img');
                    this.fetchResourceWithCacheAndBearer(`${this.resourceUrl}/${component.texture}`, (url) => {
                        element.src = url;
                    });
                    element.style.position = 'absolute';
                    element.style.width = `${component.width * component.scale}px`;
                    element.style.height = `${component.height * component.scale}px`;
                    let x = component.x - (component.width * component.scale * component.pivot_x);
                    let y = component.y - (component.height * component.scale * component.pivot_y);
                    element.style.left = `${x}px`;
                    element.style.top = `${y}px`;
                    element.style.transform = `rotate(${component.rotation}deg)`;
                    if (component.event) element.addEventListener('click', () => this.pushEvent('click', {}, id));
                    break;
                case 'sound':
                    element = document.createElement('audio');
                    element.autoplay = false;
                    element.loop = component.loop;
                    element.volume = component.volume;
                    this.fetchResourceWithCacheAndBearer(`${this.resourceUrl}/${component.sound}`, (url) => {
                        element.src = url;
                        this.playAudio(element);
                    });
                    break;
            }

            if (component.updatable) this.pushEvent('update', {}, id);
            if (!element) return;
            element.id = id;
            element.version = component.version || 0;
            this.elementsMap.set(id, element);

            if (component.parent) {
                const parentElement = this.elementsMap.get(component.parent.toString());
                parentElement?.appendChild(element);
            } else {
                mainContainer.appendChild(element);
            }
        });
    }

    fetchResourceWithCacheAndBearer(url, callback) {
        if (this.arrCachedViews[url]) callback(this.arrCachedViews[url]);
        const bearer = localStorage.getItem('token');
        fetch(url, {
            headers: { Authorization: `Bearer ${bearer}` },
        })
            .then((response) => {
                if (!response.ok) throw new Error('Error en la respuesta de la red');
                return response.blob();
            })
            .then((data) => {
                this.arrCachedViews[url] = URL.createObjectURL(data);
                callback(this.arrCachedViews[url]);
            })
            .catch((error) => console.error('Error al cargar el recurso:', error));
    }

    renderComponents(responseData, mainContainerName) {
        const mainContainer = document.getElementById(mainContainerName || 'main');
        if (!mainContainer) {
            // console.error(`No se encontró el contenedor principal con id "${mainContainerName}"`);
            return;
        }
        let deactives = responseData.deactives;
        if (deactives) {
            deactives.forEach((id) => {
                const element = this.elementsMap.get(id);
                element?.remove();
                this.elementsMap.delete(id);
            });
        }
        this.setStyles();
        this.createComponent(responseData, mainContainer);
    }

    playAudio(audio) {
        if (audio instanceof HTMLAudioElement) audio.play().catch(() => audio.play());
    }

    setStyles() {
        this.addStyles({
            "#glCanvas": {
                "width": "100%",
                "max-width": "800px",
            },
            ".vertical": {
                "display": "flex",
                "position": "relative",
                "flex-direction": "column",
                "align-items": "center",
                "width": "100%",
                "height": "100%",
            },
            ".title": {
                "font-size": "48px",
                "font-weight": "bold",
                "color": "#007bff",
                "text-shadow": "2px 2px 2px rgba(0, 0, 0, 0.5)",
                "margin": "10px 0"
            },
            ".paragraph": {
                "font-size": "20px",
                "font-weight": "normal",
                "margin": "5px 0",
                "color": "#fff",
                "text-shadow": "2px 2px 2px rgba(0, 0, 0, 0.5)"
            },
            "button": {
                "padding": "5px 10px",
                "background-color": "#007bff",
                "color": "white",
                "border": "none",
                "border-radius": "4px",
                "cursor": "pointer",
                "transition": "background-color 0.3s"
            },
            "button:hover": {
                "background-color": "#0056b3"
            }
        });
    }

    addStyles(styles) {
        let styleSheet = document.getElementById('dynamic-styles');
        if (!styleSheet) {
            styleSheet = document.createElement('style');
            styleSheet.id = 'dynamic-styles';
            document.head.appendChild(styleSheet);
        }
        let cssText = '';
        for (const selector in styles) {
            if (styles.hasOwnProperty(selector)) {
                const rules = Object.entries(styles[selector])
                    .map(([prop, value]) => `${prop}: ${value};`)
                    .join(' ');
                cssText += `${selector} { ${rules} } `;
            }
        }
        styleSheet.textContent += cssText;
    }

    async pullWithTimeout(interval) {
        const fetchData = async () => {
            const startTime = Date.now();
            try {
                if (!navigator.onLine) {
                    console.error('Disconnected');
                    return;
                }
                const { event, data, destination } = this.dequeueEvent();
                if (event) {
                    const backendElapsed = await this.sendEvent(event, data, destination);
                    this.updateBackendMetrics(backendElapsed);
                }
            } catch (error) {
                console.error('Error:', error);
            } finally {
                this.updatePingMetrics(startTime);
                if (this.stopGame) return;
                setTimeout(fetchData, interval);
            }
        };
        fetchData(10);
    }

    pushEvent(event, data = {}, destination = null) {
        if (this.eventsAlreadyPending.includes(event)) return;
        this.eventsAlreadyPending.push(event);
        this.eventQueue.push({ event, data, destination });
    }

    dequeueEvent() {
        if (this.eventQueue.length === 0) return { event: null, data: null, destination: null };
        const { event, data, destination } = this.eventQueue.shift();
        const index = this.eventsAlreadyPending.indexOf(event);
        if (index > -1) this.eventsAlreadyPending.splice(index, 1);
        return { event, data, destination };
    }

    updateBackendMetrics(backendElapsed) {
        if (backendElapsed > 0) {
            if (backendElapsed < this.backendMin) {
                this.backendMin = backendElapsed;
                this.backendMinElement.textContent = `${this.backendMin} ms`;
            }
            if (backendElapsed > this.backendMax) {
                this.backendMax = backendElapsed;
                this.backendMaxElement.textContent = `${this.backendMax} ms`;
            }
            this.backendMs.push(backendElapsed);
            if (this.backendMs.length > 10) this.backendMs.shift();
            const average = Math.round(this.backendMs.reduce((acc, curr) => acc + curr, 0) / this.backendMs.length);
            this.pingBackendElement.textContent = `${average} ms`;
        }
    }

    updatePingMetrics(startTime) {
        const endTime = Date.now();
        const elapsed = endTime - startTime;
        if (elapsed < this.lowPing) {
            this.lowPing = elapsed;
            this.pingMinElement.textContent = `${this.lowPing} ms`;
        }
        if (elapsed > this.highPing) {
            this.highPing = elapsed;
            this.pingMaxElement.textContent = `${this.highPing} ms`;
        }
        this.pings.push(elapsed);
        if (this.pings.length > 10) this.pings.shift();
        const average = Math.round(this.pings.reduce((acc, curr) => acc + curr, 0) / this.pings.length);
        this.pingAvgElement.textContent = `${average} ms`;
    }
}

export { GameRenderer };
