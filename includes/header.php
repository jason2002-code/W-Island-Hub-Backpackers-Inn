<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>W Island Hub Backpackers Inn</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- GSAP for Animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Playfair+Display:wght@700&family=Great+Vibes&family=Montserrat:wght@300;500&display=swap" rel="stylesheet">


    <script>
         tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            blue: '#0ea5e9',
                            sand: '#fef3c7',
                            dark: '#0f172a',
                            green: '#22c55e',
                            accent: '#f59e0b',
                            purple: '#6C5DD3',
                            cyan: '#1db3e8' /* Added for the new login design */
                        }
                    },
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    }
                }
            }
        }
    </script>

    <style>
       @import url('assets/css/Gsap.css');
      /* @import url('assets/css/adcss.css');*/
    </style>
</head>
<body class="bg-slate-50 text-slate-800">

    <!-- New Gold Island Hub Loader Screen -->
    <div id="loader">
        <!-- SVG Definitions for Gold Gradient -->
        <svg width="0" height="0" style="position:absolute;">
            <defs>
                <linearGradient id="gold-gradient" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#FDF5D3" />
                    <stop offset="35%" stop-color="#D4AF37" />
                    <stop offset="65%" stop-color="#8B6508" />
                    <stop offset="100%" stop-color="#D4AF37" />
                </linearGradient>
            </defs>
        </svg>

        <!-- Loader Overlay Styles -->
        <style>
            :root {
                --gold-light: #FDF5D3;
                --gold-mid: #D4AF37;
                --gold-dark: #8B6508;
                --bg-center: #3A2311;
                --bg-edge: #110A05;
            }

            #loader {
                position: fixed !important;
                top: 0;
                left: 0;
                width: 100vw !important;
                height: 100vh !important;
                z-index: 9999 !important;
                display: flex !important;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                background: radial-gradient(circle at center, var(--bg-center) 0%, var(--bg-edge) 80%) !important;
                transition: opacity 0.8s ease-out, visibility 0.8s ease-out !important;
            }

            /* Leather/Noise Texture Overlay */
            #loader::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                opacity: 0.15;
                pointer-events: none;
                background-image: url('data:image/svg+xml;utf8,<svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg"><filter id="noiseFilter"><feTurbulence type="fractalNoise" baseFrequency="0.85" numOctaves="3" stitchTiles="stitch"/></filter><rect width="100%25" height="100%25" filter="url(%23noiseFilter)"/></svg>');
                mix-blend-mode: overlay;
            }

            /* Logo Container Layout */
            .logo-container {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 15px;
                position: relative;
                z-index: 2;
                animation: float 4s ease-in-out infinite;
            }

            /* The Gold Text */
            .brand-text {
                display: flex;
                flex-direction: column;
                font-family: 'Great Vibes', cursive;
                line-height: 0.7;
                background: linear-gradient(to bottom, var(--gold-light) 0%, var(--gold-mid) 40%, var(--gold-dark) 60%, var(--gold-mid) 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                color: transparent;
                filter: drop-shadow(2px 4px 4px rgba(0,0,0,0.8)) drop-shadow(0px 0px 15px rgba(212, 175, 55, 0.3));
            }

            .top-row {
                display: flex;
                align-items: baseline;
                gap: 5px;
            }

            .text-island {
                font-size: 5rem;
            }

            .text-hub {
                font-size: 6.5rem;
                padding-left: 80px;
                margin-top: -10px;
            }

            /* Loader Gold Brand Logo SVG */
            .loader-logo-svg {
                width: 100px;
                height: 100px;
                filter: drop-shadow(2px 4px 4px rgba(0,0,0,0.8)) drop-shadow(0px 0px 15px rgba(212, 175, 55, 0.3));
            }

            @media (max-width: 640px) {
                .loader-logo-svg {
                    width: 70px;
                    height: 70px;
                }
                .text-island {
                    font-size: 3.5rem;
                }
                .text-hub {
                    font-size: 4.5rem;
                    padding-left: 50px;
                }
            }

            /* Loading Progress Bar */
            .loading-status {
                position: absolute;
                bottom: 15%;
                display: flex;
                flex-direction: column;
                align-items: center;
                z-index: 2;
            }

            .progress-bar-container {
                width: 200px;
                height: 3px;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 10px;
                overflow: hidden;
                margin-bottom: 10px;
                box-shadow: inset 0 1px 3px rgba(0,0,0,0.5);
            }

            .progress-bar {
                height: 100%;
                width: 0%;
                background: linear-gradient(90deg, var(--gold-dark), var(--gold-light), var(--gold-mid));
                border-radius: 10px;
                box-shadow: 0 0 10px var(--gold-mid);
                transition: width 0.1s linear;
            }

            .loading-text {
                color: var(--gold-mid);
                font-size: 0.8rem;
                letter-spacing: 3px;
                text-transform: uppercase;
                animation: pulse-opacity 1.5s infinite;
                font-family: 'Montserrat', sans-serif;
            }

            /* Keyframe Animations */
            @keyframes float {
                0% { transform: translateY(0px); }
                50% { transform: translateY(-10px); }
                100% { transform: translateY(0px); }
            }

            @keyframes pulse-opacity {
                0%, 100% { opacity: 0.5; }
                50% { opacity: 1; }
            }

            /* When loader is hidden */
            .loader-hidden {
                opacity: 0 !important;
                visibility: hidden !important;
                pointer-events: none !important;
            }

            /* Scoped font imports (inline equivalent) */
            @import url('https://fonts.googleapis.com/css2?family=Great+Vibes&family=Montserrat:wght@300;500&display=swap');
        </style>

        <!-- Main Loader Content -->
        <div class="logo-container">
            <!-- Official Unified Gold Brand Logo Icon (Palm Tree & Stylized W) -->
            <svg class="loader-logo-svg" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Palm Trunk -->
                <path d="M25,85 Q35,60 30,40" stroke="url(#gold-gradient)" stroke-width="5" stroke-linecap="round"/>
                <!-- Palm Leaves -->
                <path d="M30,40 C15,40 10,55 10,55 C10,55 15,45 30,40" fill="url(#gold-gradient)"/>
                <path d="M30,40 C20,25 5,30 5,30 C5,30 15,35 30,40" fill="url(#gold-gradient)"/>
                <path d="M30,40 C30,20 20,15 20,15 C20,15 30,25 30,40" fill="url(#gold-gradient)"/>
                <path d="M30,40 C45,25 55,35 55,35 C55,35 40,35 30,40" fill="url(#gold-gradient)"/>
                <path d="M30,40 C50,45 50,60 50,60 C50,60 40,50 30,40" fill="url(#gold-gradient)"/>
                <!-- Stylized W -->
                <path d="M40,35 L50,85 L65,50 L75,85 L90,35" stroke="url(#gold-gradient)" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>

            <!-- Brand Typography -->
            <div class="brand-text">
                <div class="top-row">
                    <span class="text-island">island</span>
                </div>
                <div class="text-hub">Hub</div>
            </div>
        </div>

        <!-- Progress Indicator -->
        <div class="loading-status">
            <div class="progress-bar-container">
                <div class="progress-bar" id="progress-bar"></div>
            </div>
            <div class="loading-text">Loading Experience...</div>
        </div>

        <!-- Loader Script -->
        <script>
            (function() {
                const progressBar = document.getElementById('progress-bar');
                const loader = document.getElementById('loader');
                
                let progress = 0;
                const loadingInterval = setInterval(() => {
                    progress += Math.floor(Math.random() * 15) + 5;
                    
                    if (progress >= 100) {
                        progress = 100;
                        clearInterval(loadingInterval);
                        
                        // Fade out loader (compatible with main JS)
                        setTimeout(() => {
                            if(loader) {
                                loader.classList.add('loader-hidden');
                                document.body.style.overflow = 'auto';
                            }
                        }, 500);
                    }
                    
                    if(progressBar) {
                        progressBar.style.width = `${progress}%`;
                    }
                }, 250);
            })();
        </script>
    </div>
