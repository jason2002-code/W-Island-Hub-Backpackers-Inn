<!-- Footer -->
<footer class="bg-slate-950 text-white pt-16 pb-12 mt-auto border-t border-slate-800" id="main-footer">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">
            
            <!-- Column 1: Brand & Bio -->
            <div>
                <div class="flex items-center gap-2 mb-4 cursor-pointer" onclick="router('home')">
                    <h2 class="font-serif text-2xl font-bold leading-none tracking-wide text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 via-amber-300 to-yellow-500">
                        W island Hub
                    </h2>
                </div>
                <p class="text-slate-400 text-sm leading-relaxed mb-6">
                    A tropical sanctuary where island adventure meets modern boutique hospitality. Designed for global backpackers, couples, and digital nomads.
                </p>
                <div class="flex items-center space-x-3 text-slate-400">
                    <a href="#" class="w-9 h-9 rounded-full bg-white/10 hover:bg-amber-500 hover:text-slate-950 flex items-center justify-center transition-all">
                        <i class="fab fa-facebook-f text-sm"></i>
                    </a>
                    <a href="#" class="w-9 h-9 rounded-full bg-white/10 hover:bg-amber-500 hover:text-slate-950 flex items-center justify-center transition-all">
                        <i class="fab fa-instagram text-sm"></i>
                    </a>
                    <a href="#" class="w-9 h-9 rounded-full bg-white/10 hover:bg-amber-500 hover:text-slate-950 flex items-center justify-center transition-all">
                        <i class="fab fa-tripadvisor text-sm"></i>
                    </a>
                    <a href="#" class="w-9 h-9 rounded-full bg-white/10 hover:bg-amber-500 hover:text-slate-950 flex items-center justify-center transition-all">
                        <i class="fab fa-whatsapp text-sm"></i>
                    </a>
                </div>
            </div>

            <!-- Column 2: Quick Links -->
            <div>
                <h3 class="font-bold mb-4 text-sm uppercase tracking-wider text-amber-400">Explore Stay</h3>
                <ul class="space-y-2.5 text-slate-400 text-sm">
                    <li><a onclick="router('home')" class="cursor-pointer hover:text-amber-400 transition-colors flex items-center gap-2"><i class="fas fa-chevron-right text-[10px] text-slate-600"></i> Home</a></li>
                    <li><a onclick="router('rooms')" class="cursor-pointer hover:text-amber-400 transition-colors flex items-center gap-2"><i class="fas fa-chevron-right text-[10px] text-slate-600"></i> Accommodations</a></li>
                    <li><a onclick="router('about')" class="cursor-pointer hover:text-amber-400 transition-colors flex items-center gap-2"><i class="fas fa-chevron-right text-[10px] text-slate-600"></i> About Our Inn</a></li>
                    <li><a onclick="router('rules')" class="cursor-pointer hover:text-amber-400 transition-colors flex items-center gap-2"><i class="fas fa-chevron-right text-[10px] text-slate-600"></i> House Rules & Policies</a></li>
                </ul>
            </div>

            <!-- Column 3: Guest Support -->
            <div>
                <h3 class="font-bold mb-4 text-sm uppercase tracking-wider text-amber-400">Guest Services</h3>
                <ul class="space-y-2.5 text-slate-400 text-sm">
                    <li><a onclick="router('contact')" class="cursor-pointer hover:text-amber-400 transition-colors flex items-center gap-2"><i class="fas fa-chevron-right text-[10px] text-slate-600"></i> Contact & Inquiries</a></li>
                    <li><a onclick="router('rooms')" class="cursor-pointer hover:text-amber-400 transition-colors flex items-center gap-2"><i class="fas fa-chevron-right text-[10px] text-slate-600"></i> Check Availability</a></li>
                    <li><a onclick="router('login')" class="cursor-pointer hover:text-amber-400 transition-colors flex items-center gap-2"><i class="fas fa-chevron-right text-[10px] text-slate-600"></i> Guest Portal Login</a></li>
                    <li><a onclick="router('register')" class="cursor-pointer hover:text-amber-400 transition-colors flex items-center gap-2"><i class="fas fa-chevron-right text-[10px] text-slate-600"></i> Create Guest Account</a></li>
                </ul>
            </div>

            <!-- Column 4: Contact & Front Desk -->
            <div>
                <h3 class="font-bold mb-4 text-sm uppercase tracking-wider text-amber-400">Front Desk & Location</h3>
                <ul class="space-y-3 text-slate-400 text-sm">
                    <li class="flex items-start gap-3">
                        <i class="fas fa-map-marker-alt text-amber-400 mt-1 flex-shrink-0"></i>
                        <span>Panglao Island Road, Bohol, Philippines 6340</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <i class="fas fa-phone-alt text-amber-400 flex-shrink-0"></i>
                        <span>+63 995 068 7551 / 0975-361-4030</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <i class="fas fa-envelope text-amber-400 flex-shrink-0"></i>
                        <span>reservations@wislandhub.ph</span>
                    </li>
                    <li class="flex items-center gap-3 text-xs text-slate-500 pt-2 border-t border-slate-800">
                        <i class="fas fa-clock text-teal-400 flex-shrink-0"></i>
                        <span>Front Desk: 24/7 Service</span>
                    </li>
                </ul>
            </div>

        </div>

        <!-- Bottom Copyright -->
        <div class="pt-8 border-t border-slate-800/80 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
            <div>
                &copy; <?php echo date('Y'); ?> W Island Hub Backpackers Inn. All rights reserved.
            </div>
            <div class="flex items-center gap-6">
                <span>Verified GCash & Pay-at-Hotel</span>
                <span>Starlink Powered</span>
                <span>Panglao, Bohol</span>
            </div>
        </div>
    </div>
</footer>
