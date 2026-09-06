/**
 * CAROUSEL & MARQUEE MODULE
 * Manages the Homepage Hero dynamic image slider, slide timers, manual dot navigation,
 * and the continuous scrolling marquee image gallery.
 */

let currentSlideIdx = 0;
let slideTimer = null;

/**
 * Fetches hero slider images from backend API and initializes the carousel slides and indicators.
 */
async function initHeroCarousel() {
    const container = document.getElementById('hero-slides');
    const dotsContainer = document.getElementById('hero-dots');
    
    if (!container) return;

    try {
        const res = await fetch('actions/sliders_action.php?action=get_all');
        const data = await res.json();
        
        // Filter only 'hero' type sliders
        const heroSlides = (data && data.success && Array.isArray(data.sliders)) 
            ? data.sliders.filter(s => s && s.type === 'hero') 
            : [];
        const slides = heroSlides.length > 0 
            ? heroSlides 
            : [{ image_url: 'assets/image/background.jpg' }]; // Fallback default image

        // Render Hero Slides
        container.innerHTML = slides.map((slide, i) => `
            <div class="hero-slide absolute inset-0 w-full h-full transition-opacity duration-1000 ease-in-out ${i === 0 ? 'opacity-100' : 'opacity-0'}">
                <img src="${slide.image_url}" class="w-full h-full object-cover" alt="Hero Slide ${i}">
                <div class="absolute inset-0 bg-black/30"></div>
            </div>
        `).join('');

        // Render Navigation Dots
        if (dotsContainer) {
            dotsContainer.innerHTML = slides.map((_, i) => `
                <button onclick="goToSlide(${i})" class="hero-dot w-3 h-3 rounded-full transition-all duration-300 cursor-pointer ${i === 0 ? 'bg-white scale-125' : 'bg-white/50 hover:bg-white/80'}"></button>
            `).join('');
        }
        
        startSlideTimer();
    } catch (err) {
        console.error('Hero carousel load failed:', err);
        // Fallback to single placeholder slide
        container.innerHTML = `
            <div class="hero-slide absolute inset-0 w-full h-full">
                <img src="assets/image/background.jpg" class="w-full h-full object-cover" alt="Fallback">
                <div class="absolute inset-0 bg-black/30"></div>
            </div>
        `;
        if (dotsContainer) {
            dotsContainer.innerHTML = '<button class="hero-dot w-3 h-3 rounded-full bg-white scale-125"></button>';
        }
        startSlideTimer();
    }
}

/**
 * Displays a specific slide by its zero-based index.
 * Adjusts opacity transitions and updates the active dot indicator.
 * 
 * @param {number} index - Target slide index.
 */
function showSlide(index) {
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.hero-dot');
    
    if (index >= slides.length) index = 0;
    if (index < 0) index = slides.length - 1;
    
    currentSlideIdx = index;

    slides.forEach((slide, i) => {
        slide.classList.toggle('opacity-100', i === index);
        slide.classList.toggle('opacity-0', i !== index);
    });

    dots.forEach((dot, i) => {
        if (i === index) {
            dot.className = "hero-dot w-3 h-3 rounded-full transition-all duration-300 cursor-pointer bg-white scale-125";
        } else {
            dot.className = "hero-dot w-3 h-3 rounded-full transition-all duration-300 cursor-pointer bg-white/50 hover:bg-white/80";
        }
    });
}

/**
 * Advances the carousel forward to the next slide and resets the auto-timer.
 */
function nextSlide() {
    showSlide(currentSlideIdx + 1);
    resetSlideTimer();
}

/**
 * Moves the carousel back to the previous slide and resets the auto-timer.
 */
function prevSlide() {
    showSlide(currentSlideIdx - 1);
    resetSlideTimer();
}

/**
 * Direct navigation to a specific slide (e.g. from dot click).
 * 
 * @param {number} index - Target slide index.
 */
function goToSlide(index) {
    showSlide(index);
    resetSlideTimer();
}

/**
 * Starts the automatic 5-second slide progression timer.
 */
function startSlideTimer() {
    clearInterval(slideTimer);
    slideTimer = setInterval(() => nextSlide(), 5000);
}

/**
 * Resets the slide timer when a user interacts manually.
 */
function resetSlideTimer() {
    clearInterval(slideTimer);
    startSlideTimer();
}

