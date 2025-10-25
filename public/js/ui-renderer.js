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
        // Use internal component ID (_id) for data attribute, not JSON key
        const componentId = this.config._id || this.id;
        element.setAttribute('data-component-id', componentId);
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
        
        // Handle enabled state (default to true if not specified)
        const isEnabled = this.config.enabled !== undefined ? this.config.enabled : true;
        button.disabled = !isEnabled;

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
        // Collect values from inputs in the same container context
        const contextValues = this.collectContextValues();
        
        // Merge collected values with explicit parameters (explicit params take precedence)
        const mergedParameters = { ...contextValues, ...parameters };
        
        // Send POST request to backend
        this.sendEventToBackend('click', action, mergedParameters);
    }

    /**
     * Collect values from all input elements in the same container context
     * 
     * @returns {object} Object with input names as keys and their values
     */
    collectContextValues() {
        const values = {};
        
        // Find the button element in the DOM
        const buttonElement = document.querySelector(`[data-component-id="${this.config._id}"]`);
        if (!buttonElement) {
            console.log('⚠️ Button element not found for collectContextValues');
            return values;
        }
        
        // Find the parent container (or fallback to document)
        let container = buttonElement.closest('.ui-container');
        if (!container) {
            console.log('⚠️ No .ui-container found, using document');
            container = document;
        } else {
            console.log('✅ Found container:', container);
        }
        
        // Collect values from text inputs
        const inputs = container.querySelectorAll('input:not([type="checkbox"]):not([type="radio"]), textarea');
        console.log(`🔍 Found ${inputs.length} text inputs`);
        inputs.forEach(input => {
            console.log(`  - Input: type="${input.type}", name="${input.name}", value="${input.value}"`);
            if (input.name) {
                values[input.name] = input.value;
            }
        });
        
        // Collect values from selects
        const selects = container.querySelectorAll('select');
        console.log(`🔍 Found ${selects.length} selects`);
        selects.forEach(select => {
            if (select.name) {
                values[select.name] = select.value;
            }
        });
        
        // Collect values from checkboxes
        const checkboxes = container.querySelectorAll('input[type="checkbox"]');
        console.log(`🔍 Found ${checkboxes.length} checkboxes`);
        checkboxes.forEach(checkbox => {
            if (checkbox.name) {
                values[checkbox.name] = checkbox.checked;
            }
        });
        
        // Collect values from radio buttons (only checked ones)
        const radios = container.querySelectorAll('input[type="radio"]:checked');
        console.log(`🔍 Found ${radios.length} checked radios`);
        radios.forEach(radio => {
            if (radio.name) {
                values[radio.name] = radio.value;
            }
        });
        
        console.log('📋 Collected context values:', values);
        
        return values;
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

            // Use internal component ID (_id), not the JSON key
            const componentId = this.config._id || parseInt(this.id);

            console.log('Sending event:', { component_id: componentId, action, csrfToken });

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
                    component_id: componentId,
                    event: event,
                    action: action,
                    parameters: parameters,
                }),
            });

            const result = await response.json();

            // ÉXITO: response.ok = true (status 200-299)
            if (response.ok) {
                console.log('✅ Action executed:', action, result);
                
                // Handle UI updates using global renderer
                if (result && Object.keys(result).length > 0) {
                    if (globalRenderer) {
                        globalRenderer.handleUIUpdate(result);
                    } else {
                        console.error('❌ Global renderer not initialized');
                    }
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
        console.log('🎨 Rendering UI with data:', this.data);

        // Step 1: Build a map of internal ID -> JSON key
        // Each component now has _id in its config
        const internalIdToKey = new Map();
        const componentIds = Object.keys(this.data);
        
        console.log('📋 Component IDs from JSON keys:', componentIds);
        
        for (const key of componentIds) {
            const config = this.data[key];
            if (config._id !== undefined) {
                internalIdToKey.set(config._id, key);
                console.log(`  🔗 Mapped _id ${config._id} -> JSON key "${key}"`);
            }
        }

        // Step 2: Create all component instances
        for (const id of componentIds) {
            const config = this.data[id];
            console.log(`  🏗️ Creating component type="${config.type}" id="${id}"`, config);
            const component = ComponentFactory.create(id, config);
            if (component) {
                this.components.set(id, component);
                console.log(`    ✅ Created successfully`);
            } else {
                console.log(`    ❌ Failed to create`);
            }
        }

        console.log(`✅ Created ${this.components.size} components`);

        // Step 3: Group components by parent and sort by _order
        const childrenByParent = new Map();
        for (const id of componentIds) {
            const component = this.components.get(id);
            if (!component) continue;
            
            const parentId = component.config.parent;
            let parentKey;
            
            if (typeof parentId === 'string') {
                // Parent is a DOM element
                parentKey = parentId;
            } else if (typeof parentId === 'number') {
                // Parent is a component - find its key using _id
                parentKey = internalIdToKey.get(parentId);
                if (!parentKey) {
                    console.error(`Parent component with internal ID ${parentId} not found in JSON`);
                    continue;
                }
            }
            
            if (!childrenByParent.has(parentKey)) {
                childrenByParent.set(parentKey, []);
            }
            childrenByParent.get(parentKey).push({
                id: id,
                order: component.config._order ?? 999999
            });
        }
        
        // Sort children within each parent by their _order
        for (const [parent, children] of childrenByParent.entries()) {
            children.sort((a, b) => a.order - b.order);
        }

        // Step 4: Mount components in hierarchical order
        const mounted = new Set();
        const maxIterations = this.components.size * 2;
        let iterations = 0;

        console.log('🚀 Starting component mounting...');

        while (mounted.size < this.components.size && iterations < maxIterations) {
            iterations++;
            
            // For each parent, mount its children in order
            for (const [parentKey, children] of childrenByParent.entries()) {
                for (const childInfo of children) {
                    const id = childInfo.id;
                    const component = this.components.get(id);
                    
                    if (!component || mounted.has(id)) continue;

                    const parentId = component.config.parent;
                    
                    console.log(`  📍 Attempting to mount "${id}" (type: ${component.config.type}), parent: ${parentId}`);

                    if (typeof parentId === 'string') {
                        // Parent is a DOM element (always available)
                        const parentElement = document.getElementById(parentId);
                        if (parentElement) {
                            component.mount(parentElement);
                            mounted.add(id);
                            console.log(`    ✅ Mounted to DOM element "${parentId}"`);
                        } else {
                            console.error(`    ❌ Parent element not found: ${parentId}`);
                            mounted.add(id);
                        }
                    } else if (typeof parentId === 'number') {
                        // Parent is a component - find its key using _id
                        const parentComponentKey = internalIdToKey.get(parentId);
                        const parentComponent = this.components.get(parentComponentKey);
                        
                        if (!parentComponent) {
                            console.error(`    ❌ Parent component not found for ID: ${parentId}`);
                            mounted.add(id);
                            continue;
                        }

                        // Wait for parent to be mounted first
                        if (mounted.has(parentComponentKey)) {
                            component.mount(parentComponent.element);
                            mounted.add(id);
                            console.log(`    ✅ Mounted to component "${parentComponentKey}" (_id: ${parentId})`);
                        } else {
                            console.log(`    ⏳ Waiting for parent "${parentComponentKey}" to be mounted first`);
                        }
                    }
                }
            }
        }

        if (mounted.size < this.components.size) {
            console.warn(`⚠️ Could not mount ${this.components.size - mounted.size} components (circular dependency or missing parents)`);
        }

        console.log(`✅ UI rendering complete (${mounted.size}/${this.components.size} mounted)`);
    }

    /**
     * Handle UI updates from backend
     * 
     * @param {object} uiUpdate - UI update object (same structure as initial render)
     */
    handleUIUpdate(uiUpdate) {
        console.log('📦 Processing UI updates:', uiUpdate);
        
        for (const [jsonKey, changes] of Object.entries(uiUpdate)) {
            const componentId = changes._id;
            const element = document.querySelector(`[data-component-id="${componentId}"]`);
            
            if (element) {
                // Component exists in DOM → UPDATE
                console.log(`✏️ Updating component ${componentId}`, changes);
                this.updateComponent(element, changes);
            } else {
                // Component doesn't exist → CREATE (rare in events, more common in initial render)
                console.log(`➕ Creating new component ${componentId}`, changes);
                this.addComponent(jsonKey, changes);
            }
        }
    }

    /**
     * Update existing component in DOM
     * 
     * @param {HTMLElement} element - DOM element to update
     * @param {object} changes - Properties to update
     */
    updateComponent(element, changes) {
        try {
            // Text (labels)
            if (changes.text !== undefined) {
                element.textContent = changes.text;
            }
            
            // Label (buttons)
            if (changes.label !== undefined) {
                element.textContent = changes.label;
            }
            
            // Style/classes
            if (changes.style !== undefined) {
                element.classList.remove('default', 'primary', 'secondary', 'success', 'warning', 'danger', 'info');
                element.classList.add(changes.style);
            }
            
            // Visibility
            if (changes.visible !== undefined) {
                element.style.display = changes.visible ? '' : 'none';
            }
            
            // Enabled/disabled state
            if (changes.enabled !== undefined) {
                if (element.tagName === 'BUTTON' || element.tagName === 'INPUT') {
                    element.disabled = !changes.enabled;
                }
            }
            
            // Value (inputs)
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
            
            console.log(`✅ Component ${changes._id} updated successfully`);
        } catch (error) {
            console.error(`❌ Error updating component ${changes._id}:`, error);
        }
    }

    /**
     * Add new component to DOM
     * 
     * @param {string} jsonKey - JSON key of the component
     * @param {object} config - Component configuration
     */
    addComponent(jsonKey, config) {
        try {
            const component = ComponentFactory.create(jsonKey, config);
            
            if (!component) {
                console.error(`❌ ComponentFactory returned null for type: ${config.type}`);
                return;
            }
            
            const element = component.render();
            
            // Find parent and append
            const parentElement = document.querySelector(`[data-component-id="${config.parent}"]`) 
                               || document.getElementById(config.parent);
            
            if (parentElement) {
                parentElement.appendChild(element);
                console.log(`➕ Component ${config._id} added to parent ${config.parent}`);
            } else {
                console.error(`❌ Parent ${config.parent} not found for component ${config._id}`);
            }
        } catch (error) {
            console.error(`❌ Error adding component:`, error);
        }
    }
}

// Global renderer instance
let globalRenderer = null;

// ==================== Main Application ====================
async function loadDemoUI(demoName = null) {
    try {
        // Use demo name from window global (set by Laravel) or parameter
        const demo = demoName || window.DEMO_NAME || 'button-demo';
        // Check if reset flag is set
        const reset = window.RESET_DEMO ? '/reset' : '';
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        console.log(`Fetching UI data from /api/${demo}${reset}...`);

        const response = await fetch(`/api/${demo}${reset}`, {
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
        
        // If reset was requested, update URL to normal demo URL (without /reset)
        if (window.RESET_DEMO) {
            const normalUrl = `/demo/${demo}`;
            window.history.replaceState({}, '', normalUrl);
            window.RESET_DEMO = false; // Reset the flag
        }
        
        // Create and store global renderer
        globalRenderer = new UIRenderer(uiData);
        globalRenderer.render();
        
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
document.addEventListener('DOMContentLoaded', () => {
    loadDemoUI();
});
