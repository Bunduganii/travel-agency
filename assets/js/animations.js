/**
 * Animations JavaScript File
 * Handles interactive animations and effects
 */

document.addEventListener('DOMContentLoaded', function() {
    initScrollAnimations();
    initHoverEffects();
    initButtonAnimations();
    initFormAnimations();
    initCounterAnimations();
});

/**
 * Initialize scroll-triggered animations
 */
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Observe elements with animation classes
    const animatedElements = document.querySelectorAll('.card-hover, .hover-lift, .stagger-item');
    animatedElements.forEach(el => observer.observe(el));
}

/**
 * Initialize hover effects
 */
function initHoverEffects() {
    // Add hover lift effect to cards
    const cards = document.querySelectorAll('.action-card, .package-card, .hotel-card, .flight-card, .booking-card');
    cards.forEach(card => {
        card.classList.add('hover-lift');
        
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Button ripple effect
    const buttons = document.querySelectorAll('.btn-primary, .btn-secondary');
    buttons.forEach(button => {
        button.classList.add('ripple');
        
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple-effect');
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
}

/**
 * Initialize button animations
 */
function initButtonAnimations() {
    const buttons = document.querySelectorAll('.btn');
    
    buttons.forEach(button => {
        // Add pulse animation on hover
        button.addEventListener('mouseenter', function() {
            if (!this.classList.contains('btn-disabled')) {
                this.style.transform = 'scale(1.05)';
            }
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
        
        // Add click animation
        button.addEventListener('click', function() {
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
        });
    });
}

/**
 * Initialize form animations
 */
function initFormAnimations() {
    const inputs = document.querySelectorAll('input, textarea, select');
    
    inputs.forEach(input => {
        // Focus animation
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('input-focused');
            this.style.transform = 'scale(1.02)';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('input-focused');
            this.style.transform = 'scale(1)';
        });
        
        // Label animation
        const label = this.parentElement.querySelector('label');
        if (label && input.value) {
            label.classList.add('label-active');
        }
        
        input.addEventListener('input', function() {
            if (this.value) {
                label?.classList.add('label-active');
            } else {
                label?.classList.remove('label-active');
            }
        });
    });
}

/**
 * Initialize counter animations
 */
function initCounterAnimations() {
    const counters = document.querySelectorAll('[data-count]');
    
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-count'));
        const duration = 2000; // 2 seconds
        const increment = target / (duration / 16); // 60fps
        let current = 0;
        
        const updateCounter = () => {
            current += increment;
            if (current < target) {
                counter.textContent = Math.floor(current);
                requestAnimationFrame(updateCounter);
            } else {
                counter.textContent = target;
            }
        };
        
        // Start animation when element is visible
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    updateCounter();
                    observer.unobserve(entry.target);
                }
            });
        });
        
        observer.observe(counter);
    });
}

/**
 * Animate progress bar
 */
function animateProgressBar(barElement, targetPercent) {
    let current = 0;
    const increment = targetPercent / 50; // 50 frames
    
    const update = () => {
        current += increment;
        if (current < targetPercent) {
            barElement.style.width = current + '%';
            requestAnimationFrame(update);
        } else {
            barElement.style.width = targetPercent + '%';
        }
    };
    
    update();
}

/**
 * Shake element animation
 */
function shakeElement(element) {
    element.classList.add('shake');
    setTimeout(() => {
        element.classList.remove('shake');
    }, 500);
}

/**
 * Bounce element animation
 */
function bounceElement(element) {
    element.classList.add('bounce');
    setTimeout(() => {
        element.classList.remove('bounce');
    }, 1000);
}

/**
 * Pulse element animation
 */
function pulseElement(element) {
    element.classList.add('pulse');
    setTimeout(() => {
        element.classList.remove('pulse');
    }, 2000);
}

/**
 * Fade in element
 */
function fadeInElement(element, duration = 500) {
    element.style.opacity = '0';
    element.style.display = 'block';
    
    let start = null;
    const animate = (timestamp) => {
        if (!start) start = timestamp;
        const progress = timestamp - start;
        const opacity = Math.min(progress / duration, 1);
        
        element.style.opacity = opacity;
        
        if (progress < duration) {
            requestAnimationFrame(animate);
        }
    };
    
    requestAnimationFrame(animate);
}

/**
 * Fade out element
 */
function fadeOutElement(element, duration = 500) {
    let start = null;
    const initialOpacity = parseFloat(window.getComputedStyle(element).opacity);
    
    const animate = (timestamp) => {
        if (!start) start = timestamp;
        const progress = timestamp - start;
        const opacity = initialOpacity * (1 - Math.min(progress / duration, 1));
        
        element.style.opacity = opacity;
        
        if (progress < duration) {
            requestAnimationFrame(animate);
        } else {
            element.style.display = 'none';
        }
    };
    
    requestAnimationFrame(animate);
}

/**
 * Slide element in from direction
 */
function slideInElement(element, direction = 'up', duration = 500) {
    const directions = {
        up: { from: 'translateY(50px)', to: 'translateY(0)' },
        down: { from: 'translateY(-50px)', to: 'translateY(0)' },
        left: { from: 'translateX(50px)', to: 'translateX(0)' },
        right: { from: 'translateX(-50px)', to: 'translateX(0)' }
    };
    
    const dir = directions[direction] || directions.up;
    element.style.transform = dir.from;
    element.style.opacity = '0';
    element.style.display = 'block';
    
    let start = null;
    const animate = (timestamp) => {
        if (!start) start = timestamp;
        const progress = timestamp - start;
        const progressPercent = Math.min(progress / duration, 1);
        
        element.style.transform = dir.to;
        element.style.opacity = progressPercent;
        
        if (progress < duration) {
            requestAnimationFrame(animate);
        }
    };
    
    requestAnimationFrame(animate);
}

/**
 * Add CSS for ripple effect
 */
const style = document.createElement('style');
style.textContent = `
    .ripple-effect {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.6);
        transform: scale(0);
        animation: ripple-animation 0.6s ease-out;
        pointer-events: none;
    }
    
    @keyframes ripple-animation {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    .input-focused {
        position: relative;
    }
    
    .input-focused::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 2px;
        background: var(--primary-color);
        animation: slideInLeft 0.3s ease-out;
    }
    
    .label-active {
        color: var(--primary-color);
        transform: translateY(-5px);
        font-size: 0.85rem;
    }
`;
document.head.appendChild(style);

// Export animation functions
window.Animations = {
    animateProgressBar,
    shakeElement,
    bounceElement,
    pulseElement,
    fadeInElement,
    fadeOutElement,
    slideInElement
};

