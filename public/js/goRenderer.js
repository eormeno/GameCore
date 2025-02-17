var rootId = '';
var eventSent = false;
var currentMillis = 0;
var arrCachedViews = {};
var arrClientRenderings = [];
var previousMillis = 500;
let pingAvgElement;
let pingMinElement;
let pingMaxElement;
let pingBackendElement;
let backendMaxElement;
let backendMinElement;
let lowPing = 1000;
let highPing = 0;
let backendMin = 1000;
let backendMax = 0;
const elementsMap = new Map();
const eventQueue = [];
const pings = [];
const backendMs = [];
let eventsAlreadyPending = [];
let resourceUrl = '';
let eventUrl = '';
let stopGame = false;

function startGame(game) {
	pingAvgElement = document.getElementById('pingAvg');
	pingMinElement = document.getElementById('pingMin');
	pingMaxElement = document.getElementById('pingMax');
	pingBackendElement = document.getElementById('backendAvg');
	backendMinElement = document.getElementById('backendMin');
	backendMaxElement = document.getElementById('backendMax');
	// resourceUrl = document.getElementById('routeDiv').getAttribute('resourceUrl');
    resourceUrl=game.resourcesUrl;
    eventUrl=game.eventUrl;
	pushEvent('reload', {});
	pullWithTimeout(10);
}

function stopGameLoop() {
    stopGame = true;
}

function resetValues() {
    elementsMap.clear();
    eventQueue.length = 0;
    pings.length = 0;
    backendMs.length = 0;
    eventsAlreadyPending.length = 0;
    arrCachedViews = {};
    arrClientRenderings.length = 0;
    previousMillis = 500;
    lowPing = 1000;
    highPing = 0;
    backendMin = 1000;
    backendMax = 0;
    stopGame = false;
}

async function sendEvent(event, formData = {}, destination = null) {
	if (eventSent) {
		return -1;
	}
	eventSent = true;
	currentMillis = Date.now();
	backendElapsed = -1;

	event = event || '';
	// var routeDiv = document.getElementById('routeDiv');
	// var route = routeDiv.getAttribute('route');
	// var token = routeDiv.getAttribute('token');
    var token = localStorage.getItem('token');

	try {
		eventInfo = {
			event: event,
			data: formData,
			rendered: allIdsFromMapAndVersions(),
		};
		if (destination) {
			eventInfo.destination = destination;
		}
		const response = await fetch(eventUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-CSRF-TOKEN': token,
                'Authorization': `Bearer ${token}`
			},
			body: JSON.stringify(eventInfo)
		});

		const data = await response.text();

		if (data.startsWith('<')) {
			document.write(data);
		} else {
			const json = JSON.parse(data);
			backendElapsed = json.elapsed || -1;
			delete json.elapsed;
			if (Object.keys(json).length > 0) {
				// let stringified = JSON.stringify(json, null, 2);
				// console.log(stringified);
				renderComponents(json, 'glCanvas');
			}
		}
	} catch (error) {
		console.error(error);
	} finally {
		eventSent = false;
	}
	return backendElapsed;
}

function allIdsFromMapAndVersions() {
	// return pairs of id and version { 1: 0, 2: 1, 3: 0 }
	return Array.from(elementsMap.entries()).reduce((acc, [id, element]) => {
		acc[id] = element.version || 0;
		return acc;
	}, {});
}

