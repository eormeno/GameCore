/**
 * Database Debugger Client
 * 
 * A professional database debugging tool that provides a clean interface
 * to browse and view database tables through API endpoints.
 */

// ===== CONFIGURATION =====
const CONFIG = {
    API_BASE_URL: '/api',
    ENDPOINTS: {
        TABLES: '/tables',
        TABLE_DATA: '/tables'
    },
    TIMEOUTS: {
        DEFAULT: 10000,
        TABLE_LOAD: 15000
    }
};

// ===== STATE MANAGEMENT =====
class AppState {
    constructor() {
        this.selectedTable = null;
        this.tables = [];
        this.tableData = null;
        this.isLoading = false;
        this.connectionStatus = 'connecting';
        this.lastError = null;
    }

    setSelectedTable(tableName) {
        this.selectedTable = tableName;
    }

    setTables(tables) {
        this.tables = tables;
    }

    setTableData(data) {
        this.tableData = data;
    }

    setLoading(isLoading) {
        this.isLoading = isLoading;
    }

    setConnectionStatus(status) {
        this.connectionStatus = status;
    }

    setError(error) {
        this.lastError = error;
    }
}

// ===== GLOBAL STATE =====
const appState = new AppState();

// ===== API SERVICE =====
class ApiService {
    static async fetchWithTimeout(url, options = {}, timeout = CONFIG.TIMEOUTS.DEFAULT) {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), timeout);

        try {
            const response = await fetch(url, {
                ...options,
                signal: controller.signal
            });
            clearTimeout(timeoutId);
            return response;
        } catch (error) {
            clearTimeout(timeoutId);
            throw error;
        }
    }

    static async getTables() {
        try {
            const response = await this.fetchWithTimeout(
                `${CONFIG.API_BASE_URL}${CONFIG.ENDPOINTS.TABLES}`
            );

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            return Array.isArray(data) ? data : [];
        } catch (error) {
            if (error.name === 'AbortError') {
                throw new Error('Request timed out. Please check your connection.');
            }
            throw new Error(`Failed to fetch tables: ${error.message}`);
        }
    }

    static async getTableData(tableName) {
        try {
            const response = await this.fetchWithTimeout(
                `${CONFIG.API_BASE_URL}${CONFIG.ENDPOINTS.TABLE_DATA}/${encodeURIComponent(tableName)}`,
                {},
                CONFIG.TIMEOUTS.TABLE_LOAD
            );

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            return data;
        } catch (error) {
            if (error.name === 'AbortError') {
                throw new Error('Request timed out. The table might be too large.');
            }
            throw new Error(`Failed to fetch table data: ${error.message}`);
        }
    }
}

// ===== UI COMPONENTS =====
class UIComponents {
    static createTableItem(table) {
        const tableItem = document.createElement('div');
        tableItem.className = 'table-item';
        tableItem.dataset.tableName = table.name;
        
        tableItem.innerHTML = `
            <div class="table-name">${this.escapeHtml(table.name)}</div>
            <div class="table-meta">
                <span>📊 ${table.rows} rows</span>
                <span>📋 ${table.columns} columns</span>
            </div>
        `;

        tableItem.addEventListener('click', () => this.handleTableSelect(table.name));
        return tableItem;
    }

    static createDataTable(data) {
        if (!data || !Array.isArray(data) || data.length === 0) {
            return this.createEmptyState('No data found', 'This table appears to be empty.');
        }

        const table = document.createElement('table');
        table.className = 'data-table';

        // Create header
        const thead = document.createElement('thead');
        const headerRow = document.createElement('tr');
        
        const columns = Object.keys(data[0]);
        columns.forEach(column => {
            const th = document.createElement('th');
            th.textContent = column;
            headerRow.appendChild(th);
        });
        
        thead.appendChild(headerRow);
        table.appendChild(thead);

        // Create body
        const tbody = document.createElement('tbody');
        data.forEach(row => {
            const tr = document.createElement('tr');
            columns.forEach(column => {
                const td = document.createElement('td');
                const value = row[column];
                
                td.innerHTML = this.formatCellValue(value);
                tr.appendChild(td);
            });
            tbody.appendChild(tr);
        });
        
        table.appendChild(tbody);
        return table;
    }

    static createEmptyState(title, message, iconSvg = null) {
        const emptyState = document.createElement('div');
        emptyState.className = 'welcome-message';
        
        const defaultIcon = `
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="m9 12 2 2 4-4"></path>
            </svg>
        `;
        
        emptyState.innerHTML = `
            ${iconSvg || defaultIcon}
            <h3>${this.escapeHtml(title)}</h3>
            <p>${this.escapeHtml(message)}</p>
        `;
        
        return emptyState;
    }