/**
 * =========================================================================
 * ISLAND GALLERY CAROUSEL MODULE
 * Multi-card responsive slider with auto-play, touch swipe, and lightbox.
 * =========================================================================
 */

let currentGalleryIdx = 0;
let galleryTimer = null;
let gallerySlides = [];

/**
 * Determines number of visible cards based on viewport width.
 */
function getCardsPerView() {
    if (window.innerWidth < 640) return 1;
    if (window.innerWidth < 1024) return 2;
    return 3;
}

/**
 * Default fallback images for the gallery when database is offline or empty.
 */
const defaultGalleryPhotos = [
    { title: 'Tropical Oasis & Palm Garden', image_url: 'assets/image/background.jpg', tag: 'Paradise' },
    { title: 'Cozy Backpacker Dormitory', image_url: 'assets/image/69b3bfd75e102.jpg', tag: 'Boutique Dorm' },
    { title: 'Tropical Queen Comfort', image_url: 'assets/image/69b3c84d5988d.jpg', tag: 'Private Rooms' },
    { title: 'Sunset Coastline & Ocean Dip', image_url: 'assets/image/69b3e44e0a7b6.jpg', tag: 'Panglao Shore' },
    { title: 'Social Communal Lounge & Bar', image_url: 'assets/image/69b3e47771e0f.jpg', tag: 'Inn Life' },
    { title: 'Digital Nomad Starlink Space', image_url: 'assets/image/69b3ec692f61e.jpg', tag: 'Fast Starlink' },
    { title: 'Island Adventure Excursions', image_url: 'assets/image/69be8a7da2074.jpg', tag: 'Adventure' },
    { title: 'Sunset Vibes & Acoustic Jam', image_url: 'assets/image/69be8ac371967.jpg', tag: 'Community' }
];

/**
 * Initializes the Island Gallery Carousel.
 * Fetches slides from backend API, renders cards, binds controls and touch events.
 */
async function initGalleryCarousel() {
    const track = document.getElementById('gallery-carousel-track');
    const dotsContainer = document.getElementById('gallery-dots');
    const container = document.getElementById('gallery-carousel-container');

    if (!track) return;

    try {
        const res = await fetch('actions/sliders_action.php?action=get_all');
        const data = await res.json();

        const fetched = (data && data.success && Array.isArray(data.sliders))
            ? data.sliders.filter(s => s && s.type === 'marquee' && s.is_active == 1)
            : [];

        if (fetched.length > 0) {
            gallerySlides = fetched.map(s => ({
                title: s.title || 'Island Sanctuary',
                image_url: s.image_url,
                tag: 'Island Life'
            }));
            // If fewer than 4 items, merge with fallback photos to ensure full carousel experience
            if (gallerySlides.length < 4) {
                const uniqueUrls = new Set(gallerySlides.map(s => s.image_url));
                for (const item of defaultGalleryPhotos) {
                    if (!uniqueUrls.has(item.image_url)) {
                        gallerySlides.push(item);
                        uniqueUrls.add(item.image_url);
                    }
                }
            }
        } else {
            gallerySlides = [...defaultGalleryPhotos];
        }
    } catch (err) {
        console.warn('Gallery sliders load failed, using default images:', err);
        gallerySlides = [...defaultGalleryPhotos];
    }

    renderGallerySlides();
    renderGalleryDots();
    updateGalleryCarousel();
    startGalleryTimer();

    // Pause on hover
    if (container) {
        container.addEventListener('mouseenter', () => clearInterval(galleryTimer));
        container.addEventListener('mouseleave', () => startGalleryTimer());
    }

    // Touch swipe support for mobile devices
    setupGalleryTouch(container || track);

    // Re-adjust slider position on window resize
    window.addEventListener('resize', () => {
        renderGalleryDots();
        updateGalleryCarousel();
    });
}

/**
 * Renders individual cards into the gallery carousel track.
 */
