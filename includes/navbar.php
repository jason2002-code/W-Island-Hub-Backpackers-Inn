<!-- Navigation (User Side) -->
 
    <nav class="fixed w-full z-50 bg-white/80 backdrop-blur-lg border-b border-white/20 shadow-[0_4px_30px_rgba(0,0,0,0.03)] transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center gap-2 cursor-pointer group" onclick="router('home')">
                    <!-- SVG Logo Icon -->
                    <svg class="w-10 h-10 md:w-12 md:h-12 transition-transform duration-500 group-hover:scale-105 drop-shadow-md" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="goldGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#FDE047"/>
                                <stop offset="50%" stop-color="#D97706"/>
                                <stop offset="100%" stop-color="#78350F"/>
                            </linearGradient>
                        </defs>
                        <!-- Palm Trunk -->
                        <path d="M25,85 Q35,60 30,40" stroke="url(#goldGradient)" stroke-width="5" stroke-linecap="round"/>
                        <!-- Palm Leaves -->
                        <path d="M30,40 C15,40 10,55 10,55 C10,55 15,45 30,40" fill="url(#goldGradient)"/>
                        <path d="M30,40 C20,25 5,30 5,30 C5,30 15,35 30,40" fill="url(#goldGradient)"/>
                        <path d="M30,40 C30,20 20,15 20,15 C20,15 30,25 30,40" fill="url(#goldGradient)"/>
                        <path d="M30,40 C45,25 55,35 55,35 C55,35 40,35 30,40" fill="url(#goldGradient)"/>
                        <path d="M30,40 C50,45 50,60 50,60 C50,60 40,50 30,40" fill="url(#goldGradient)"/>
                        <!-- Stylized W -->
                        <path d="M40,35 L50,85 L65,50 L75,85 L90,35" stroke="url(#goldGradient)" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    
                    <!-- Typographic Text -->
                    <div class="flex flex-col justify-center">
                        <h1 class="font-serif text-xl md:text-2xl font-bold leading-none tracking-wide text-transparent bg-clip-text bg-gradient-to-r from-yellow-500 via-yellow-400 to-yellow-600 drop-shadow-sm pb-1">
                            W island
                        </h1>
                        <span class="text-[0.6rem] md:text-[0.65rem] font-bold tracking-[0.3em] uppercase text-gray-500 leading-none mt-0.5 ml-1">
                            Hub
                        </span>
                    </div>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex space-x-8 items-center">
                    <a onclick="router('home')" class="relative cursor-pointer text-gray-600 hover:text-brand-dark transition-colors font-semibold group text-sm tracking-wide">
                        Home
                        <span class="absolute left-0 -bottom-1.5 w-0 h-0.5 bg-brand-cyan transition-all duration-300 ease-in-out group-hover:w-full rounded-full"></span>
                    </a>
                    <a onclick="router('rooms')" class="relative cursor-pointer text-gray-600 hover:text-brand-dark transition-colors font-semibold group text-sm tracking-wide">
                        Rooms
                        <span class="absolute left-0 -bottom-1.5 w-0 h-0.5 bg-brand-cyan transition-all duration-300 ease-in-out group-hover:w-full rounded-full"></span>
                    </a>
                    <a onclick="router('discovery-bohol')" class="relative cursor-pointer text-gray-600 hover:text-brand-dark transition-colors font-semibold group text-sm tracking-wide flex items-center gap-1.5">
                        <i class="fas fa-compass text-amber-500 text-xs"></i>
                        <span>Discover Bohol</span>
                        <span class="absolute left-0 -bottom-1.5 w-0 h-0.5 bg-brand-cyan transition-all duration-300 ease-in-out group-hover:w-full rounded-full"></span>
                    </a>
                    <a onclick="router('about')" class="relative cursor-pointer text-gray-600 hover:text-brand-dark transition-colors font-semibold group text-sm tracking-wide">
                        About
                        <span class="absolute left-0 -bottom-1.5 w-0 h-0.5 bg-brand-cyan transition-all duration-300 ease-in-out group-hover:w-full rounded-full"></span>
                    </a>
                    <a onclick="router('contact')" class="relative cursor-pointer text-gray-600 hover:text-brand-dark transition-colors font-semibold group text-sm tracking-wide">
                        Contact
                        <span class="absolute left-0 -bottom-1.5 w-0 h-0.5 bg-brand-cyan transition-all duration-300 ease-in-out group-hover:w-full rounded-full"></span>
                    </a>
                    <a onclick="router('rules')" class="relative cursor-pointer text-gray-600 hover:text-brand-dark transition-colors font-semibold group text-sm tracking-wide">
                        Rules
                        <span class="absolute left-0 -bottom-1.5 w-0 h-0.5 bg-brand-cyan transition-all duration-300 ease-in-out group-hover:w-full rounded-full"></span>
                    </a>
                    
                    <div class="h-6 w-px bg-gray-200 mx-2"></div> <!-- Separator -->
                   
                    <div id="auth-buttons" class="flex items-center">
                        <!-- Injected via JS based on auth state -->
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-btn" class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-600 hover:text-brand-cyan hover:bg-gray-100 focus:outline-none transition-all shadow-sm">
                        <i class="fas fa-bars text-lg"></i>
                    </button>
                </div>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-white/95 backdrop-blur-xl border-t border-gray-100 absolute w-full left-0 top-20 shadow-2xl rounded-b-3xl overflow-hidden">
            <div class="px-4 py-4 flex flex-col space-y-1">
                <a onclick="router('home')" class="block py-3 px-6 text-gray-600 hover:text-brand-cyan hover:bg-brand-cyan/10 rounded-xl transition-all font-medium flex items-center gap-3">
                    <i class="fas fa-home w-5 text-center text-brand-cyan/70"></i> Home
                </a>
                <a onclick="router('rooms')" class="block py-3 px-6 text-gray-600 hover:text-brand-cyan hover:bg-brand-cyan/10 rounded-xl transition-all font-medium flex items-center gap-3">
                    <i class="fas fa-bed w-5 text-center text-brand-cyan/70"></i> Rooms
                </a>
                <a onclick="router('discovery-bohol')" class="block py-3 px-6 text-gray-600 hover:text-brand-cyan hover:bg-brand-cyan/10 rounded-xl transition-all font-medium flex items-center gap-3">
                    <i class="fas fa-compass w-5 text-center text-amber-500"></i> Discover Bohol
                </a>
                <a onclick="router('about')" class="block py-3 px-6 text-gray-600 hover:text-brand-cyan hover:bg-brand-cyan/10 rounded-xl transition-all font-medium flex items-center gap-3">
                    <i class="fas fa-info-circle w-5 text-center text-brand-cyan/70"></i> About
                </a>
                <a onclick="router('contact')" class="block py-3 px-6 text-gray-600 hover:text-brand-cyan hover:bg-brand-cyan/10 rounded-xl transition-all font-medium flex items-center gap-3">
                    <i class="fas fa-envelope w-5 text-center text-brand-cyan/70"></i> Contact
                </a>
                <a onclick="router('rules')" class="block py-3 px-6 text-gray-600 hover:text-brand-cyan hover:bg-brand-cyan/10 rounded-xl transition-all font-medium flex items-center gap-3">
                    <i class="fas fa-gavel w-5 text-center text-brand-cyan/70"></i> Rules
                </a>
            </div>
            <div id="mobile-auth-buttons" class="p-6 bg-gray-50/80 border-t border-gray-100 flex flex-col gap-3"></div>
        </div>
    </nav>
