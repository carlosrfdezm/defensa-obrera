// js/main.js
document.addEventListener('DOMContentLoaded', function() {
    // Menú móvil
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('open');
        });
    }
    
    document.querySelectorAll('.nav-menu a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                navMenu.classList.remove('open');
            }
        });
    });
    
    // Slider
    class Slider {
        constructor(container) {
            this.container = container;
            this.track = container.querySelector('.slider-track');
            this.slides = container.querySelectorAll('.slider-slide');
            this.dots = container.querySelectorAll('.slider-dot');
            this.prevBtn = container.querySelector('.slider-btn.prev');
            this.nextBtn = container.querySelector('.slider-btn.next');
            this.currentSlide = 0;
            this.totalSlides = this.slides.length;
            
            if (this.totalSlides === 0) return;
            this.init();
        }
        
        init() {
            this.goToSlide(0);
            if (this.prevBtn) this.prevBtn.addEventListener('click', () => this.prev());
            if (this.nextBtn) this.nextBtn.addEventListener('click', () => this.next());
            
            this.dots.forEach((dot, index) => {
                dot.addEventListener('click', () => this.goToSlide(index));
            });
            
            setInterval(() => this.next(), 5000);
        }
        
        goToSlide(index) {
            if (index < 0) index = this.totalSlides - 1;
            if (index >= this.totalSlides) index = 0;
            this.currentSlide = index;
            this.track.style.transform = `translateX(-${index * 100}%)`;
            
            this.dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === index);
            });
        }
        
        prev() { this.goToSlide(this.currentSlide - 1); }
        next() { this.goToSlide(this.currentSlide + 1); }
    }
    
    const sliderContainer = document.querySelector('.slider-container');
    if (sliderContainer) {
        new Slider(sliderContainer);
    }
});