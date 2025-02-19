import { EventManager } from './EventManager.js';
import { ComponentRenderer } from './ComponentRenderer.js';
import { ResourceLoader } from './ResourceLoader.js';
import { PerformanceMonitor } from './PerformanceMonitor.js';

export class GameEngine {
    constructor(config) {
        this.config = config;
        this.isRunning = false;
        this.frameHandle = null;

        this.resourceLoader = new ResourceLoader();
        this.eventManager = new EventManager(config.eventUrl);
        this.componentRenderer = new ComponentRenderer(config.resourceUrl, this.resourceLoader);
        this.performanceMonitor = new PerformanceMonitor({
            frontendAvg: 'pingAvg',
            frontendMin: 'pingMin',
            frontendMax: 'pingMax',
            backendAvg: 'backendAvg',
            backendMin: 'backendMin',
            backendMax: 'backendMax'
        });

        this.initializeEventListeners();
    }

    async start() {
        if (this.isRunning) return;
        this.isRunning = true;

        await this.initialLoad();
        this.gameLoop();
    }

    stop() {
        this.isRunning = false;
        cancelAnimationFrame(this.frameHandle);
        this.resourceLoader.clearCache();
    }

    async initialLoad() {
        const response = await this.eventManager.send('reload', {});
        if (response?.components) {
            await this.componentRenderer.render(response.components);
        }
    }

    gameLoop() {
        const loop = async () => {
            if (!this.isRunning) return;

            const startTime = performance.now();
            await this.processEvents();
            this.performanceMonitor.record('frontend', performance.now() - startTime);

            this.frameHandle = requestAnimationFrame(loop);
        };

        this.frameHandle = requestAnimationFrame(loop);
    }

    async processEvents() {
        const result = await this.eventManager.processNext();
        if (!result) return;

        if (result.elapsed) {
            this.performanceMonitor.record('backend', result.elapsed);
        }

        if (result.components) {
            await this.componentRenderer.render(result.components);
        }
    }

    initializeEventListeners() {
        document.addEventListener('click', (e) => {
            const button = e.target.closest('[data-component="button"]');
            if (button) {
                const component = this.componentRenderer.components.get(button.id);
                this.eventManager.push(component.config.event, {});
            }
        });

        window.addEventListener('beforeunload', () => this.stop());
    }
}
