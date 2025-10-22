// ==================== Base Component Class ====================
class UIComponent {
    constructor(id, config) {
        this.id = id;
        this.config = config;
        this.element = null;
    }

    render() {
        // Override in subclasses
        throw new Error('render() must be implemented by subclass');
    }

    mount(parentElement) {
        if (!this.element) {
            this.element = this.render();
        }
        if (parentElement) {
            parentElement.appendChild(this.element);
        }
    }

    applyCommonAttributes(element) {
        element.setAttribute('data-component-id', this.id);
        if (this.config.name) {
            element.id = this.config.name;
        }
        return element;
    }
}

// ==================== Container Component ====================
class ContainerComponent extends UIComponent {
    render() {
        const container = document.createElement('div');
        container.className = `ui-container ${this.config.layout || 'vertical'}`;
        
        if (this.config.title) {
            const title = document.createElement('div');
            title.className = 'title';
            title.textContent = this.config.title;
            container.appendChild(title);
        }

        return this.applyCommonAttributes(container);
    }
}

// ==================== Button Component ====================
class ButtonComponent extends UIComponent {
    render() {
        const button = document.createElement('button');
        button.className = `ui-button ${this.config.style || 'primary'}`;
        button.textContent = this.config.label || 'Button';
        button.disabled = !this.config.enabled;

        if (this.config.action) {
            button.addEventListener('click', () => {
                console.log('Button action:', this.config.action, this.config.parameters || {});
                this.handleAction(this.config.action, this.config.parameters);
            });
        }

        if (this.config.tooltip) {
            button.title = this.config.tooltip;
        }

        return this.applyCommonAttributes(button);
    }

    handleAction(action, parameters = {}) {
        // Send POST request to backend
        this.sendEventToBackend('click', action, parameters);
    }

