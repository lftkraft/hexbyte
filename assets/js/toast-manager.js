/**
 * Toast üzenetkezelő
 */
class ToastManager {
    constructor() {
        this.container = document.querySelector('.toast-container');
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        }
        console.log('ToastManager inicializálva.');
    }

    /**
     * Toast üzenet megjelenítése
     * @param {string} message - Az üzenet szövege
     * @param {string} type - Az üzenet típusa (success, error, warning, info)
     * @param {number} duration - Meddig maradjon látható (ms)
     */
    show(message, type = 'info', duration = 5000) {
        console.log(`Toast megjelenítése: Üzenet: "${message}", Típus: "${type}"`);
        const toast = document.createElement('div');
        toast.className = `toast show toast-${type} animate__animated animate__fadeInRight`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');

        const header = this.getTypeIcon(type);
        
        toast.innerHTML = `
            <div class="toast-header bg-dark text-light">
                ${header}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body bg-dark text-light">
                ${message}
            </div>
        `;

        this.container.appendChild(toast);

        // Automatikus eltüntetés
        setTimeout(() => {
            toast.classList.remove('show');
            toast.classList.add('animate__fadeOutRight');
            setTimeout(() => toast.remove(), 300);
            console.log(`Toast eltüntetve: "${message}"`);
        }, duration);

        // Bezárás gomb kezelése
        const closeBtn = toast.querySelector('.btn-close');
        closeBtn.addEventListener('click', () => {
            toast.classList.remove('show');
            toast.classList.add('animate__fadeOutRight');
            setTimeout(() => toast.remove(), 300);
            console.log(`Toast manuálisan bezárva: "${message}"`);
        });
    }

    /**
     * Ikon és cím meghatározása típus alapján
     */
    getTypeIcon(type) {
        const icons = {
            success: '<i class="fas fa-check-circle text-success me-2"></i><strong class="me-auto">Siker</strong>',
            error: '<i class="fas fa-exclamation-circle text-danger me-2"></i><strong class="me-auto">Hiba</strong>',
            warning: '<i class="fas fa-exclamation-triangle text-warning me-2"></i><strong class="me-auto">Figyelmeztetés</strong>',
            info: '<i class="fas fa-info-circle text-info me-2"></i><strong class="me-auto">Információ</strong>'
        };
        return icons[type] || icons.info;
    }
}
