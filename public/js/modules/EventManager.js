export class EventManager {
    constructor(eventUrl) {
        this.eventQueue = [];
        this.pendingEvents = new Set();
        this.eventUrl = eventUrl;
        this.isProcessing = false;
    }

    push(event, data = {}, destination = null) {
        if (!this.pendingEvents.has(event)) {
            this.pendingEvents.add(event);
            this.eventQueue.push({ event, data, destination });
        }
    }

    async processNext() {
        if (this.isProcessing || this.eventQueue.length === 0) return null;
        this.isProcessing = true;

        const { event, data, destination } = this.eventQueue.shift();
        try {
            const result = await this.send(event, data, destination);
            return result;
        } finally {
            this.pendingEvents.delete(event);
            this.isProcessing = false;
        }
    }

    async send(event, data, destination) {
        const token = localStorage.getItem('token');
        const eventInfo = {
            event,
            data,
            destination,
            rendered: this.getRenderedComponents()
        };

        try {
            const response = await fetch(this.eventUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify(eventInfo)
            });

            return this.handleResponse(response);
        } catch (error) {
            console.error('Event error:', error);
            return null;
        }
    }

    getRenderedComponents() {
        return Array.from(document.querySelectorAll('[data-component]'))
            .reduce((acc, el) => {
                acc[el.id] = parseInt(el.dataset.version) || 0;
                return acc;
            }, {});
    }

    async handleResponse(response) {
        const text = await response.text();
        if (text.startsWith('<')) {
            document.open();
            document.write(text);
            document.close();
            return null;
        }

        const data = JSON.parse(text);
        return {
            components: data,
            elapsed: data.elapsed || null
        };
    }
}