    /**
     * Send UI event to backend
     * 
     * @param {string} event - Event type (click, change, etc.)
     * @param {string} action - Action name (snake_case)
     * @param {object} parameters - Event parameters
     */
    async sendEventToBackend(event, action, parameters = {}) {
        try {
            // Get CSRF token from meta tag
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            console.log('Sending event:', { component_id: this.id, action, csrfToken });

            const response = await fetch('/api/ui-event', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    component_id: parseInt(this.id),
                    event: event,
                    action: action,
                    parameters: parameters,
                }),
            });

            const result = await response.json();

            // ÉXITO: response.ok = true (status 200-299)
            if (response.ok) {
                console.log('✅ Action executed:', action, result);
                
                // Handle UI updates if provided
                if (result.ui_update) {
                    this.handleUIUpdate(result.ui_update);
                }
                
                // Show success message if provided
                if (result.message) {
                    this.showNotification(result.message, 'success');
                }

                // Handle redirects if provided
                if (result.redirect) {
                    window.location.href = result.redirect;
                }
            } else {
                // ERROR: response.ok = false (status 400+)
                console.error('❌ Action failed:', action, result);
                this.showNotification(result.error || 'Action failed', 'error');
            }
        } catch (error) {
            console.error('❌ Network error:', error);
            this.showNotification('Network error: ' + error.message, 'error');
        }
    }

    /**
     * Show notification to user
     * 
     * @param {string} message - Message to display
     * @param {string} type - Type (success, error, info, warning)
     */
    showNotification(message, type = 'info') {
        // Simple console notification for now
        // TODO: Implement proper UI notification system
        const emoji = { success: '✅', error: '❌', info: 'ℹ️', warning: '⚠️' }[type] || 'ℹ️';
        console.log(`${emoji} ${message}`);
    }

    /**
     * Handle UI updates from backend
     * 
     * Protocolo:
     * - AGREGAR: { id: { type, text, parent, ... } } ← tiene parent
     * - ACTUALIZAR: { id: { text: "nuevo" } } ← solo props modificadas
     * - ELIMINAR: { id: { parent: null } } ← parent = null
     * 
     * @param {object} uiUpdate - UI update configuration
     */
    handleUIUpdate(uiUpdate) {
        console.log('📦 Processing UI updates:', uiUpdate);
        
        for (const [id, changes] of Object.entries(uiUpdate)) {
            const element = document.querySelector(`[data-component-id="${id}"]`);
            
            // CASO 1: ELIMINAR (parent = null)
            if (changes.parent === null) {
                if (element) {
                    console.log(`🗑️ Removing component ${id}`);
                    element.remove();
                }
                continue;
            }
            
            // CASO 2: AGREGAR (tiene parent y elemento no existe)
            if (!element && changes.parent) {
                console.log(`➕ Adding component ${id}`, changes);
                this.addComponent(id, changes);
                continue;
            }
            
            // CASO 3: ACTUALIZAR (elemento existe y hay cambios)
            if (element) {
                console.log(`✏️ Updating component ${id}`, changes);
                this.updateComponent(element, changes);
            }
        }
    }

    /**
     * Agregar nuevo componente al DOM
     * 
     * @param {string} id - Component ID
     * @param {object} config - Component configuration
     */
    addComponent(id, config) {
        try {
            // Crear componente usando factory (id primero, config segundo)
            const component = ComponentFactory.create(id, config);
            
            if (!component) {
                console.error(`❌ ComponentFactory returned null for type: ${config.type}`);
                return;
            }
            
            const element = component.render();
            
            // Buscar parent y agregar
            const parentElement = document.querySelector(`[data-component-id="${config.parent}"]`) 
                               || document.getElementById(config.parent);
            
            if (parentElement) {
                parentElement.appendChild(element);
                console.log(`➕ Component ${id} added to parent ${config.parent}`);
            } else {
                console.error(`❌ Parent ${config.parent} not found for component ${id}`);
            }
        } catch (error) {
            console.error(`❌ Error adding component ${id}:`, error);
        }
    }

    /**
     * Actualizar componente existente en el DOM
     * 
     * @param {HTMLElement} element - DOM element
     * @param {object} changes - Properties to update
     */
    updateComponent(element, changes) {
        try {
            // Texto (labels, buttons)
            if (changes.text !== undefined) {
                element.textContent = changes.text;
            }
            
            // Label (buttons)
            if (changes.label !== undefined) {
                element.textContent = changes.label;
            }
            
            // Estilos/clases CSS
            if (changes.style !== undefined) {
                // Remover clases de estilo antiguas
                element.classList.remove('default', 'primary', 'secondary', 'success', 'warning', 'danger', 'info');
                element.classList.add(changes.style);
            }
            
            // Visibilidad
            if (changes.visible !== undefined) {
                element.style.display = changes.visible ? '' : 'none';
            }
            
            // Estado enabled/disabled
            if (changes.enabled !== undefined) {
                if (element.tagName === 'BUTTON' || element.tagName === 'INPUT') {
                    element.disabled = !changes.enabled;
                }
            }
            
            // Valor (inputs)
            if (changes.value !== undefined) {
                if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                    element.value = changes.value;
                } else {
                    const input = element.querySelector('input, textarea');
                    if (input) input.value = changes.value;
                }
            }
            
            // Checked (checkboxes)
            if (changes.checked !== undefined) {
                if (element.type === 'checkbox') {
                    element.checked = changes.checked;
                } else {
                    const checkbox = element.querySelector('input[type="checkbox"]');
                    if (checkbox) checkbox.checked = changes.checked;
                }
            }
            
            console.log(`✅ Component updated`);
        } catch (error) {
            console.error(`❌ Error updating component:`, error);
        }
    }
}

// ==================== Label Component ====================
class LabelComponent extends UIComponent {
    render() {
        const label = document.createElement('span');
        label.className = `ui-label ${this.config.style || 'default'}`;
        label.textContent = this.config.text || '';

        return this.applyCommonAttributes(label);
    }
}