function createComponent(data, mainContainer) {
	Object.entries(data).forEach(([id, component]) => {
		if (elementsMap.has(id)) {
			// find the element and update it
			const element = elementsMap.get(id);
			if (component.type === 'sprite') {
                fetchResourceWithCacheAndBearer(`${resourceUrl}/${component.texture}`, (url) => {
                    element.src = url;
                });
				element.style.position = 'absolute';
				element.style.width = component.width * component.scale + 'px';
				element.style.height = component.height * component.scale + 'px';
				let x = component.x - (element.width * component.scale * component.pivot_x);
				let y = component.y - (element.height * component.scale * component.pivot_y);
				element.style.left = x + 'px';
				element.style.top = y + 'px';
				element.style.transform = `rotate(${component.rotation}deg)`;
				// let x = component.x - element.width * component.scale;
				// let y = component.y - element.height * component.scale;
				// element.src = `res/${component.texture}`;
				// element.style.left = x + 'px';
				// element.style.top = y + 'px';
				// element.style.transform = `scale(${component.scale}) rotate(${component.rotation}deg)`;
			}
			if (component.updatable) {
				pushEvent('update', {}, id);
			}
			return;
		}

		if (id === 'actives' || id == 'elapsed' || id == 'root' || id == 'deactives') return;

		let element;

		switch (component.type) {
			case 'container':
				element = document.createElement('div');
				element.className = component.layout || 'vertical';
				if (component.width) element.style.width = component.width;
				if (component.height) element.style.height = component.height;
				if (component.image) {
                    fetchResourceWithCacheAndBearer(`${resourceUrl}/${component.image}`, (url) => {
                        element.style.backgroundImage = `url(${url})`;
                    });
					// element.style.backgroundImage = `url(${resourceUrl}/${component.image})`;
					element.style.backgroundSize = 'cover';
					element.style.backgroundPosition = 'center';
				}
				if (component.x || component.y) {
					element.style.position = 'absolute';
					element.style.left = component.x + 'px';
					element.style.top = component.y + 'px';
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
				if (component.event) {
					element.addEventListener('click', () => pushEvent(component.event, {}));
				}
				break;

			case 'sprite':
				element = document.createElement('img');
                fetchResourceWithCacheAndBearer(`${resourceUrl}/${component.texture}`, (url) => {
                    element.src = url;
                });
				element.style.position = 'absolute';
				element.style.width = component.width * component.scale + 'px';
				element.style.height = component.height * component.scale + 'px';
				let x = component.x - (element.width * component.scale * component.pivot_x);
				let y = component.y - (element.height * component.scale * component.pivot_y);
				element.style.left = x + 'px';
				element.style.top = y + 'px';
				element.style.transform = `rotate(${component.rotation}deg)`;
				element.addEventListener('mouseenter', () => {
					element.style.filter = `brightness(1.2)`;
				});
				element.addEventListener('mouseleave', () => {
					element.style.filter = 'none';
				});
				// on click event
				if (component.event) {
					element.addEventListener('click', () => pushEvent('click', {}, id));
				}

				break;

			case 'sound':
				element = document.createElement('audio');
				element.autoplay = false;
				element.loop = component.loop;
				element.volume = component.volume;
                fetchResourceWithCacheAndBearer(`${resourceUrl}/${component.sound}`, (url) => {
                    element.src = url;
                    playAudio(element);
                });
				break;
		}

		if (component.updatable) {
			pushEvent('update', {}, id);
		}

		if (!element) {
			return;
		}

		element.id = id;
		element.version = component.version || 0;
		elementsMap.set(id, element);

		if (component.parent) {
			const parentElement = elementsMap.get(component.parent.toString());
			parentElement?.appendChild(element);
		} else {
			// Agregar al contenedor principal
			mainContainer.appendChild(element);
		}

	});
}

function fetchResourceWithCacheAndBearer(url, callback) {
    if (arrCachedViews[url]) {
        callback(arrCachedViews[url]);
    }
    const bearer = localStorage.getItem('token');
    return fetch(url, {
        headers: {
            'Authorization': `Bearer ${bearer}`
        }
    }).then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta de la red');
        }
        return response.blob();
    }).then(data => {
        arrCachedViews[url] = URL.createObjectURL(data);
        callback(arrCachedViews[url]);
    }
    ).catch(error => {
        console.error('Error al cargar el recurso:', error);
    });
}

// function handleEvent(eventType, data, destination) {
// 	pushEvent(eventType, data, destination);
// }

