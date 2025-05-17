/**
 * Router module for handling navigation and partial loading in the application.
 * This module uses Navigo for routing and PartialLoader for loading HTML partials.
 * It also manages authentication state and updates the navigation menu accordingly.
 */

import { partialLoader } from '/js/modules/PartialLoader.js';
import { fetchApi } from '/js/services/api.js';

let routerInstance = null;

function updateActiveNav(url) {
    // if url does not start with /, add /
    if (!url.startsWith('/')) {
        url = '/' + url;
    }

    // Reset all active states
    document.querySelectorAll('.nav-menu a, .nav-menu .menu-item').forEach(item => {
        item.classList.remove('active');
    });

    // Set active state for direct links
    document.querySelectorAll('.nav-menu a').forEach(link => {
        if (link.getAttribute('href') === url) {
            link.classList.add('active');

            // If link is in submenu, also highlight parent
            const parentMenuItem = link.closest('.submenu')?.parentElement;
            if (parentMenuItem) {
                parentMenuItem.classList.add('active');
            }
        }
    });
}

export function getRouter() {
    if (!routerInstance) {
        routerInstance = new Navigo("/");
    }
    return routerInstance;
}

export function initializeRouter() {
    const router = getRouter();
    router.hooks({
        before: (done, match) => {

            updateActiveNav(match.url);

            done();
        },
        after: async (match) => {
            await updateAuthState();
            window.scrollTo(0, 0);
        }
    });

    return router;
}

// Initialize router and setup routes when DOM is loaded
window.addEventListener("load", async () => {
    const router = initializeRouter();
    const gamesContainer = document.querySelector("#gamesContainer");

    // Handle submenu toggle on mobile (since hover doesn't work well)
    document.querySelectorAll('.menu-item.has-submenu').forEach(item => {
        item.addEventListener('click', (e) => {
            // Only toggle if clicked directly on the menu item, not its children
            if (e.currentTarget === e.target || e.target.closest('.menu-item') === e.currentTarget && !e.target.closest('.submenu')) {
                // Toggle submenu visibility
                const submenu = item.querySelector('.submenu');
                if (window.innerWidth < 768) {  // Only for mobile view
                    submenu.style.display = submenu.style.display === 'flex' ? 'none' : 'flex';
                    e.preventDefault();
                    e.stopPropagation();
                }
            }
        });
    });

    router
        .on("/", async (match) => {
            await partialLoader.loadPartial('home', gamesContainer);
        })
        .on("/games", (match) => {
        })
        .on("/login", async (match) => {
            await partialLoader.loadPartial('login-form', gamesContainer);
        })
        .on("/logout", async (match) => {
            await closeSession();
        })
        .resolve();

});

// Actualizar el estado de autenticación en toda la aplicación
async function updateAuthState() {
    const token = localStorage.getItem('token');
    const authUserName = document.getElementById('auth-user-name');

    // Establecemos el estado por defecto (no autenticado)
    document.body.setAttribute('data-auth-state', 'guest');
    authUserName.innerText = 'Authentication';

    if (token) {
        // Verificar el token con el servidor
        await fetchApi('api/user', 'GET', null, (stateName, data) => {
            if (stateName !== 'auth_required') {
                // Usuario autenticado - cambiar el estado
                document.body.setAttribute('data-auth-state', 'authenticated');
                authUserName.innerText = data.name;
            }
        });
    }
}

async function closeSession() {
    await fetchApi('api/logout', 'POST', null, async (stateName, data) => {
        localStorage.removeItem('token');
        await updateAuthState();
    });
}
