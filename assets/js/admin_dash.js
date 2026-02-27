 // --- 1. DASHBOARD OVERVIEW ---
        function renderAdminStats() {
            document.getElementById('admin-page-title').innerText = "Dashboard Overview";
            const revenue = appState.bookings.filter(b => b.status !== 'Cancelled').reduce((sum, b) => sum + b.total, 0);
            
            document.getElementById('admin-content').innerHTML = `
                <!-- Top Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="dash-card bg-white p-6 rounded-2xl shadow-sm border border-gray-50 flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-400 font-medium">Total Revenue</p>
                            <h3 class="text-2xl font-bold text-brand-dark mt-1">$${revenue.toLocaleString()}</h3>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-blue-50 text-brand-blue flex items-center justify-center text-xl"><i class="fas fa-dollar-sign"></i></div>
                    </div>
                    <div class="dash-card bg-white p-6 rounded-2xl shadow-sm border border-gray-50 flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-400 font-medium">Total Bookings</p>
                            <h3 class="text-2xl font-bold text-brand-dark mt-1">${appState.bookings.length}</h3>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-purple-50 text-brand-purple flex items-center justify-center text-xl"><i class="fas fa-bookmark"></i></div>
                    </div>
                    <div class="dash-card bg-white p-6 rounded-2xl shadow-sm border border-gray-50 flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-400 font-medium">Total Users</p>
                            <h3 class="text-2xl font-bold text-brand-dark mt-1">${appState.users.length}</h3>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-green-50 text-green-500 flex items-center justify-center text-xl"><i class="fas fa-users"></i></div>
                    </div>
                    <div class="dash-card bg-white p-6 rounded-2xl shadow-sm border border-gray-50 flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-400 font-medium">Feedback</p>
                            <h3 class="text-2xl font-bold text-brand-dark mt-1">${appState.reviews.length}</h3>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-yellow-50 text-yellow-500 flex items-center justify-center text-xl"><i class="fas fa-star"></i></div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-50">
                        <h4 class="font-bold text-gray-700 mb-4">Revenue Analytics</h4>
                        <div class="h-64">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-50">
                        <h4 class="font-bold text-gray-700 mb-4">Active Bookings Status</h4>
                        <div class="h-52 flex justify-center">
                            <canvas id="roomStatusChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-50 overflow-hidden">
                    <div class="p-6 border-b border-gray-50 flex justify-between items-center">
                        <h4 class="font-bold text-gray-700">Recent Bookings</h4>
                        <button onclick="renderAdminBookings()" class="text-sm text-brand-purple hover:underline">View All</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50/50 text-xs text-gray-400 uppercase tracking-wider">
                                <tr>
                                    <th class="p-4 font-medium">Guest</th>
                                    <th class="p-4 font-medium">Room</th>
                                    <th class="p-4 font-medium">Amount</th>
                                    <th class="p-4 font-medium">Status</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm divide-y divide-gray-50">
                                ${appState.bookings.slice(-5).reverse().map(b => `
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="p-4 flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-600">${b.guestName.charAt(0)}</div>
                                            <span class="font-medium text-gray-700">${b.guestName}</span>
                                        </td>
                                        <td class="p-4 text-gray-500">${b.roomName}</td>
                                        <td class="p-4 font-bold text-gray-700">$${b.total}</td>
                                        <td class="p-4"><span class="px-3 py-1 rounded-full text-xs font-medium ${b.status === 'Confirmed' ? 'bg-green-100 text-green-600' : (b.status === 'Cancelled' ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-600')}">${b.status}</span></td>
                                    </tr>
                                `).join('')}
                                ${appState.bookings.length === 0 ? '<tr><td colspan="4" class="p-8 text-center text-gray-400">No bookings yet.</td></tr>' : ''}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;

            initCharts();
        }