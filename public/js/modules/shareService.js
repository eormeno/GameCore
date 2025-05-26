/**
 * ShareService - Handles sharing functionality
 */
export class ShareService {
    /**
     * Share game with invitation code
     */
    async share(invitationCode) {
        const shareUrl = this.buildShareUrl(invitationCode);
        const shareText = `¡Mira este juego! ${shareUrl}`;

        try {
            if (this.isWebShareSupported()) {
                await this.shareWithNativeAPI(shareText, shareUrl);
            } else {
                this.shareWithFallback(shareUrl);
            }
        } catch (error) {
            console.error('Error al compartir el juego:', error);
            this.shareWithFallback(shareUrl);
        }
    }

    /**
     * Build share URL
     */
    buildShareUrl(invitationCode) {
        return `${window.location.href}play/${invitationCode}`;
    }

    /**
     * Check Web Share API support
     */
    isWebShareSupported() {
        return 'share' in navigator;
    }

    /**
     * Share with native API
     */
    async shareWithNativeAPI(shareText, shareUrl) {
        await navigator.share({
            title: 'Juego Compartido',
            text: shareText,
            url: shareUrl
        });
        console.log('Juego compartido con éxito');
    }

    /**
     * Fallback share method
     */
    shareWithFallback(shareUrl) {
        alert(`Copia este enlace para compartir: ${shareUrl}`);
    }
}