// ==================== Input Component ====================
class InputComponent extends UIComponent {
    render() {
        const group = document.createElement('div');
        group.className = 'ui-input-group';

        if (this.config.label) {
            const label = document.createElement('label');
            label.textContent = this.config.label;
            if (this.config.required) {
                label.className = 'required';
            }
            if (this.config.name) {
                label.setAttribute('for', this.config.name);
            }
            group.appendChild(label);
        }

        const input = document.createElement('input');
        input.className = 'ui-input';
        input.type = this.config.input_type || 'text';
        input.placeholder = this.config.placeholder || '';
        input.value = this.config.value || '';
        input.required = this.config.required || false;
        input.disabled = this.config.disabled || false;
        input.readonly = this.config.readonly || false;

        if (this.config.name) {
            input.name = this.config.name;
            input.id = this.config.name;
        }

        if (this.config.maxlength) input.maxLength = this.config.maxlength;
        if (this.config.minlength) input.minLength = this.config.minlength;
        if (this.config.pattern) input.pattern = this.config.pattern;

        group.appendChild(input);

        return this.applyCommonAttributes(group);
    }
}

// ==================== Select Component ====================
class SelectComponent extends UIComponent {
    render() {
        const group = document.createElement('div');
        group.className = 'ui-select-group';

        if (this.config.label) {
            const label = document.createElement('label');
            label.textContent = this.config.label;
            if (this.config.required) {
                label.className = 'required';
            }
            if (this.config.name) {
                label.setAttribute('for', this.config.name);
            }
            group.appendChild(label);
        }

        const select = document.createElement('select');
        select.className = 'ui-select';
        select.required = this.config.required || false;
        select.disabled = this.config.disabled || false;

        if (this.config.name) {
            select.name = this.config.name;
            select.id = this.config.name;
        }

        // Add placeholder option if exists
        if (this.config.placeholder && !this.config.value) {
            const placeholderOption = document.createElement('option');
            placeholderOption.value = '';
            placeholderOption.textContent = this.config.placeholder;
            placeholderOption.disabled = true;
            placeholderOption.selected = true;
            select.appendChild(placeholderOption);
        }

        // Add options
        if (this.config.options) {
            for (const [value, label] of Object.entries(this.config.options)) {
                const option = document.createElement('option');
                option.value = value;
                option.textContent = label;
                if (this.config.value === value) {
                    option.selected = true;
                }
                select.appendChild(option);
            }
        }

        group.appendChild(select);

        return this.applyCommonAttributes(group);
    }
}

// ==================== Checkbox Component ====================
class CheckboxComponent extends UIComponent {
    render() {
        const group = document.createElement('div');
        group.className = 'ui-checkbox-group';

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.className = 'ui-checkbox';
        checkbox.checked = this.config.checked || false;
        checkbox.required = this.config.required || false;
        checkbox.disabled = this.config.disabled || false;

        if (this.config.name) {
            checkbox.name = this.config.name;
            checkbox.id = this.config.name;
        }

        if (this.config.value) {
            checkbox.value = this.config.value;
        }

        group.appendChild(checkbox);

        if (this.config.label) {
            const label = document.createElement('label');
            label.className = 'ui-checkbox-label';
            label.textContent = this.config.label;
            if (this.config.required) {
                label.classList.add('required');
            }
            if (this.config.name) {
                label.setAttribute('for', this.config.name);
            }
            group.appendChild(label);
        }

        return this.applyCommonAttributes(group);
    }
}

// ==================== Component Factory ====================
class ComponentFactory {
    static create(id, config) {
        switch (config.type) {
            case 'container':
                return new ContainerComponent(id, config);
            case 'button':
                return new ButtonComponent(id, config);
            case 'label':
                return new LabelComponent(id, config);
            case 'input':
                return new InputComponent(id, config);
            case 'select':
                return new SelectComponent(id, config);
            case 'checkbox':
                return new CheckboxComponent(id, config);
            default:
                console.warn(`Unknown component type: ${config.type}`);
                return null;
        }
    }
}

// ==================== UI Renderer ====================
class UIRenderer {
    constructor(data) {
        this.data = data;
        this.components = new Map();
    }

