// Router configuration for GameCore application
import { partialLoader } from '/js/modules/PartialLoader.js';

// Initialize router and setup routes when DOM is loaded
window.addEventListener("load", () => {
    const router = new Navigo("/");
    const gamesContainer = document.querySelector("#gamesContainer");
    const render = (content) => {
        const container = document.querySelector("#gamesContainer");
        container.innerHTML = content;
    };

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
            await partialLoader.loadPartial('home', gamesContainer);
        })
        .on("/games", (match) => {
            render("Games");
            updateActiveNav("/games");
        })
        .on("/login", async (match) => {
            updateActiveNav("/login");
            await partialLoader.loadPartial('login-form', gamesContainer);
        })
        .resolve();
});

// Commented code preserved for reference
//import { main } from '/js/main.js';
//import pageStateManager from '/js/modules/PageStateManager.js';

//document.addEventListener('DOMContentLoaded', () => {
//    main(pageStateManager.getPageState());
//});
