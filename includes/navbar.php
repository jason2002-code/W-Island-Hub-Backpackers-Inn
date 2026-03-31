<!-- Navigation (User Side) -->
 
    <nav class="fixed w-full z-50 bg-white/80 backdrop-blur-lg border-b border-white/20 shadow-[0_4px_30px_rgba(0,0,0,0.03)] transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex-shrink-0 cursor-pointer flex items-center gap-3 group" onclick="router('home')">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-blue to-brand-cyan flex items-center justify-center text-white text-xl font-serif font-bold shadow-md group-hover:shadow-lg group-hover:-translate-y-0.5 transition-all duration-300">W</div>
                    <h1 class="font-serif text-2xl font-bold text-brand-dark tracking-tight group-hover:text-brand-cyan transition-colors duration-300">Island Hub</h1>
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
