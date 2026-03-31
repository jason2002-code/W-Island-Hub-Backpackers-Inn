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

                    <!-- Toggle to Login -->
                    <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                        <p class="text-xs text-gray-500 font-medium">
                            Already have an account? <a onclick="router('login')" class="text-brand-cyan cursor-pointer font-bold hover:underline">Sign In</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
