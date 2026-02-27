<!-- ================= Login Page ================= -->
<div id="login" class="page-section">
            <div class="min-h-[80vh] flex items-center justify-center bg-slate-100 px-4">
                <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md">
                    <h2 class="text-2xl font-bold mb-6 text-center text-brand-dark">Login</h2>
                    <form id="login-form" class="space-y-4">
                        <div>
                            <label class="block text-sm text-gray-600">Email Address</label>
                            <input type="email" id="login-email" class="w-full border rounded-lg p-3" required>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600">Password</label>
                            <input type="password" id="login-password" class="w-full border rounded-lg p-3" required>
                        </div>
                        <button type="submit" class="w-full bg-brand-blue text-white font-bold py-3 rounded-lg hover:bg-sky-600">Sign In</button>
                    </form>
                    <p class="mt-4 text-center text-sm text-gray-600">
                        Don't have an account? <a onclick="router('register')" class="text-brand-blue cursor-pointer font-bold">Register</a>
                    </p>
                    <div class="mt-4 p-3 bg-blue-50 text-xs rounded text-blue-800">
                        <p><strong>Admin:</strong> admin@wisland.com / admin123</p>
                        <p><strong>User:</strong> guest@wisland.com / guest123</p>
                    </div>
                </div>
            </div>
        </div>


        
