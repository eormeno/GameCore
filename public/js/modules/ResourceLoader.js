export class ResourceLoader {
    constructor() {
        this.cache = new Map();
    }

    async load(url) {
        if (this.cache.has(url)) return this.cache.get(url);

        try {
            const response = await fetch(url, {
                headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
            });

            const blob = await response.blob();
            const objectURL = URL.createObjectURL(blob);
            this.cache.set(url, objectURL);
            return objectURL;
        } catch (error) {
            console.error('Failed to load resource:', url, error);
            return '';
        }
    }

    clearCache() {
        this.cache.forEach(url => URL.revokeObjectURL(url));
        this.cache.clear();
    }
}
