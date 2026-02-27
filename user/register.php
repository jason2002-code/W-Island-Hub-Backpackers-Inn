 <!-- ================= REGISTER PAGE ================= -->
 <div id="register" class="page-section">
            <div class="min-h-[80vh] flex items-center justify-center bg-slate-100 px-4">
                <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md">
                    <h2 class="text-2xl font-bold mb-6 text-center text-brand-dark">Register</h2>
                    <form id="register-form" class="space-y-4">
                        <div>
                            <label class="block text-sm text-gray-600">Full Name</label>
                            <input type="text" id="reg-name" class="w-full border rounded-lg p-3" required>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600">Email Address</label>
                            <input type="email" id="reg-email" class="w-full border rounded-lg p-3" required>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600">Password</label>
                            <input type="password" id="reg-pass" class="w-full border rounded-lg p-3" required>
                            <div id="password-requirements" class="mt-2 text-xs text-gray-500">
                                <p>Password must contain:</p>
                                <ul class="list-disc list-inside">
                                    <li id="req-length">At least 8 characters</li>
                                    <li id="req-uppercase">At least one uppercase letter</li>
                                    <li id="req-lowercase">At least one lowercase letter</li>
                                    <li id="req-number">At least one number</li>
                                    <li id="req-special">At least one special character</li>
                                </ul>
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-brand-blue text-white font-bold py-3 rounded-lg hover:bg-sky-600">Create Account</button>
                    </form>
                    <p class="mt-4 text-center text-sm text-gray-600">
                        Already have an account? <a onclick="router('login')" class="text-brand-blue cursor-pointer font-bold">Login</a>
                    </p>
                </div>
            </div>
        </div>