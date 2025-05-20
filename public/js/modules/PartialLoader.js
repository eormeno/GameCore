class PartialLoader {
    static #instance = null;
    static #DEFAULT_TTL = 5 * 60 * 1000; // 5 minutos en milisegundos
    static #cache = new Map();
    static #useCache = true;

    constructor() {
        if (PartialLoader.#instance) {
            return PartialLoader.#instance;
        }
        PartialLoader.#instance = this;
    }

    async loadPartial(file, container, params = {}) {
        try {
            const html = await this._getCachedContent(file);
            this._injectContent(html, container, params);
            this._replaceScripts(container);
        } catch (error) {
            this._handleError(error, file);
            throw error;
        }
    }

    async _getCachedContent(file) {
        if (PartialLoader.#useCache && PartialLoader.#cache.has(file)) {
            const entry = PartialLoader.#cache.get(file);
            if (entry.expiration > Date.now()) return entry.html;
            PartialLoader.#cache.delete(file);
        }

        const fetchPromise = this._fetchPartial(file);
        PartialLoader.#cache.set(file, {
            html: fetchPromise,
            expiration: Date.now() + PartialLoader.#DEFAULT_TTL
        });

        const html = await fetchPromise;
        PartialLoader.#cache.set(file, {
            html,
            expiration: Date.now() + PartialLoader.#DEFAULT_TTL
        });

        return html;
    }

    async _fetchPartial(file) {
        const response = await fetch(`/partials/${file}.html`);
        if (!response.ok) throw new Error(`HTTP ${response.status} - ${file}`);
        return response.text();
    }

    _injectContent(html, container, params) {
        container.innerHTML = html;
        container._partialParams = params;
    }

    _replaceScripts(container) {
        container.querySelectorAll('script').forEach(oldScript => {
            const newScript = this._createScriptClone(oldScript);
            const isModule = newScript.type === 'module';

            if (isModule) {
                // Cargar el script como módulo dinámico y ejecutar función
                const blob = new Blob([newScript.textContent], { type: 'text/javascript' });
                const url = URL.createObjectURL(blob);

                import(url).then(mod => {
                    if (typeof mod.render === 'function') {
                        mod.render(container);
                    }
                    URL.revokeObjectURL(url);
                });
            } else {
                // Script clásico
                oldScript.parentNode.replaceChild(newScript, oldScript);
            }
        });
    }

    _createScriptClone(oldScript) {
        const newScript = document.createElement('script');
        Array.from(oldScript.attributes).forEach(attr => {
            newScript.setAttribute(attr.name, attr.value);
        });
        newScript.textContent = oldScript.textContent;
        return newScript;
    }

    _handleError(error, file) {
        console.error(`Error loading partial '${file}':`, error);
        // Opcional: Implementar notificación de error global
    }

    static clearCache() {
        this.#cache.clear();
    }

    static removeFromCache(file) {
        this.#cache.delete(file);
    }

    static setDefaultTTL(ttlMs) {
        if (typeof ttlMs !== 'number' || ttlMs < 0) {
            throw new Error('TTL must be a positive number');
        }
        this.#DEFAULT_TTL = ttlMs;
    }
}

// Exportar instancia única y congelada
export const partialLoader = Object.freeze(new PartialLoader());
