// Banner.js - Script untuk navbar dan banner restaurant

document.addEventListener('DOMContentLoaded', function() {
    
    // Smooth scroll untuk animasi
    initSmoothScroll();
    
    // Cart icon hover effect
    initCartHoverEffect();
    
    // Parallax effect untuk banner (optional)
    initParallaxEffect();
    
});

// Smooth Scroll
function initSmoothScroll() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            
            if (targetId !== '#' && document.querySelector(targetId)) {
                e.preventDefault();
                
                document.querySelector(targetId).scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Cart Icon Hover Effect
function initCartHoverEffect() {
    const cartIcon = document.querySelector('.cart-icon');
    
    if (cartIcon) {
        cartIcon.addEventListener('mouseenter', function() {
            const badge = this.querySelector('.cart-badge');
            if (badge) {
                badge.style.animation = 'none';
                setTimeout(() => {
                    badge.style.animation = 'pulse 0.5s ease';
                }, 10);
            }
        });
    }
}

// Parallax Effect untuk Banner
function initParallaxEffect() {
    const banner = document.querySelector('.banner-restaurant');
    const bannerImg = document.querySelector('.banner-img');
    
    if (banner && bannerImg) {
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const parallaxSpeed = 0.5;
            
            if (scrolled < banner.offsetHeight) {
                bannerImg.style.transform = `translateY(${scrolled * parallaxSpeed}px)`;
            }
        });
    }
}

// Update cart count dynamically (jika diperlukan)
function updateCartCount(count) {
    const cartBadge = document.querySelector('.cart-badge');
    const cartIcon = document.querySelector('.cart-icon');
    
    if (count > 0) {
        if (cartBadge) {
            cartBadge.textContent = count;
            cartBadge.style.animation = 'pulse 0.5s ease';
        } else if (cartIcon) {
            const badge = document.createElement('span');
            badge.className = 'cart-badge';
            badge.textContent = count;
            cartIcon.querySelector('a').appendChild(badge);
        }
    } else {
        if (cartBadge) {
            cartBadge.remove();
        }
    }
}

// Export functions untuk digunakan di file lain
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        updateCartCount
    };
}