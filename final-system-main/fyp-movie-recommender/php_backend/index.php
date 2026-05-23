<?php
// index.php - Premium Redesign for MoodAI Recommendation System

// Fetch movies for the marquee
$marquee_movies = [];

// Fetch DB config manually to avoid die() in connection.php
if (file_exists('includes/config.php')) {
    @include_once 'includes/config.php';
    if (defined('DB_HOST') && defined('DB_NAME')) {
        try {
            $pdo_marquee = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [PDO::ATTR_TIMEOUT => 2, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $stmt = $pdo_marquee->prepare("SELECT poster_path, title FROM cached_movies ORDER BY RAND() LIMIT 20");
            $stmt->execute();
            $marquee_movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $marquee_movies = [];
        }
    }
}

// Fallback if no movies found or DB connection fails
if (empty($marquee_movies)) {
    $marquee_movies = [
        ['title' => 'Apex', 'poster_path' => '/eTp7gSPkSF3Aw79mNx1NkBP1PZT.jpg'],
        ['title' => 'Michael', 'poster_path' => '/j57QWe3OoaXL9Idi9gLtsAybWLP.jpg'],
        ['title' => 'They Will Kill You', 'poster_path' => '/6oI4oQKTWMVUlr8Ivqydp28Ruu6.jpg'],
        ['title' => 'Hoppers', 'poster_path' => '/xjtWQ2CL1mpmMNwuU5HeS4Iuwuu.jpg'],
        ['title' => 'Vengeance', 'poster_path' => '/ygWXPL0RS91JyJPNOfK34eV3bRE.jpg'],
        ['title' => 'Lee Cronin\'s The Mummy', 'poster_path' => '/8L8efNkz8rUmwR7sV0g3vnC9yjn.jpg'],
        ['title' => 'Hokum', 'poster_path' => '/gC4FtEthYvx6XV2JHCQibf4P3FT.jpg'],
        ['title' => 'Forbidden Fruits', 'poster_path' => '/t3hvrrrqvRoURz4y1A1aCys6WD9.jpg']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoodAI | Premium Mood-Based Movie Recommendations</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top" data-bs-theme="dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                Mood<span>AI</span>.
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#how-it-works">How it Works</a></li>
                    <li class="nav-item"><a class="nav-link" href="#why-us">Why Us</a></li>
                </ul>
                <div class="d-flex">
                    <button type="button" class="btn btn-outline-premium me-2" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
                    <button type="button" class="btn btn-premium" data-bs-toggle="modal" data-bs-target="#registerModal">Get Started</button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Split Hero Section -->
    <section id="home" class="hero-section">
        <div class="container">
            <div class="hero-card" data-aos="zoom-in">
                <div class="row align-items-center p-5">
                    <div class="col-lg-5 text-center position-relative mb-5 mb-lg-0" data-aos="fade-right">
                        <!-- Decorative small icons -->
                        <i class="bi bi-star-fill decorative-icon icon-1"></i>
                        <i class="bi bi-film decorative-icon icon-2"></i>
                        <i class="bi bi-heart-fill decorative-icon icon-3"></i>
                        <i class="bi bi-play-circle-fill decorative-icon icon-4"></i>
                        
                        <!-- Large Main Icon -->
                        <div class="large-hero-icon">
                            <i class="bi bi-camera-reels"></i>
                        </div>
                    </div>
                    <div class="col-lg-7 hero-content ps-lg-5" data-aos="fade-left">
                        <span class="section-tag hero-tag">Movies for Your Mood</span>
                        <h1 class="hero-title-text">Your Emotions,<br>Our AI Tool.</h1>
                        <p class="lead hero-lead mb-5">
                            Experience a movie journey tailored to you. MoodAI uses smart AI to find the perfect movie for you based on how you feel.
                        </p>
                        <div class="d-flex">
                            <button type="button" class="btn btn-hero-primary btn-lg me-3" data-bs-toggle="modal" data-bs-target="#registerModal">Find My Mood</button>
                            <a href="#how-it-works" class="btn btn-hero-outline btn-lg">See How it Works</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section id="features" class="py-5">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <span class="section-tag">What We Can Do</span>
                <h2 class="section-title-premium">Smart Mood Tools</h2>
            </div>
            
            <div class="bento-container">
                <!-- Large Feature -->
                <div class="bento-1" data-aos="zoom-in">
                    <div class="premium-card">
                        <i class="bi bi-webcam premium-icon"></i>
                        <h3>Camera Detection</h3>
                        <p class="text-muted">We use your camera to see your face and find your mood. It looks at your expressions in real-time to see how you are feeling.</p>
                        <div class="mt-4">
                            <span class="badge border border-danger text-danger p-2">Real-time</span>
                            <span class="badge border border-secondary text-muted p-2 ms-2">DeepFace</span>
                        </div>
                    </div>
                </div>
                <!-- Medium Feature -->
                <div class="bento-2" data-aos="zoom-in" data-aos-delay="100">
                    <div class="premium-card">
                        <i class="bi bi-mic premium-icon"></i>
                        <h3>Voice Detection</h3>
                        <p class="text-muted">We listen to your voice to see how you are feeling. It analyzes your speech to find your underlying mood.</p>
                    </div>
                </div>
                <!-- Small Feature -->
                <div class="bento-3" data-aos="zoom-in" data-aos-delay="200">
                    <div class="premium-card d-flex flex-column justify-content-center align-items-center text-center">
                        <i class="bi bi-chat-dots premium-icon"></i>
                        <h4>Text Detection</h4>
                        <p class="small text-muted">Analyzing your words.</p>
                    </div>
                </div>
                <!-- Small Feature -->
                <div class="bento-4" data-aos="zoom-in" data-aos-delay="300">
                    <div class="premium-card d-flex flex-column justify-content-center align-items-center text-center">
                        <i class="bi bi-database premium-icon"></i>
                        <h4>TMDB Sync</h4>
                        <p class="small text-muted">Direct API integration.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Movie Marquee Section -->
    <section class="movie-marquee-section py-5 overflow-hidden">
        <div class="container-fluid px-0">
            <div class="text-center mb-5">
                <span class="section-tag">Cinematic Library</span>
                <h2 class="section-title-premium text-white">Our Curated Collection</h2>
            </div>
            
            <?php if (!empty($marquee_movies)): ?>
                <!-- Single Row Marquee as requested -->
                <div class="marquee-wrapper">
                    <div class="marquee-content marquee-left">
                        <?php 
                        // Duplicate movies to ensure seamless loop
                        $loop_movies = array_merge($marquee_movies, $marquee_movies);
                        foreach ($loop_movies as $movie): 
                            $poster_path = $movie['poster_path'];
                            if (strpos($poster_path, 'http') === false) {
                                $poster = "https://image.tmdb.org/t/p/w500" . $poster_path;
                            } else {
                                $poster = $poster_path;
                            }
                        ?>
                            <div class="movie-glass-card">
                                <img src="<?php echo htmlspecialchars($poster); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" loading="lazy">
                                <div class="glass-overlay"></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center text-muted">
                    <p>Updating cinematic library...</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Architecture / How it works -->
    <section id="how-it-works" class="bg-black py-5">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <span class="section-tag">Simple Steps</span>
                <h2 class="section-title-premium">How it Works</h2>
            </div>
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up">
                    <div class="premium-card">
                        <div class="h1 fw-bold mb-3 number-highlight">01</div>
                        <h5>Step 01</h5>
                        <p class="text-muted">Choose a tool to let us find your mood via camera, voice, or text.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="premium-card">
                        <div class="h1 fw-bold mb-3 number-highlight">02</div>
                        <h5>Step 02</h5>
                        <p class="text-muted">Our smart AI tools will analyze your data to see how you are feeling.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="premium-card">
                        <div class="h1 fw-bold mb-3 number-highlight">03</div>
                        <h5>Step 03</h5>
                        <p class="text-muted">You get a list of movies just for you, matching your current vibe.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why MoodAI Section -->
    <section id="why-us" class="py-5">
        <div class="container">
            <div class="premium-card p-5" data-aos="fade-up">
                <div class="row align-items-center">
                    <div class="col-lg-7">
                        <span class="section-tag">Why Us</span>
                        <h2 class="mb-4">Why Choose MoodAI?</h2>
                        <div class="mb-4">
                            <h5 class="text-white"><i class="bi bi-shield-check text-danger me-2"></i> Precision Driven</h5>
                            <p class="text-muted small">We don't just guess. We analyze high-fidelity data points to ensure accuracy.</p>
                        </div>
                        <div class="mb-4">
                            <h5 class="text-white"><i class="bi bi-lightning-charge text-danger me-2"></i> Low Latency</h5>
                            <p class="text-muted small">Our neural engines are optimized for near-instantaneous mood resolution.</p>
                        </div>
                        <div>
                            <h5 class="text-white"><i class="bi bi-incognito text-danger me-2"></i> Privacy First</h5>
                            <p class="text-muted small">All biometric data is processed in-memory and never stored permanently.</p>
                        </div>
                    </div>
                    <div class="col-lg-5 text-center d-none d-lg-block">
                        <i class="bi bi-suit-heart-fill" style="font-size: 10rem; color: #3D0000; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA Section -->
    <section class="py-5 mb-5">
        <div class="container text-center" data-aos="fade-up">
            <div class="premium-card p-5">
                <h2 class="section-title-premium mb-4">Ready to find your movie?</h2>
                <p class="text-muted lead mb-5">Experience the future of movie discovery today. No strings attached.</p>
                <div class="d-flex justify-content-center">
                    <a href="guest_login.php" class="btn btn-premium btn-lg px-5">Try Without Login</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer mt-auto py-5 text-center">
        <div class="container">
            <h5 class="fw-bold mb-3" style="color: var(--text-white); letter-spacing: -0.5px;">Mood<span style="color: var(--accent-red);">AI</span>.</h5>
            <p class="small mb-4 animate-reveal" style="color: var(--text-white); opacity: 0.8;">Redefining movie discovery through the lens of high-fidelity neural intelligence and human-centric emotional mapping.</p>
            <hr class="my-4 border-dark" style="opacity: 0.1;">
            <div class="small animate-reveal" style="color: var(--text-white); opacity: 0.6;">
                &copy; <?php echo date("Y"); ?> MoodAI. Engineered for high-performance cinematic resolution and emotional synchronization.
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content login-modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-0">
                    <div class="text-start mb-4">
                        <h4 class="fw-bold mb-1 logo-text">MoodAI<span>.</span></h4>
                    </div>
                    
                    <h2 class="login-title text-white">Welcome Back</h2>
                    <p class="login-subtitle">Login to get your movie recommendations.</p>

                    <div id="loginAlert" class="alert alert-danger d-none" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <span id="loginErrorMessage"></span>
                    </div>

                    <form id="loginForm">
                        <label class="form-label-custom">Email</label>
                        <div class="input-group-premium">
                            <span class="input-icon"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" 
                                    placeholder="Email Address" required>
                        </div>

                        <label class="form-label-custom">Password</label>
                        <div class="input-group-premium">
                            <span class="input-icon"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" 
                                    placeholder="Password" required>
                            <button class="toggle-password-btn" type="button" data-target="password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>

                        <button type="submit" class="btn btn-premium">
                            Secure Login
                        </button>
                    </form>

                    <div class="auth-footer">
                        New user? <a href="#" class="auth-link" data-bs-toggle="modal" data-bs-target="#registerModal" data-bs-dismiss="modal">Create Account</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content register-modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-0">
                    <div class="text-start mb-4">
                        <h4 class="fw-bold mb-1 logo-text">MoodAI<span>.</span></h4>
                    </div>
                    
                    <h2 class="register-title text-white">Join Us</h2>
                    <p class="register-subtitle">Create your profile to find movies for your mood.</p>

                    <div id="registerAlert" class="alert alert-danger d-none" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <span id="registerErrorMessage"></span>
                    </div>

                    <form id="registerForm">
                        <label class="form-label-custom">Your Name</label>
                        <div class="input-group-premium">
                            <span class="input-icon"><i class="bi bi-person-circle"></i></span>
                            <input type="text" class="form-control" id="reg_name" name="name" 
                                    placeholder="Enter your name" required>
                        </div>
                        
                        <label class="form-label-custom">Email Address</label>
                        <div class="input-group-premium">
                            <span class="input-icon"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="reg_email" name="email" 
                                    placeholder="Email Address" required>
                        </div>

                        <label class="form-label-custom">Password</label>
                        <div class="input-group-premium">
                            <span class="input-icon"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="reg_password" name="password" 
                                    placeholder="8+ characters" required>
                            <button class="toggle-password-btn" type="button" data-target="reg_password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>

                        <label class="form-label-custom">Confirm Password</label>
                        <div class="input-group-premium">
                            <span class="input-icon"><i class="bi bi-shield-lock"></i></span>
                            <input type="password" class="form-control" id="reg_confirm_password" name="confirm_password" 
                                    placeholder="Re-enter Password" required>
                            <button class="toggle-password-btn" type="button" data-target="reg_confirm_password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>

                        <button type="submit" class="btn btn-premium">
                            Create Account
                        </button>
                    </form>

                    <div class="auth-footer">
                        Already have an account? <a href="#" class="auth-link" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Login Now</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true,
            easing: 'ease-out-cubic'
        });

        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        });

        // AJAX Registration Handling
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const registerAlert = document.getElementById('registerAlert');
            const errorMessage = document.getElementById('registerErrorMessage');

            fetch('register.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        const loginAlert = document.getElementById('loginAlert');
                        const loginError = document.getElementById('loginErrorMessage');
                        
                        loginAlert.classList.remove('alert-danger', 'd-none');
                        loginAlert.classList.add('alert-success');
                        loginError.textContent = data.message;
                        
                        bootstrap.Modal.getInstance(document.getElementById('registerModal')).hide();
                        new bootstrap.Modal(document.getElementById('loginModal')).show();
                    }
                } else {
                    errorMessage.textContent = data.message;
                    registerAlert.classList.remove('d-none');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                errorMessage.textContent = "A system error occurred. Please try again.";
                registerAlert.classList.remove('d-none');
            });
        });

        // AJAX Login Handling
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const loginAlert = document.getElementById('loginAlert');
            const errorMessage = document.getElementById('loginErrorMessage');

            fetch('login.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    errorMessage.textContent = data.message;
                    loginAlert.classList.remove('d-none');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                errorMessage.textContent = "A system error occurred. Please try again.";
                loginAlert.classList.remove('d-none');
            });
        });

        // Toggle Password
        document.querySelectorAll('.toggle-password-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.querySelector('i').classList.toggle('bi-eye');
                this.querySelector('i').classList.toggle('bi-eye-slash');
            });
        });
    </script>
</body>
</html>