    render() {
        console.log('Rendering UI with data:', this.data);

        // Step 1: Create all component instances PRESERVING ORDER
        // CRITICAL: Sort by _order field to preserve insertion order (Object.keys() sorts numeric keys)
        const componentIds = Object.keys(this.data).sort((a, b) => {
            const orderA = this.data[a]._order ?? 0;
            const orderB = this.data[b]._order ?? 0;
            return orderA - orderB;
        });
        
        // DEBUG: Log order of counter components
        console.log('=== Component Order ===');
        for (const id of componentIds) {
            const config = this.data[id];
            if (config.name && (config.name.includes('counter') || config.name.includes('btn_'))) {
                console.log(`ID: ${id} | Name: ${config.name} | Parent: ${config.parent} | Order: ${config._order}`);
            }
        }
        
        for (const id of componentIds) {
            const config = this.data[id];
            const component = ComponentFactory.create(id, config);
            if (component) {
                this.components.set(id, component);
            }
        }

        console.log(`Created ${this.components.size} components`);

        // Step 2: Group components by parent to maintain sibling order
        const childrenByParent = new Map(); // parent -> [child_ids in order]
        for (const id of componentIds) {
            const component = this.components.get(id);
            if (!component) continue;
            
            const parentId = component.config.parent;
            const parentKey = typeof parentId === 'string' ? parentId : parentId.toString();
            
            if (!childrenByParent.has(parentKey)) {
                childrenByParent.set(parentKey, []);
            }
            childrenByParent.get(parentKey).push(id);
        }

        // Step 3: Mount components in hierarchical order
        const mounted = new Set();
        const maxIterations = this.components.size * 2;
        let iterations = 0;

        while (mounted.size < this.components.size && iterations < maxIterations) {
            iterations++;
            
            // Iterate in ORIGINAL ORDER
            for (const id of componentIds) {
                const component = this.components.get(id);
                if (!component || mounted.has(id)) continue;

                const parentId = component.config.parent;

                if (typeof parentId === 'string') {
                    // Parent is a DOM element (always available)
                    const parentElement = document.getElementById(parentId);
                    if (parentElement) {
                        component.mount(parentElement);
                        mounted.add(id);
                    } else {
                        console.error(`Parent element not found: ${parentId}`);
                        mounted.add(id);
                    }
                } else if (typeof parentId === 'number') {
                    // Parent is another component - check if parent is mounted
                    const parentComponent = this.components.get(parentId.toString());
                    
                    if (!parentComponent) {
                        console.error(`Parent component not found: ${parentId}`);
                        mounted.add(id);
                        continue;
                    }

                    // Wait for parent to be mounted first
                    if (mounted.has(parentId.toString())) {
                        // Mount in order by just using appendChild
                        // Since we iterate in order, siblings will be appended in correct order
                        component.mount(parentComponent.element);
                        mounted.add(id);
                    }
                }
            }
        }

        if (mounted.size < this.components.size) {
            console.warn(`Could not mount ${this.components.size - mounted.size} components (circular dependency or missing parents)`);
        }

        console.log(`UI rendering complete (${mounted.size}/${this.components.size} mounted)`);
    }
}

// ==================== Main Application ====================
async function loadDemoUI() {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        console.log('Fetching UI data from /api/demo-ui...');
        
        const response = await fetch('/api/demo-ui', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const uiData = await response.json();
        console.log('UI Data received:', uiData);
        
        // Render the UI
        const renderer = new UIRenderer(uiData);
        renderer.render();
        
    } catch (error) {
        console.error('Error loading demo UI:', error);
        document.getElementById('main').innerHTML = `
            <div style="padding: 20px; color: red; background: #fee; border: 1px solid #fcc; border-radius: 6px;">
                <h2>❌ Error loading UI components</h2>
                <p><strong>Message:</strong> ${error.message}</p>
                <p><strong>Check the console</strong> for more details.</p>
            </div>
        `;
    }
}

// Listen for UI actions
window.addEventListener('ui-action', (event) => {
    console.log('UI Action triggered:', event.detail);
    // Here you can handle actions globally
    // e.g., send to backend, update state, etc.
});

// Load UI on page load
document.addEventListener('DOMContentLoaded', loadDemoUI);