    static createErrorState(title, message) {
        const errorIcon = `
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
        `;
        
        const errorState = document.createElement('div');
        errorState.className = 'error-message';
        errorState.innerHTML = `
            ${errorIcon}
            <h3>${this.escapeHtml(title)}</h3>
            <p>${this.escapeHtml(message)}</p>
        `;
        
        return errorState;
    }

    static formatCellValue(value) {
        if (value === null || value === undefined) {
            return '<span class="null-value">NULL</span>';
        }
        
        if (typeof value === 'boolean') {
            return `<span class="boolean-value">${value ? 'TRUE' : 'FALSE'}</span>`;
        }
        
        if (typeof value === 'number') {
            return `<span class="number-value">${value.toLocaleString()}</span>`;
        }
        
        // Escape HTML and truncate long strings
        const stringValue = String(value);
        const escaped = this.escapeHtml(stringValue);
        
        if (stringValue.length > 100) {
            return `<span title="${escaped}">${escaped.substring(0, 100)}...</span>`;
        }
        
        return escaped;
    }

    static escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    static handleTableSelect(tableName) {
        if (appState.isLoading) return;
        
        // Update selected state in UI
        document.querySelectorAll('.table-item').forEach(item => {
            item.classList.remove('selected');
        });
        
        const selectedItem = document.querySelector(`[data-table-name="${tableName}"]`);
        if (selectedItem) {
            selectedItem.classList.add('selected');
        }
        
        // Load table data
        TableController.loadTableData(tableName);
    }
}

// ===== CONTROLLERS =====
class TablesController {
    static async loadTables() {
        try {
            appState.setLoading(true);
            this.updateConnectionStatus('connecting');
            this.showTablesLoading();

            const tables = await ApiService.getTables();
            
            appState.setTables(tables);
            appState.setConnectionStatus('connected');
            this.updateConnectionStatus('connected');
            this.renderTablesList(tables);
            
        } catch (error) {
            console.error('Error loading tables:', error);
            appState.setError(error);
            appState.setConnectionStatus('error');
            this.updateConnectionStatus('error');
            this.showTablesError(error.message);
        } finally {
            appState.setLoading(false);
        }
    }

    static renderTablesList(tables) {
        const tablesList = document.getElementById('tablesList');
        
        if (!tables || tables.length === 0) {
            tablesList.innerHTML = '';
            const emptyState = UIComponents.createEmptyState(
                'No tables found',
                'No database tables are available.'
            );
            tablesList.appendChild(emptyState);
            return;
        }

        tablesList.innerHTML = '';
        tables.forEach(table => {
            const tableItem = UIComponents.createTableItem(table);
            tablesList.appendChild(tableItem);
        });
    }

    static showTablesLoading() {
        const tablesList = document.getElementById('tablesList');
        tablesList.innerHTML = `
            <div class="loading-spinner">
                <div class="spinner"></div>
                <p>Loading tables...</p>
            </div>
        `;
    }

    static showTablesError(message) {
        const tablesList = document.getElementById('tablesList');
        tablesList.innerHTML = '';
        
        const errorState = UIComponents.createErrorState(
            'Failed to load tables',
            message
        );
        
        tablesList.appendChild(errorState);
    }

    static updateConnectionStatus(status) {
        const statusIndicator = document.getElementById('connectionStatus');
        const statusText = document.querySelector('.status-text');
        
        statusIndicator.className = 'status-indicator';
        
        switch (status) {
            case 'connected':
                statusIndicator.classList.add('connected');
                statusText.textContent = 'Connected';
                break;
            case 'error':
                statusIndicator.classList.add('error');
                statusText.textContent = 'Connection Error';
                break;
            default:
                statusText.textContent = 'Connecting...';
        }
    }
}

class TableController {
    static async loadTableData(tableName) {
        try {
            appState.setSelectedTable(tableName);
            this.showTableLoading(tableName);
            this.showLoadingOverlay(true);

            const data = await ApiService.getTableData(tableName);
            
            appState.setTableData(data);
            this.renderTableData(tableName, data);
            
        } catch (error) {
            console.error('Error loading table data:', error);
            appState.setError(error);
            this.showTableError(tableName, error.message);
        } finally {
            this.showLoadingOverlay(false);
        }
    }

    static renderTableData(tableName, data) {
        const tableTitle = document.getElementById('tableTitle');
        const tableInfo = document.getElementById('tableInfo');
        const contentBody = document.getElementById('contentBody');

        // Update header
        tableTitle.textContent = tableName;
        
        if (data && Array.isArray(data)) {
            const rowCount = data.length;
            const columnCount = rowCount > 0 ? Object.keys(data[0]).length : 0;
            tableInfo.innerHTML = `
                <span>📊 ${rowCount} rows</span>
                <span>📋 ${columnCount} columns</span>
            `;
        } else {
            tableInfo.innerHTML = '';
        }

        // Update content
        contentBody.innerHTML = '';
        const tableElement = UIComponents.createDataTable(data);
        contentBody.appendChild(tableElement);
    }