function renderGallerySlides() {
    const track = document.getElementById('gallery-carousel-track');
    if (!track) return;

    track.innerHTML = gallerySlides.map((slide, i) => `
        <div class="gallery-card-item w-full sm:w-1/2 lg:w-1/3 flex-shrink-0 px-3 select-none">
            <div class="group relative h-80 sm:h-96 md:h-[420px] rounded-3xl overflow-hidden bg-slate-900 shadow-md hover:shadow-2xl transition-all duration-500 cursor-pointer border border-slate-100"
                 onclick="openGalleryLightbox('${slide.image_url}', '${slide.title.replace(/'/g, "\\'")}')">
                
                <!-- Background Image -->
                <img src="${slide.image_url}" alt="${slide.title}" 
                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                
                <!-- Luxury Ambient Gradient Overlay -->
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/30 to-transparent"></div>
                
                <!-- Category Tag Badge -->
                <div class="absolute top-4 left-4 z-10">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-white/90 backdrop-blur-md text-slate-900 shadow-md">
                        <i class="fas fa-sparkles text-amber-500 text-[10px]"></i>
                        ${slide.tag || 'Island Life'}
                    </span>
                </div>

                <!-- Zoom Icon Button (Hover reveal) -->
                <div class="absolute top-4 right-4 z-10 w-10 h-10 rounded-full bg-slate-900/60 backdrop-blur-md text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 transition-all duration-300">
                    <i class="fas fa-expand text-sm text-amber-400"></i>
                </div>

                <!-- Card Bottom Content -->
                <div class="absolute bottom-0 inset-x-0 p-6 z-10">
                    <h3 class="text-xl sm:text-2xl font-serif font-bold text-white mb-2 drop-shadow-md group-hover:text-amber-300 transition-colors">
                        ${slide.title}
                    </h3>
                    <p class="text-slate-300 text-xs sm:text-sm font-light line-clamp-2 mb-3">
                        Experience the laid-back charm, social energy, and tranquil comforts of our boutique backpackers haven.
                    </p>
                    <div class="inline-flex items-center gap-2 text-xs font-bold text-amber-400 group-hover:translate-x-1 transition-transform">
                        <span>Click to view photo</span>
                        <i class="fas fa-arrow-right text-[10px]"></i>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

/**
 * Renders dot pagination indicators for the gallery carousel.
 */
function renderGalleryDots() {
    const dotsContainer = document.getElementById('gallery-dots');
    if (!dotsContainer) return;

    const perView = getCardsPerView();
    const totalSteps = Math.max(1, gallerySlides.length - perView + 1);

    dotsContainer.innerHTML = Array.from({ length: totalSteps }).map((_, i) => `
        <button onclick="goToGallerySlide(${i})" 
                aria-label="Go to gallery slide ${i + 1}"
                class="gallery-dot h-2.5 rounded-full transition-all duration-300 cursor-pointer ${i === currentGalleryIdx ? 'w-8 bg-amber-500' : 'w-2.5 bg-slate-300 hover:bg-slate-400'}">
        </button>
    `).join('');
}

/**
 * Updates track translation and active dot state.
 */
function updateGalleryCarousel() {
    const track = document.getElementById('gallery-carousel-track');
    if (!track || gallerySlides.length === 0) return;

    const perView = getCardsPerView();
    const maxIdx = Math.max(0, gallerySlides.length - perView);
    if (currentGalleryIdx > maxIdx) currentGalleryIdx = maxIdx;
    if (currentGalleryIdx < 0) currentGalleryIdx = 0;

    const percentage = currentGalleryIdx * (100 / perView);
    track.style.transform = `translateX(-${percentage}%)`;

    // Update dots
    const dots = document.querySelectorAll('.gallery-dot');
    dots.forEach((dot, i) => {
        if (i === currentGalleryIdx) {
            dot.className = "gallery-dot h-2.5 rounded-full transition-all duration-300 cursor-pointer w-8 bg-amber-500";
        } else {
            dot.className = "gallery-dot h-2.5 rounded-full transition-all duration-300 cursor-pointer w-2.5 bg-slate-300 hover:bg-slate-400";
        }
    });
}

/**
 * Advances gallery carousel to the next slide.
 */
function nextGallerySlide() {
    const perView = getCardsPerView();
    const maxIdx = Math.max(0, gallerySlides.length - perView);
    
    if (currentGalleryIdx >= maxIdx) {
        currentGalleryIdx = 0;
    } else {
        currentGalleryIdx++;
    }
    updateGalleryCarousel();
    resetGalleryTimer();
}

/**
 * Moves gallery carousel to the previous slide.
 */
function prevGallerySlide() {
    const perView = getCardsPerView();
    const maxIdx = Math.max(0, gallerySlides.length - perView);

    if (currentGalleryIdx <= 0) {
        currentGalleryIdx = maxIdx;
    } else {
        currentGalleryIdx--;
    }
    updateGalleryCarousel();
    resetGalleryTimer();
}

/**
 * Navigates directly to a target slide index.
 */
function goToGallerySlide(index) {
    const perView = getCardsPerView();
    const maxIdx = Math.max(0, gallerySlides.length - perView);
    currentGalleryIdx = Math.min(Math.max(0, index), maxIdx);
    updateGalleryCarousel();
    resetGalleryTimer();
}

/**
 * Starts automatic 4.5-second slide progression.
 */
function startGalleryTimer() {
    clearInterval(galleryTimer);
    galleryTimer = setInterval(() => nextGallerySlide(), 4500);
}

/**
 * Resets auto-slide timer upon user interaction.
 */
function resetGalleryTimer() {
    clearInterval(galleryTimer);
    startGalleryTimer();
}

/**
 * Configures touch gestures for swipe-to-slide on mobile devices.
 */
function setupGalleryTouch(element) {
    if (!element) return;
    let touchStartX = 0;
    let touchEndX = 0;

    element.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
        clearInterval(galleryTimer);
    }, { passive: true });

    element.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
        startGalleryTimer();
    }, { passive: true });

    function handleSwipe() {
        const diff = touchEndX - touchStartX;
        if (Math.abs(diff) > 45) {
            if (diff < 0) {
                nextGallerySlide();
            } else {
                prevGallerySlide();
            }
        }
    }
}

/**
 * Opens high-resolution image in full-screen Lightbox modal.
 */
function openGalleryLightbox(imgSrc, title) {
    const modal = document.getElementById('gallery-lightbox');
    const modalImg = document.getElementById('lightbox-image');
    const modalTitle = document.getElementById('lightbox-title');

    if (!modal || !modalImg) return;

    modalImg.src = imgSrc;
    if (modalTitle) modalTitle.textContent = title || 'Our Island Gallery';

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

/**
 * Closes the Lightbox modal.
 */
function closeGalleryLightbox() {
    const modal = document.getElementById('gallery-lightbox');
    if (!modal) return;

    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = 'auto';
}

// Close lightbox on Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeGalleryLightbox();
    }
});

/**
 * Fetches marquee gallery photos from the backend and duplicates them for infinite CSS scroll.
 * Maintained for backward compatibility.
 */
async function setupMarquee() {
    const track = document.getElementById('marquee-track');
    if (!track) return;
    
    try {
        const res = await fetch('actions/sliders_action.php?action=get_all');
        const data = await res.json();
        
        const marqueeImages = (data && data.success && Array.isArray(data.sliders)) 
            ? data.sliders.filter(s => s && s.type === 'marquee') 
            : [];
        const images = marqueeImages.length > 0 
            ? marqueeImages 
            : [{ image_url: 'assets/image/background.jpg' }];
        
        // Duplicate image array for seamless endless loop
        const allImages = [...images, ...images];
        track.innerHTML = allImages.map(slide => `
            <div class="marquee-item" onclick="router('rooms')">
                <img src="${slide.image_url}" alt="Gallery Image">
            </div>
        `).join('');
    } catch (err) {
        console.error('Marquee load failed:', err);
        const fallbackImages = Array(12).fill({ image_url: 'assets/image/background.jpg' });
        track.innerHTML = fallbackImages.map(slide => `
            <div class="marquee-item" onclick="router('rooms')">
                <img src="${slide.image_url}" alt="Gallery Image">
            </div>
        `).join('');
    }
}

// Global window bindings for inline HTML and cross-module access
window.initHeroCarousel = initHeroCarousel;
window.showSlide = showSlide;
window.nextSlide = nextSlide;
window.prevSlide = prevSlide;
window.goToSlide = goToSlide;
window.startSlideTimer = startSlideTimer;
window.resetSlideTimer = resetSlideTimer;

window.initGalleryCarousel = initGalleryCarousel;
window.nextGallerySlide = nextGallerySlide;
window.prevGallerySlide = prevGallerySlide;
window.goToGallerySlide = goToGallerySlide;
window.openGalleryLightbox = openGalleryLightbox;
window.closeGalleryLightbox = closeGalleryLightbox;

window.setupMarquee = setupMarquee;


