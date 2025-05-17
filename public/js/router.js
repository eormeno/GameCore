// Router configuration for GameCore application
import { partialLoader } from '/js/modules/PartialLoader.js';
import { fetchApi } from '/js/services/api.js';

// Initialize router and setup routes when DOM is loaded
window.addEventListener("load", async () => {
    const router = new Navigo("/");
    const gamesContainer = document.querySelector("#gamesContainer");

    // Function to update active navigation link
    const updateActiveNav = (url) => {
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
    };

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
            updateActiveNav("/");
            await partialLoader.loadPartial('home', gamesContainer, router);
        })
        .on("/games", (match) => {
            updateActiveNav("/games");
        })
        .on("/login", async (match) => {
            updateActiveNav("/login");
            await partialLoader.loadPartial('login-form', gamesContainer, router);
        })
        .on("/logout", async (match) => {
            updateActiveNav("/logout");
            await closeSession();
        })
        .resolve();

    await updateAuthState();

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