    static showTableLoading(tableName) {
        const tableTitle = document.getElementById('tableTitle');
        const tableInfo = document.getElementById('tableInfo');
        const contentBody = document.getElementById('contentBody');

        tableTitle.textContent = tableName;
        tableInfo.innerHTML = '<span class="text-muted">Loading...</span>';
        
        contentBody.innerHTML = `
            <div class="loading-spinner">
                <div class="spinner"></div>
                <p>Loading table data...</p>
            </div>
        `;
    }

    static showTableError(tableName, message) {
        const tableTitle = document.getElementById('tableTitle');
        const tableInfo = document.getElementById('tableInfo');
        const contentBody = document.getElementById('contentBody');

        tableTitle.textContent = tableName;
        tableInfo.innerHTML = '<span class="text-muted" style="color: #dc3545;">Error</span>';
        
        contentBody.innerHTML = '';
        const errorState = UIComponents.createErrorState(
            'Failed to load table data',
            message
        );
        contentBody.appendChild(errorState);
    }

    static showLoadingOverlay(show) {
        const overlay = document.getElementById('loadingOverlay');
        if (show) {
            overlay.classList.add('show');
        } else {
            overlay.classList.remove('show');
        }
    }
}

class ErrorController {
    static showErrorModal(title, message, onRetry = null) {
        const modal = document.getElementById('errorModal');
        const errorMessage = document.getElementById('errorMessage');
        const retryBtn = document.getElementById('retryBtn');

        errorMessage.textContent = message;
        
        if (onRetry) {
            retryBtn.style.display = 'inline-flex';
            retryBtn.onclick = () => {
                this.hideErrorModal();
                onRetry();
            };
        } else {
            retryBtn.style.display = 'none';
        }

        modal.classList.add('show');
    }

    static hideErrorModal() {
        const modal = document.getElementById('errorModal');
        modal.classList.remove('show');
    }
}

// ===== EVENT HANDLERS =====
class EventHandlers {
    static initializeEventListeners() {
        // Refresh tables button
        const refreshBtn = document.getElementById('refreshTables');
        refreshBtn.addEventListener('click', () => {
            TablesController.loadTables();
        });

        // Error modal close buttons
        const closeErrorBtn = document.getElementById('closeErrorModal');
        const dismissErrorBtn = document.getElementById('dismissErrorBtn');
        
        closeErrorBtn.addEventListener('click', () => {
            ErrorController.hideErrorModal();
        });
        
        dismissErrorBtn.addEventListener('click', () => {
            ErrorController.hideErrorModal();
        });

        // Close modal when clicking outside
        const errorModal = document.getElementById('errorModal');
        errorModal.addEventListener('click', (e) => {
            if (e.target === errorModal) {
                ErrorController.hideErrorModal();
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                ErrorController.hideErrorModal();
                TableController.showLoadingOverlay(false);
            }
            
            if (e.key === 'F5' || (e.ctrlKey && e.key === 'r')) {
                e.preventDefault();
                TablesController.loadTables();
            }
        });

        // Handle window resize for responsive behavior
        window.addEventListener('resize', this.debounce(() => {
            // Could add resize-specific logic here if needed
        }, 250));
    }

    static debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

// ===== APPLICATION INITIALIZATION =====
class App {
    static async initialize() {
        try {
            console.log('🚀 Initializing Database Debugger...');
            
            // Setup event listeners
            EventHandlers.initializeEventListeners();
            
            // Initial load
            await TablesController.loadTables();
            
            console.log('✅ Database Debugger initialized successfully');
            
        } catch (error) {
            console.error('❌ Failed to initialize Database Debugger:', error);
            ErrorController.showErrorModal(
                'Initialization Error',
                'Failed to initialize the application. Please refresh the page and try again.',
                () => window.location.reload()
            );
        }
    }
}

// ===== APPLICATION STARTUP =====
document.addEventListener('DOMContentLoaded', () => {
    App.initialize();
});

// ===== GLOBAL ERROR HANDLING =====
window.addEventListener('error', (event) => {
    console.error('Global error:', event.error);
    // You could show a global error message here if needed
});

window.addEventListener('unhandledrejection', (event) => {
    console.error('Unhandled promise rejection:', event.reason);
    // You could show a global error message here if needed
});

// ===== EXPORT FOR TESTING (if needed) =====
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        ApiService,
        UIComponents,
        TablesController,
        TableController,
        ErrorController,
        EventHandlers,
        App,
        appState
    };
}