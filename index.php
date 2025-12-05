<?php
require_once 'includes/functions.php';

$products = get_all_products();
$categories = array_unique(array_column($products, 'category'));
$cart_count = get_cart_count();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kafe Latte - Premium Coffee Experience</title>
    <meta name="description" content="Nikmati kopi premium terbaik di Kafe Latte. Tempat sempurna untuk menikmati momen berharga Anda.">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header Navigation -->
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <a href="#home" class="logo">
                    <i class="fas fa-coffee"></i>
                    <span>KAFE LATTE</span>
                </a>
                
                <ul class="nav-menu">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#menu">Menu</a></li>
                    <li><a href="#about">About</a></li>
                    <li>
                        <a href="pages/cart.php" class="cart-link">
                            <i class="fas fa-shopping-bag"></i>
                            <span class="cart-badge"><?php echo $cart_count; ?></span>
                        </a>
                    </li>
                    
                    <?php if (is_logged_in()): ?>
                        <?php if (is_admin()): ?>
                            <li><a href="admin/dashboard.php" class="btn-admin">Dashboard</a></li>
                        <?php else: ?>
                            <li><a href="pages/history.php">Orders</a></li>
                        <?php endif; ?>
                        <li><a href="auth/logout.php" class="btn-logout">Logout</a></li>
                    <?php else: ?>
                        <li><a href="auth/login.php" class="btn-login">Sign In</a></li>
                        <li><a href="auth/register.php" class="btn-register">Sign Up</a></li>
                    <?php endif; ?>
                </ul>
                
                <button class="hamburger" aria-label="Menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </nav>
        </div>
    </header>

    <!-- Hero Slider Section -->
    <section id="home" class="hero-slider">
        <div class="slider-container">
            <div class="slide active" style="background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=1920&q=80');">
                <div class="slide-content">
                    <h1 class="slide-title">Welcome to Kafe Latte</h1>
                    <p class="slide-text">Experience the finest coffee crafted with passion</p>
                    <a href="#menu" class="btn-hero">Explore Menu</a>
                </div>
            </div>
            <div class="slide" style="background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('https://images.unsplash.com/photo-1511920170033-f8396924c348?w=1920&q=80');">
                <div class="slide-content">
                    <h1 class="slide-title">Premium Coffee Selection</h1>
                    <p class="slide-text">From the finest beans to your perfect cup</p>
                    <a href="#menu" class="btn-hero">Order Now</a>
                </div>
            </div>
            <div class="slide" style="background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=1920&q=80');">
                <div class="slide-content">
                    <h1 class="slide-title">Your Perfect Coffee Moment</h1>
                    <p class="slide-text">A warm atmosphere where memories are made</p>
                    <a href="#menu" class="btn-hero">Discover More</a>
                </div>
            </div>
        </div>
        
        <button class="slider-btn prev" aria-label="Previous">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="slider-btn next" aria-label="Next">
            <i class="fas fa-chevron-right"></i>
        </button>
        <div class="slider-dots"></div>
    </section>

    <!-- Menu Section -->
    <section id="menu" class="menu-section">
        <div class="container">
            <div class="section-header">
                <h2>Our Menu</h2>
                <p>Discover our carefully curated selection of beverages and treats</p>
            </div>

            <div class="menu-filter">
                <button class="filter-btn active" data-filter="all">All</button>
                <?php foreach ($categories as $category): ?>
                    <button class="filter-btn" data-filter="<?php echo strtolower($category); ?>">
                        <?php echo ucfirst($category); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="menu-grid">
                <?php foreach ($products as $product): ?>
                    <article class="menu-card" data-category="<?php echo strtolower($product['category']); ?>">
                        <div class="menu-image">
                            <img src="<?php echo htmlspecialchars($product['image_url'] ?: 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=400&q=80'); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 loading="lazy">
                            <?php if ($product['stock'] < 10 && $product['stock'] > 0): ?>
                                <span class="badge-limited">Limited</span>
                            <?php endif; ?>
                        </div>
                        <div class="menu-content">
                            <h3 class="menu-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="menu-description"><?php echo htmlspecialchars($product['description']); ?></p>
                            <div class="menu-footer">
                                <span class="menu-price"><?php echo format_rupiah($product['price']); ?></span>
                                <?php if ($product['stock'] > 0): ?>
                                    <button class="btn-add-cart" onclick="addToCart(<?php echo $product['product_id']; ?>)">
                                        <i class="fas fa-plus"></i>
                                        <span>Add</span>
                                    </button>
                                <?php else: ?>
                                    <span class="out-of-stock">Out of Stock</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about-section">
        <div class="container">
            <div class="about-grid">
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1442512595331-e89e73853f31?w=800&q=80" 
                         alt="Kafe Latte Interior"
                         loading="lazy">
                </div>
                <div class="about-content">
                    <h2>About Kafe Latte</h2>
                    <p>Since 2020, Kafe Latte has been dedicated to serving exceptional coffee experiences. We carefully source premium beans from the finest plantations and craft each cup with expertise and passion.</p>
                    <p>More than just a coffee shop, we're a community space where connections are made and memories are created over the perfect cup of coffee.</p>
                    
                    <div class="about-features">
                        <div class="feature">
                            <i class="fas fa-coffee"></i>
                            <h4>Premium Beans</h4>
                            <p>Ethically sourced from top plantations</p>
                        </div>
                        <div class="feature">
                            <i class="fas fa-award"></i>
                            <h4>Expert Baristas</h4>
                            <p>Trained professionals crafting perfection</p>
                        </div>
                        <div class="feature">
                            <i class="fas fa-heart"></i>
                            <h4>Made with Love</h4>
                            <p>Every cup tells a story</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h3>
                        <i class="fas fa-coffee"></i>
                        KAFE LATTE
                    </h3>
                    <p>Premium coffee for your best moments</p>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                
                <div class="footer-col">
                    <h4>Hours</h4>
                    <p>Monday - Friday: 07:00 - 22:00</p>
                    <p>Saturday - Sunday: 08:00 - 23:00</p>
                </div>
                
                <div class="footer-col">
                    <h4>Contact</h4>
                    <p><i class="fas fa-map-marker-alt"></i> Depok, West Java, ID</p>
                    <p><i class="fas fa-phone"></i> (021) 1234-5678</p>
                    <p><i class="fas fa-envelope"></i> hello@kafelatte.com</p>
                </div>
                
                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#home">Home</a></li>
                        <li><a href="#menu">Menu</a></li>
                        <li><a href="#about">About</a></li>
                        <li><a href="pages/cart.php">Cart</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2024 Kafe Latte. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="assets/js/script.js"></script>
    <script>
    // Add to Cart Function
    function addToCart(productId) {
        const btn = event.target.closest('.btn-add-cart');
        if (!btn || btn.disabled) return;
        
        const originalHTML = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="loading"></span>';
        
        fetch('pages/add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'product_id=' + productId + '&quantity=1'
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = originalHTML;
            
            if (data.success) {
                updateCartBadge(data.cart_count);
                showNotification('Product added to cart successfully!', 'success');
            } else {
                showNotification(data.message || 'Failed to add to cart', 'error');
            }
        })
        .catch(error => {
            btn.disabled = false;
            btn.innerHTML = originalHTML;
            console.error('Error:', error);
            showNotification('An error occurred. Please try again.', 'error');
        });
    }

    // Update Cart Badge
    function updateCartBadge(count) {
        const badge = document.querySelector('.cart-badge');
        if (badge) {
            badge.textContent = count;
            badge.style.animation = 'none';
            setTimeout(() => {
                badge.style.animation = 'pulse 0.3s ease';
            }, 10);
        }
    }

    // Show Notification
    function showNotification(message, type = 'success') {
        const existingNotifications = document.querySelectorAll('.notification');
        existingNotifications.forEach(notif => notif.remove());
        
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        
        const icon = type === 'success' 
            ? '<i class="fas fa-check-circle"></i>' 
            : '<i class="fas fa-times-circle"></i>';
        
        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px;">
                ${icon}
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => notification.classList.add('show'), 100);
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
        
        notification.addEventListener('click', () => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        });
    }

    // Initialize on DOM Load
    document.addEventListener('DOMContentLoaded', function() {
        initMobileMenu();
        initHeroSlider();
        initMenuFilter();
        initSmoothScroll();
        initScrollEffects();
        initBackToTop();
    });

    // Mobile Menu Toggle
    function initMobileMenu() {
        const hamburger = document.querySelector('.hamburger');
        const navMenu = document.querySelector('.nav-menu');
        
        if (!hamburger) return;
        
        hamburger.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            
            const spans = hamburger.querySelectorAll('span');
            if (navMenu.classList.contains('active')) {
                spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
                spans[1].style.opacity = '0';
                spans[2].style.transform = 'rotate(-45deg) translate(7px, -6px)';
            } else {
                spans.forEach(span => {
                    span.style.transform = '';
                    span.style.opacity = '1';
                });
            }
        });
        
        document.querySelectorAll('.nav-menu a').forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
                hamburger.querySelectorAll('span').forEach(span => {
                    span.style.transform = '';
                    span.style.opacity = '1';
                });
            });
        });
    }

    // Hero Slider
    function initHeroSlider() {
        const slides = document.querySelectorAll('.slide');
        const prevBtn = document.querySelector('.slider-btn.prev');
        const nextBtn = document.querySelector('.slider-btn.next');
        const dotsContainer = document.querySelector('.slider-dots');
        
        if (!slides.length) return;
        
        let currentSlide = 0;
        let slideInterval;
        
        // Create dots
        slides.forEach((_, index) => {
            const dot = document.createElement('div');
            dot.classList.add('dot');
            if (index === 0) dot.classList.add('active');
            dot.addEventListener('click', () => goToSlide(index));
            dotsContainer.appendChild(dot);
        });
        
        const dots = document.querySelectorAll('.dot');
        
        function goToSlide(n) {
            slides[currentSlide].classList.remove('active');
            dots[currentSlide].classList.remove('active');
            
            currentSlide = (n + slides.length) % slides.length;
            
            slides[currentSlide].classList.add('active');
            dots[currentSlide].classList.add('active');
        }
        
        function nextSlide() {
            goToSlide(currentSlide + 1);
        }
        
        function prevSlide() {
            goToSlide(currentSlide - 1);
        }
        
        function startSlideShow() {
            slideInterval = setInterval(nextSlide, 5000);
        }
        
        function stopSlideShow() {
            clearInterval(slideInterval);
        }
        
        if (prevBtn) prevBtn.addEventListener('click', () => {
            prevSlide();
            stopSlideShow();
            startSlideShow();
        });
        
        if (nextBtn) nextBtn.addEventListener('click', () => {
            nextSlide();
            stopSlideShow();
            startSlideShow();
        });
        
        startSlideShow();
        
        const sliderContainer = document.querySelector('.slider-container');
        if (sliderContainer) {
            sliderContainer.addEventListener('mouseenter', stopSlideShow);
            sliderContainer.addEventListener('mouseleave', startSlideShow);
        }
        
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                prevSlide();
                stopSlideShow();
                startSlideShow();
            } else if (e.key === 'ArrowRight') {
                nextSlide();
                stopSlideShow();
                startSlideShow();
            }
        });
    }

    // Menu Filter
    function initMenuFilter() {
        const filterBtns = document.querySelectorAll('.filter-btn');
        const menuCards = document.querySelectorAll('.menu-card');
        
        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                filterBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                const filter = btn.getAttribute('data-filter');
                
                menuCards.forEach(card => {
                    const category = card.getAttribute('data-category');
                    
                    if (filter === 'all' || category === filter) {
                        card.style.display = 'block';
                        setTimeout(() => {
                            card.style.opacity = '1';
                            card.style.transform = 'translateY(0)';
                        }, 50);
                    } else {
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(20px)';
                        setTimeout(() => {
                            card.style.display = 'none';
                        }, 300);
                    }
                });
            });
        });
    }

    // Smooth Scrolling
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                
                if (target) {
                    const headerOffset = 104;
                    const elementPosition = target.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                    
                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
    }

    // Scroll Effects
    function initScrollEffects() {
        const header = document.querySelector('.header');
        let lastScroll = 0;
        
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            
            // Header shadow on scroll
            if (currentScroll > 0) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
            
            lastScroll = currentScroll;
        });
        
        // Intersection Observer for fade-in animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);
        
        document.querySelectorAll('.menu-card, .feature').forEach(el => {
            el.classList.add('fade-in');
            observer.observe(el);
        });
    }

    // Back to Top Button
    function initBackToTop() {
        const backToTopBtn = document.createElement('button');
        backToTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
        backToTopBtn.className = 'back-to-top';
        backToTopBtn.setAttribute('aria-label', 'Back to top');
        document.body.appendChild(backToTopBtn);
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopBtn.style.opacity = '1';
                backToTopBtn.style.visibility = 'visible';
            } else {
                backToTopBtn.style.opacity = '0';
                backToTopBtn.style.visibility = 'hidden';
            }
        });
        
        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    </script>
</body>
</html>