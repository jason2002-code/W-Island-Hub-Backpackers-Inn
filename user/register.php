 <!-- ================= REGISTER PAGE ================= -->
<div id="register" class="page-section bg-[#e8e8e8]">
            <div class="min-h-screen flex items-center justify-center px-4 relative pb-20 pt-10">
                <div class="bg-white p-10 md:p-12 rounded-[40px] shadow-[0_30px_60px_rgba(0,0,0,0.08)] w-full max-w-md relative mt-10">
                    <h2 class="text-[2.5rem] font-black mb-10 text-center text-brand-cyan tracking-tight">Sign Up</h2>
                    
                    <form id="register-form" class="space-y-5">
                        <div>
                            <input type="text" id="reg-name" placeholder="Full Name" class="w-full bg-white border border-gray-100 rounded-full px-6 py-4 text-sm text-gray-700 outline-none shadow-[0_4px_15px_rgba(0,0,0,0.03)] focus:ring-2 focus:ring-brand-cyan/20 transition" required>
                        </div>
                        <div>
                            <input type="email" id="reg-email" placeholder="E-mail" class="w-full bg-white border border-gray-100 rounded-full px-6 py-4 text-sm text-gray-700 outline-none shadow-[0_4px_15px_rgba(0,0,0,0.03)] focus:ring-2 focus:ring-brand-cyan/20 transition" required>
                        </div>
                        <div class="password-wrapper">
                            <input type="password" id="reg-pass" placeholder="Password" class="w-full bg-white border border-gray-100 rounded-full px-6 py-4 pr-12 text-sm text-gray-700 outline-none shadow-[0_4px_15px_rgba(0,0,0,0.03)] focus:ring-2 focus:ring-brand-cyan/20 transition" required>
                            <i class="fas fa-eye password-toggle-icon" id="toggle-reg-pass" onclick="togglePassword('reg-pass', 'toggle-reg-pass')"></i>
                        </div>
                        
                        <button type="submit" class="w-full bg-brand-cyan text-white text-lg font-bold py-4 rounded-full shadow-[0_10px_25px_rgba(29,179,232,0.4)] hover:bg-[#189bc9] hover:shadow-[0_12px_25px_rgba(29,179,232,0.5)] transition transform hover:-translate-y-0.5 mt-4">Sign Up</button>
                    </form>

                    <div class="mt-8">
                        <p class="text-center text-xs text-gray-400 font-medium mb-6">Or Sign up with</p>
                        <div class="flex justify-center gap-5">
                            <button type="button" onclick="handleSocialLogin('Google')" class="w-12 h-12 bg-[#252525] text-white rounded-full flex items-center justify-center shadow-[0_8px_15px_rgba(0,0,0,0.15)] hover:-translate-y-1 transition text-lg"><i class="fab fa-google"></i></button>
                            <button type="button" onclick="handleSocialLogin('Apple')" class="w-12 h-12 bg-[#252525] text-white rounded-full flex items-center justify-center shadow-[0_8px_15px_rgba(0,0,0,0.15)] hover:-translate-y-1 transition text-xl"><i class="fab fa-apple"></i></button>
                            <button type="button" onclick="handleSocialLogin('X')" class="w-12 h-12 bg-[#252525] text-white rounded-full flex items-center justify-center shadow-[0_8px_15px_rgba(0,0,0,0.15)] hover:-translate-y-1 transition text-lg flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="w-4 h-4 fill-current"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.6 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Toggle to Login -->
                    <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                        <p class="text-xs text-gray-500 font-medium">
                            Already have an account? <a onclick="router('login')" class="text-brand-cyan cursor-pointer font-bold hover:underline">Sign In</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
