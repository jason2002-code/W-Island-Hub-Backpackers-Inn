<!-- ================= LOGIN PAGE ================= -->
<div id="login" class="page-section bg-gradient-to-b from-slate-900 via-slate-950 to-slate-900 text-slate-800 py-10 md:py-16 px-4 sm:px-6 lg:px-8 min-h-screen relative flex items-center justify-center overflow-hidden">
    
    <!-- Ambient Tropical Glow Elements -->
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-amber-500/5 via-transparent to-transparent pointer-events-none"></div>

    <div class="max-w-4xl w-full mx-auto relative z-10">
        
        <!-- Top Navigation Strip -->
        <div class="mb-6 flex items-center justify-between">
            <button type="button" onclick="router('home')" class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold text-slate-300 hover:text-amber-400 transition-colors cursor-pointer group bg-white/5 hover:bg-white/10 px-4 py-2 rounded-full border border-white/10 backdrop-blur-sm">
                <i class="fas fa-arrow-left text-xs group-hover:-translate-x-1 transition-transform"></i>
                <span>Back to Homepage &bull; Rooms</span>
            </button>
            <div class="hidden sm:inline-flex items-center gap-2 text-xs text-slate-400 bg-white/5 px-3 py-1.5 rounded-full border border-white/5">
                <i class="fas fa-shield-alt text-emerald-400"></i>
                <span>SSL Encrypted &bull; 100% Secure</span>
            </div>
        </div>

        <!-- Main Card Container: Split Layout on Desktop -->
        <div class="bg-white rounded-3xl sm:rounded-[32px] shadow-2xl overflow-hidden border border-slate-100 grid grid-cols-1 lg:grid-cols-12">
            
            <!-- Left Column: Tropical Brand Showcase -->
            <div class="lg:col-span-5 bg-gradient-to-br from-slate-950 via-slate-900 to-amber-950 p-8 sm:p-10 text-white flex flex-col justify-between relative overflow-hidden">
                <!-- Background Image Texture with Soft Overlay -->
                <img src="assets/image/background.jpg" alt="W Island Hub" class="absolute inset-0 w-full h-full object-cover opacity-20 mix-blend-overlay pointer-events-none">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/80 to-transparent pointer-events-none"></div>
                <div class="absolute -bottom-10 -right-10 w-44 h-44 bg-amber-500/20 rounded-full blur-2xl pointer-events-none"></div>

                <div class="relative z-10">
                    <!-- Brand Pill -->
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/15 text-amber-300 text-[11px] font-semibold tracking-wider uppercase mb-6 backdrop-blur-md">
                        <i class="fas fa-umbrella-beach text-amber-400"></i>
                        <span>Dauis, Bohol &bull; Premier Inn</span>
                    </div>

                    <!-- Brand Title -->
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 to-yellow-600 text-slate-950 font-serif font-bold flex items-center justify-center text-2xl shadow-lg flex-shrink-0">
                            W
                        </div>
                        <div>
                            <h3 class="font-serif font-bold text-xl sm:text-2xl text-white leading-tight">W Island Hub</h3>
                            <p class="text-xs text-amber-300 font-medium tracking-widest uppercase">Backpackers Inn</p>
                        </div>
                    </div>

                    <p class="text-slate-300 text-xs sm:text-sm font-light leading-relaxed mb-6">
                        Your tropical sanctuary in central Bohol. Where island warmth meets modern comfort.
                    </p>

                    <!-- Inn Feature Highlights -->
                    <div class="space-y-3 pt-2">
                        <div class="flex items-center gap-3 text-xs text-slate-200">
                            <div class="w-7 h-7 rounded-xl bg-amber-500/20 border border-amber-400/30 flex items-center justify-center text-amber-300 flex-shrink-0">
                                <i class="fas fa-tag text-[11px]"></i>
                            </div>
                            <span>Guaranteed best rates &amp; direct bookings</span>
                        </div>
                        <div class="flex items-center gap-3 text-xs text-slate-200">
                            <div class="w-7 h-7 rounded-xl bg-sky-500/20 border border-sky-400/30 flex items-center justify-center text-sky-300 flex-shrink-0">
                                <i class="fas fa-wifi text-[11px]"></i>
                            </div>
                            <span>Nomad-grade Starlink satellite WiFi</span>
                        </div>
                        <div class="flex items-center gap-3 text-xs text-slate-200">
                            <div class="w-7 h-7 rounded-xl bg-emerald-500/20 border border-emerald-400/30 flex items-center justify-center text-emerald-300 flex-shrink-0">
                                <i class="fas fa-key text-[11px]"></i>
                            </div>
                            <span>Keycard lockers &amp; 24/7 front desk security</span>
                        </div>
                        <div class="flex items-center gap-3 text-xs text-slate-200">
                            <div class="w-7 h-7 rounded-xl bg-teal-500/20 border border-teal-400/30 flex items-center justify-center text-teal-300 flex-shrink-0">
                                <i class="fas fa-soap text-[11px]"></i>
                            </div>
                            <span>On-site laundry hub &amp; pickleball court</span>
                        </div>
                    </div>
                </div>

                <!-- Verified Guest Quote Card -->
                <div class="relative z-10 mt-8 pt-6 border-t border-white/10">
                    <div class="bg-white/10 backdrop-blur-md p-3.5 rounded-2xl border border-white/10 text-xs">
                        <div class="flex items-center gap-1 text-amber-400 text-xs mb-1">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <span class="text-white font-bold ml-1">4.9 / 5</span>
                        </div>
                        <p class="text-slate-200 italic line-clamp-2">"Super clean rooms, fast WiFi, and warm vibes. Booking direct was effortless!"</p>
                        <p class="text-[10px] text-amber-300 mt-1 font-semibold">&bull; Verified Guest Stay</p>
                    </div>
                </div>
            </div>

            <!-- Right Column: Interactive Form -->
            <div class="lg:col-span-7 p-6 sm:p-8 md:p-10 flex flex-col justify-between">
                <div>
                    <!-- Segmented Tab Switcher (Sign In vs Sign Up) -->
                    <div class="flex items-center p-1 bg-slate-100 rounded-2xl mb-8 max-w-xs mx-auto sm:mx-0">
                        <button type="button" class="flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold bg-white text-slate-900 shadow-sm transition cursor-default">
                            Sign In
                        </button>
                        <button type="button" onclick="router('register')" class="flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-semibold text-slate-500 hover:text-slate-900 transition cursor-pointer">
                            Sign Up
                        </button>
                    </div>

                    <!-- Header -->
                    <div class="mb-6">
                        <h2 class="text-2xl sm:text-3xl font-serif font-bold text-slate-900 tracking-tight">
                            Welcome Back
                        </h2>
                        <p class="text-slate-500 text-xs sm:text-sm mt-1">
                            Sign in to access your room reservations and special guest rates.
                        </p>
                    </div>

                    <!-- Social Login (Prominent Google) -->
                    <div class="space-y-3 mb-6">
                        <button type="button" onclick="handleSocialLogin('Google')" class="w-full flex items-center justify-center gap-3 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-semibold py-3.5 px-4 rounded-2xl text-sm transition-all duration-200 shadow-2xs hover:shadow-xs cursor-pointer group">
                            <!-- Official Google Icon SVG -->
                            <svg class="w-5 h-5 flex-shrink-0 group-hover:scale-105 transition-transform" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"/>
                            </svg>
                            <span>Continue with Google</span>
                        </button>
                    </div>

                    <!-- Divider -->
                    <div class="relative flex items-center justify-center my-6">
                        <div class="border-t border-slate-200 w-full"></div>
                        <span class="bg-white px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400 absolute">or sign in with email</span>
                    </div>

                    <!-- Form -->
                    <form id="login-form" class="space-y-4">
                        <!-- Email Input Group -->
                        <div>
                            <label for="login-email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5 flex items-center gap-1.5">
                                <i class="fas fa-envelope text-amber-500"></i>
                                <span>Email Address</span>
                                <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="email" id="login-email" name="email" autocomplete="username" placeholder="name@example.com" required
                                       class="w-full bg-slate-50/80 hover:bg-slate-50 focus:bg-white border border-slate-200 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 rounded-2xl px-4 py-3.5 text-sm text-slate-800 font-medium outline-none transition shadow-2xs">
                            </div>
                        </div>

                        <!-- Password Input Group -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="login-password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                                    <i class="fas fa-lock text-amber-500"></i>
                                    <span>Password</span>
                                    <span class="text-rose-500">*</span>
                                </label>
                                <a onclick="router('forgot-password')" class="text-amber-600 hover:text-amber-700 text-xs font-semibold hover:underline cursor-pointer transition">
                                    Forgot password?
                                </a>
                            </div>
                            <div class="relative">
                                <input type="password" id="login-password" name="password" autocomplete="current-password" placeholder="Enter your password" required
                                       class="w-full bg-slate-50/80 hover:bg-slate-50 focus:bg-white border border-slate-200 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 rounded-2xl pl-4 pr-12 py-3.5 text-sm text-slate-800 font-medium outline-none transition shadow-2xs">
                                <button type="button" onclick="togglePassword('login-password', 'toggle-login-password')" aria-label="Toggle password visibility" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer transition">
                                    <i class="fas fa-eye text-sm" id="toggle-login-password"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-2">
                            <button type="submit" class="w-full bg-gradient-to-r from-amber-500 via-amber-600 to-yellow-500 hover:from-amber-600 hover:to-yellow-600 text-slate-950 font-bold py-3.5 px-6 rounded-2xl shadow-lg hover:shadow-xl hover:shadow-amber-500/25 transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2 cursor-pointer text-sm sm:text-base">
                                <span>Sign In</span>
                                <i class="fas fa-arrow-right text-xs"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Footer / Register Toggle -->
                <div class="mt-8 pt-6 border-t border-slate-100 text-center">
                    <p class="text-xs sm:text-sm text-slate-500">
                        Don't have an account yet? 
                        <a onclick="router('register')" class="text-amber-600 hover:text-amber-700 font-bold hover:underline cursor-pointer ml-1">Create an account</a>
                    </p>
                </div>
            </div>

        </div>

    </div>