function renderComponents(responseData, mainContainerName) {
	const mainContainer = document.getElementById(mainContainerName || 'main');
	if (!mainContainer) {
		console.error(`No se encontró el contenedor principal con id "${mainContainerName}"`);
		return;
	}
	let deactives = responseData.deactives;
	if (deactives) {
		deactives.forEach(id => {
			const element = elementsMap.get(id);
			element?.remove();
			elementsMap.delete(id);
		});
	}
	setStyles();
	createComponent(responseData, mainContainer);
}

function playAudio(audio) {
	if (audio instanceof HTMLAudioElement) {
		audio.play().catch(error => {
			// Simulate user interaction to play audio
			audio.play();
		});
	}
}

function setStyles() {
	addStyles({
		"#glCanvas": {
			"width": "100%",
			"max-width": "800px",
		},
		".vertical": {
			"display": "flex",
			"position": "relative",
			"flex-direction": "column",
			"align-items": "center",
			"width": "100%"
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

function addStyles(styles) {
	let styleSheet = document.getElementById("dynamic-styles");

	// Si no existe el <style>, lo creamos y lo agregamos al <head>
	if (!styleSheet) {
		styleSheet = document.createElement("style");
		styleSheet.id = "dynamic-styles";
		document.head.appendChild(styleSheet);
	}

	// Convertimos el objeto de estilos en reglas CSS y las agregamos al <style>
	let cssText = "";
	for (const selector in styles) {
		if (styles.hasOwnProperty(selector)) {
			const rules = Object.entries(styles[selector])
				.map(([prop, value]) => `${prop}: ${value};`)
				.join(" ");
			cssText += `${selector} { ${rules} } `;
		}
	}

	styleSheet.textContent += cssText;
}

const pullWithTimeout = async (interval) => {

	const fetchData = async () => {
		const startTime = Date.now();
		try {
			if (!navigator.onLine) {
				console.error('Disconnected');
				return;
			}
			const { event, data, destination } = dequeueEvent();
			if (event) {
				backendElapsed = await sendEvent(event, data, destination);
				updateBackendMetrics(backendElapsed);
			}
		} catch (error) {
			console.error('Error:', error);
		} finally {
			updatePingMetrics(startTime);
            if (stopGame) return;
			setTimeout(fetchData, interval);
		}
	};

	fetchData(10);
};

function pushEvent(event, data = {}, destination = null) {
	if (eventsAlreadyPending.includes(event)) {
		return;
	}
	eventsAlreadyPending.push(event);
	eventQueue.push({ event, data, destination });
}

function dequeueEvent() {
	if (eventQueue.length === 0) {
		return { event: null, data: null, destination: null };
	}
	const { event, data, destination } = eventQueue.shift();
	// Remove the event from the pending list
	const index = eventsAlreadyPending.indexOf(event);
	if (index > -1) {
		eventsAlreadyPending.splice(index, 1);
	}
	return { event, data, destination };
}

function updateBackendMetrics(backendElapsed) {
	if (backendElapsed > 0) {
		if (backendElapsed < backendMin) {
			backendMin = backendElapsed;
			backendMinElement.textContent = `${backendMin} ms`;
		}
		if (backendElapsed > backendMax) {
			backendMax = backendElapsed;
			backendMaxElement.textContent = `${backendMax} ms`;
		}
		backendMs.push(backendElapsed);
		if (backendMs.length > 10) {
			backendMs.shift();
		}
		const average = Math.round(backendMs.reduce((acc, curr) => acc + curr, 0) / backendMs.length);
		pingBackendElement.textContent = `${average} ms`;
	}
}

function updatePingMetrics(startTime) {
	const endTime = Date.now();
	const elapsed = endTime - startTime;
	if (elapsed < lowPing) {
		lowPing = elapsed;
		pingMinElement.textContent = `${lowPing} ms`;
	}
	if (elapsed > highPing) {
		highPing = elapsed;
		pingMaxElement.textContent = `${highPing} ms`;
	}
	pings.push(elapsed);
	if (pings.length > 10) {
		pings.shift();
	}
	const average = Math.round(pings.reduce((acc, curr) => acc + curr, 0) / pings.length);
	pingAvgElement.textContent = `${average} ms`;
}
