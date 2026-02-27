 <!-- Navigation (User Side) -->
 
    <nav class="fixed w-full z-50 bg-white/90 backdrop-blur-md shadow-sm transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex-shrink-0 cursor-pointer" onclick="router('home')">
                    <h1 class="font-serif text-2xl font-bold text-brand-dark"><span class="text-brand-blue">W</span> Island Hub</h1>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex space-x-8 items-center">
                    <a onclick="router('home')" class="cursor-pointer hover:text-brand-blue transition font-medium">Home</a>
                    <a onclick="router('rooms')" class="cursor-pointer hover:text-brand-blue transition font-medium">Rooms</a>
                    <a onclick="router('about')" class="cursor-pointer hover:text-brand-blue transition font-medium">About</a>
                    <a onclick="router('contact')" class="cursor-pointer hover:text-brand-blue transition font-medium">Contact</a>
                    
                    <div id="auth-buttons" class="flex items-center">
                        <!-- Injected via JS based on auth state -->
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-btn" class="text-gray-700 hover:text-brand-blue focus:outline-none p-2">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-white border-t absolute w-full left-0 top-20 shadow-lg">
            <a onclick="router('home')" class="block py-3 px-6 hover:bg-gray-50 border-b border-gray-100">Home</a>
            <a onclick="router('rooms')" class="block py-3 px-6 hover:bg-gray-50 border-b border-gray-100">Rooms</a>
            <a onclick="router('about')" class="block py-3 px-6 hover:bg-gray-50 border-b border-gray-100">About</a>
            <a onclick="router('contact')" class="block py-3 px-6 hover:bg-gray-50 border-b border-gray-100">Contact</a>
            <div id="mobile-auth-buttons" class="p-4 bg-gray-50"></div>
        </div>
    </nav>