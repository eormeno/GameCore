/**
 * NavigationService - Handles routing operations
 */
export class NavigationService {
    constructor(router) {
        this.router = router;
    }

    /**
     * Navigate to login page
     */
    navigateToLogin() {
        this.router?.navigate('/login');
    }

    /**
     * Navigate to games page
     */
    navigateToGames() {
        this.router?.navigate('/games');
    }
}