</div>

<!-- ================= FORGOT PASSWORD PAGE ================= -->
<div id="forgot-password" class="page-section bg-gradient-to-b from-slate-900 via-slate-950 to-slate-900 text-slate-800 py-10 md:py-16 px-4 sm:px-6 lg:px-8 min-h-screen relative flex items-center justify-center overflow-hidden">
    
    <!-- Ambient Tropical Glow Elements -->
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-md w-full mx-auto relative z-10">
        
        <!-- Back Navigation -->
        <div class="mb-6 flex items-center justify-start">
            <button type="button" onclick="router('login')" class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold text-slate-300 hover:text-amber-400 transition-colors cursor-pointer group bg-white/5 hover:bg-white/10 px-4 py-2 rounded-full border border-white/10 backdrop-blur-sm">
                <i class="fas fa-arrow-left text-xs group-hover:-translate-x-1 transition-transform"></i>
                <span>Back to Sign In</span>
            </button>
        </div>

        <div class="bg-white p-8 sm:p-10 rounded-3xl sm:rounded-[32px] shadow-2xl border border-slate-100 text-center">
            
            <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl mx-auto mb-5 shadow-inner">
                <i class="fas fa-key"></i>
            </div>

            <h2 class="text-2xl sm:text-3xl font-serif font-bold text-slate-900 mb-2 tracking-tight">
                Reset Password
            </h2>
            <p class="text-slate-500 text-xs sm:text-sm leading-relaxed mb-6">
                Enter your registered email address below and we'll send you instructions to regain access to your account.
            </p>

            <form id="forgot-password-form" class="space-y-4 text-left">
                <div>
                    <label for="forgot-email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5 flex items-center gap-1.5">
                        <i class="fas fa-envelope text-amber-500"></i>
                        <span>Email Address</span>
                        <span class="text-rose-500">*</span>
                    </label>
                    <input type="email" id="forgot-email" name="email" placeholder="name@example.com" required
                           class="w-full bg-slate-50/80 hover:bg-slate-50 focus:bg-white border border-slate-200 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 rounded-2xl px-4 py-3.5 text-sm text-slate-800 font-medium outline-none transition shadow-2xs">
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-amber-500 via-amber-600 to-yellow-500 hover:from-amber-600 hover:to-yellow-600 text-slate-950 font-bold py-3.5 px-6 rounded-2xl shadow-lg hover:shadow-xl hover:shadow-amber-500/25 transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2 cursor-pointer text-sm sm:text-base mt-2">
                    <span>Send Reset Link</span>
                    <i class="fas fa-paper-plane text-xs"></i>
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-slate-100">
                <p class="text-xs text-slate-500">
                    Remembered your password? 
                    <a onclick="router('login')" class="text-amber-600 hover:text-amber-700 font-bold hover:underline cursor-pointer ml-1">Sign In</a>
                </p>
            </div>
        </div>
    </div>
</div>


