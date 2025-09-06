    </main>

    <!-- Lábléc -->
    <footer class="footer mt-auto py-4">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4" data-aos="fade-up">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-cube me-2"></i>HexByte
                    </h5>
                    <p class="text-light mb-3">
                        Modern platform Minecraft pluginok megosztására és felfedezésére. Csatlakozz a közösséghez és oszd meg kreativitásod!
                    </p>
                    <div class="social-links">
                        <a href="#" class="me-3"><i class="fab fa-github"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-discord"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2" data-aos="fade-up" data-aos-delay="100">
                    <h6 class="text-primary mb-3">Navigáció</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php">Főoldal</a></li>
                        <li class="mb-2"><a href="plugins.php">Pluginok</a></li>
                        <li class="mb-2"><a href="upload.php">Feltöltés</a></li>
                        <li class="mb-2"><a href="docs.php">Dokumentáció</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2" data-aos="fade-up" data-aos-delay="200">
                    <h6 class="text-primary mb-3">Kategóriák</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="plugins.php?category=admin">Admin</a></li>
                        <li class="mb-2"><a href="plugins.php?category=economy">Gazdaság</a></li>
                        <li class="mb-2"><a href="plugins.php?category=fun">Szórakozás</a></li>
                        <li class="mb-2"><a href="plugins.php?category=utility">Segédprogramok</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                    <h6 class="text-primary mb-3">Hírlevél</h6>
                    <p class="text-light mb-3">Iratkozz fel hírlevelünkre, hogy értesülj a legújabb pluginokról és fejlesztésekről!</p>
                    <form class="newsletter-form">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Email címed">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <hr class="border-secondary my-4">
            
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0 text-light">
                        &copy; <?php echo date('Y'); ?> HexByte. Minden jog fenntartva.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <ul class="list-inline mb-0">
                        <li class="list-inline-item">
                            <a href="privacy.php">Adatvédelem</a>
                        </li>
                        <li class="list-inline-item">
                            <span class="text-muted mx-2">|</span>
                        </li>
                        <li class="list-inline-item">
                            <a href="terms.php">Felhasználási feltételek</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scriptek -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- Saját scriptek -->
    <script>
        // AOS inicializálása
        AOS.init({
            duration: 800,
            once: true
        });
        
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
        
        // Toast üzenetek automatikus eltüntetése
        const toasts = document.querySelectorAll('.toast');
        toasts.forEach(toast => {
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        });
        
        // Aktív menüpont kiemelése
        const currentLocation = location.pathname;
        const menuItems = document.querySelectorAll('.nav-link');
        menuItems.forEach(item => {
            if (item.getAttribute('href') === currentLocation) {
                item.classList.add('active');
            }
        });
        
        // Loading spinner mutatása oldalbetöltéskor
        window.addEventListener('load', () => {
            const loader = document.querySelector('.loading-spinner');
            if (loader) {
                loader.style.display = 'none';
            }
        });
    </script>
</body>
</html> 