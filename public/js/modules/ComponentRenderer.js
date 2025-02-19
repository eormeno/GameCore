export class ComponentRenderer {
    constructor(resourceUrl, resourceLoader) {
        this.resourceUrl = resourceUrl;
        this.resourceLoader = resourceLoader;
        this.components = new Map();
    }

    async render(responseData, containerId = 'glCanvas') {
        const container = document.getElementById(containerId);
        if (!container) throw new Error(`Container ${containerId} not found`);

        this.removeDeactivated(responseData.deactives);
        await this.updateComponents(responseData, container);
        this.applyGlobalStyles();
    }

    removeDeactivated(componentIds = []) {
        componentIds.forEach(id => {
            const component = this.components.get(id);
            component?.element.remove();
            this.components.delete(id);
        });
    }

    async updateComponents(componentsData, container) {
        for (const [id, config] of Object.entries(componentsData)) {
            if (this.components.has(id)) {
                await this.updateComponent(id, config);
            } else {
                await this.createComponent(id, config, container);
            }
        }
    }

    async createComponent(id, config, container) {
        const element = await this.createElement(config);
        element.id = id;
        element.dataset.component = config.type;
        element.dataset.version = config.version || 0;

        if (config.parent) {
            const parentElement = this.components.get(config.parent)?.element;
            parentElement ? parentElement.appendChild(element) : container.appendChild(element);
        } else {
            container.appendChild(element);
        }

        this.components.set(id, { element, config });
    }

    async updateComponent(id, newConfig) {
        const { element, config } = this.components.get(id);
        if (newConfig.version <= config.version) return;

        // Actualización optimizada de propiedades
        const updaters = {
            sprite: async (el, cfg) => this.updateSprite(el, cfg),
            button: (el, cfg) => this.updateButton(el, cfg),
            // ... otros tipos
        };

        await updaters[newConfig.type]?.(element, newConfig);
        element.dataset.version = newConfig.version;
        this.components.set(id, { element, config: newConfig });
    }

    async createElement(config) {
        const creators = {
            sprite: () => this.createSprite(config),
            button: () => this.createButton(config),
            label: () => this.createLabel(config),
            container: () => this.createContainer(config),
            sound: () => this.createSound(config)
        };

        return creators[config.type]() || document.createElement('div');
    }

    async createSprite(config) {
        const img = document.createElement('img');
        img.style.position = 'absolute';
        img.src = await this.resourceLoader.load(`${this.resourceUrl}/${config.texture}`);
        this.applyTransform(img, config);
        return img;
    }

    createButton(config) {
        const button = document.createElement('button');
        button.textContent = config.text;
        button.className = config.style || '';
        return button;
    }

    applyTransform(element, config) {
        element.style.width = `${config.width * config.scale}px`;
        element.style.height = `${config.height * config.scale}px`;
        element.style.left = `${config.x - (element.offsetWidth * config.pivot_x)}px`;
        element.style.top = `${config.y - (element.offsetHeight * config.pivot_y)}px`;
        element.style.transform = `rotate(${config.rotation}deg)`;
    }

    applyGlobalStyles() {
        const styles = `
        #glCanvas {
          width: 100%;
          max-width: 800px;
        }
        .vertical-layout {
          display: flex;
          flex-direction: column;
          gap: 10px;
        }
        .game-button {
          padding: 8px 16px;
          background: #007bff;
          color: white;
          border-radius: 4px;
          transition: background 0.2s;
        }
        .game-button:hover {
          background: #0056b3;
        }
      `;

        const styleTag = document.getElementById('game-styles') || document.createElement('style');
        styleTag.id = 'game-styles';
        styleTag.textContent = styles;
        document.head.appendChild(styleTag);
    }
}
