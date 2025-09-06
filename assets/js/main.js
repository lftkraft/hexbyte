// Számláló animáció
function animateCounters() {
    const counters = document.querySelectorAll('.counter');
    
    counters.forEach(counter => {
        const target = parseInt(counter.innerText.replace(/,/g, ''));
        const duration = 2000;
        const step = target / (duration / 16);
        let current = 0;
        
        const updateCounter = () => {
            current += step;
            if (current < target) {
                counter.innerText = Math.floor(current).toLocaleString();
                requestAnimationFrame(updateCounter);
            } else {
                counter.innerText = target.toLocaleString();
            }
        };
        
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                updateCounter();
                observer.unobserve(counter);
            }
        });
        
        observer.observe(counter);
    });
}

// Parallax effekt
function initParallax() {
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const parallaxElements = document.querySelectorAll('[data-parallax]');
        
        parallaxElements.forEach(el => {
            const speed = el.getAttribute('data-parallax');
            el.style.transform = `translateY(${scrolled * speed}px)`;
        });
    });
}

// Smooth scroll
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Aktív menüpont kiemelése
function highlightActiveMenuItem() {
    const currentLocation = window.location.pathname;
    const menuItems = document.querySelectorAll('.nav-link');
    
    menuItems.forEach(item => {
        if (item.getAttribute('href') === currentLocation) {
            item.classList.add('active');
        }
    });
}

// Form validáció
function initFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    forms.forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
}

// Plugin kártya hover effekt
function initPluginCardEffects() {
    const cards = document.querySelectorAll('.plugin-card');
    
    cards.forEach(card => {
        card.addEventListener('mousemove', e => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            card.style.setProperty('--mouse-x', `${x}px`);
            card.style.setProperty('--mouse-y', `${y}px`);
        });
    });
}

// Loading spinner
class LoadingSpinner {
    constructor() {
        this.spinner = document.createElement('div');
        this.spinner.className = 'loading-spinner';
        document.body.appendChild(this.spinner);
    }
    
    show() {
        this.spinner.style.display = 'block';
    }
    
    hide() {
        this.spinner.style.display = 'none';
    }
}

// Oldal betöltési események
document.addEventListener('DOMContentLoaded', () => {
    // AOS inicializálása
    AOS.init({
        duration: 800,
        once: true,
        offset: 50
    });
    
    // Komponensek inicializálása
    animateCounters();
    initParallax();
    initSmoothScroll();
    highlightActiveMenuItem();
    initFormValidation();
    initPluginCardEffects();
    
    // Toast manager példányosítása
    window.toastManager = new ToastManager();
    
    // Flash üzenetek feldolgozása (áthelyezve a toast-manager.js-ből)
    const alerts = document.querySelectorAll('.alert');
    console.log(`Talált flash üzenet (alert) elem(ek) száma: ${alerts.length}`);
    alerts.forEach(alert => {
        const message = alert.querySelector('.alert-message').textContent;
        const type = alert.classList.contains('alert-success') ? 'success' :
                    alert.classList.contains('alert-danger') ? 'error' :
                    alert.classList.contains('alert-warning') ? 'warning' : 'info';
        
        console.log(`Flash üzenet feldolgozása: Üzenet: "${message}", Típus: "${type}"`);
        window.toastManager.show(message, type);
        alert.remove();
        console.log('Flash üzenet (alert) elem eltávolítva a DOM-ból.');
    });
    console.log('Flash üzenetek feldolgozása befejeződött.');

    // Loading spinner példányosítása
    window.loadingSpinner = new LoadingSpinner();
    
    // Oldal betöltési animáció
    document.body.classList.add('page-loaded');
});

// Dinamikus háttér effekt
function initDynamicBackground() {
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    
    canvas.className = 'background-canvas';
    document.body.appendChild(canvas);
    
    function resize() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    }
    
    window.addEventListener('resize', resize);
    resize();
    
    const particles = [];
    const particleCount = 50;
    
    for (let i = 0; i < particleCount; i++) {
        particles.push({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            size: Math.random() * 3 + 1,
            speedX: Math.random() * 2 - 1,
            speedY: Math.random() * 2 - 1
        });
    }
    
    function animate() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        particles.forEach(particle => {
            particle.x += particle.speedX;
            particle.y += particle.speedY;
            
            if (particle.x < 0) particle.x = canvas.width;
            if (particle.x > canvas.width) particle.x = 0;
            if (particle.y < 0) particle.y = canvas.height;
            if (particle.y > canvas.height) particle.y = 0;
            
            ctx.beginPath();
            ctx.arc(particle.x, particle.y, particle.size, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(108, 99, 255, 0.1)';
            ctx.fill();
        });
        
        requestAnimationFrame(animate);
    }
    
    animate();
}

// Inicializáljuk a dinamikus hátteret
initDynamicBackground();