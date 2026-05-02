  // --- DATA & STATE MANAGEMENT ---
        // Reads from the exact same localStorage keys as index.html
        let appState = {
            users: JSON.parse(localStorage.getItem('w_users')) || [],
            bookings: JSON.parse(localStorage.getItem('w_bookings')) || [],
            rooms: JSON.parse(localStorage.getItem('w_rooms')) || [],
            reports: JSON.parse(localStorage.getItem('w_reports')) || [],
            currentUser: JSON.parse(sessionStorage.getItem('w_adminUser')) || null
        };

        const saveState = () => {
            localStorage.setItem('w_users', JSON.stringify(appState.users));
            localStorage.setItem('w_bookings', JSON.stringify(appState.bookings));
            localStorage.setItem('w_rooms', JSON.stringify(appState.rooms));
            localStorage.setItem('w_reports', JSON.stringify(appState.reports));
            if(appState.currentUser) sessionStorage.setItem('w_adminUser', JSON.stringify(appState.currentUser));
            else sessionStorage.removeItem('w_adminUser');
        };

        let revenueChart = null;
        let roomStatusChart = null;

        // --- INIT & AUTH ---
        function initAdmin() {
            // Load data from database
            loadDashboardData();
        }

        // Load all data from database
        async function loadDashboardData() {
            try {
                // Create timeout controller
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 5000); // 5 second timeout
                
                // Load rooms
                try {
                    const roomsResponse = await fetch('../actions/rooms_action.php', { signal: controller.signal });
                    const roomsData = await roomsResponse.json();
                    if (roomsData.success) {
                        appState.rooms = roomsData.rooms;
                    }
                } catch (e) { console.warn('Rooms API failed:', e); }
                
                // Load bookings
                try {
                    const bookingsResponse = await fetch('../actions/bookings_action.php', { signal: controller.signal });
                    const bookingsData = await bookingsResponse.json();
                    if (bookingsData.success) {
                        appState.bookings = bookingsData.bookings;
                    }
                } catch (e) { console.warn('Bookings API failed:', e); }
                
                // Load users
                try {
                    const usersResponse = await fetch('../actions/users_action.php', { signal: controller.signal });
                    const usersData = await usersResponse.json();
                    if (usersData.success) {
                        appState.users = usersData.users;
                    }
                } catch (e) { console.warn('Users API failed:', e); }
                
                // Load reports
                try {
                    const reportsResponse = await fetch('../actions/reports_action.php', { signal: controller.signal });
                    const reportsData = await reportsResponse.json();
                    if (reportsData.success && reportsData.reports) {
                        appState.reports = reportsData.reports;
                    }
                } catch (e) { console.warn('Reports API failed:', e); }
                
                clearTimeout(timeoutId);
                renderAdminStats();
                
            } catch (error) {
                console.error('Error loading data:', error);
                // Fallback to localStorage if API fails
                appState.users = JSON.parse(localStorage.getItem('w_users')) || [];
                appState.bookings = JSON.parse(localStorage.getItem('w_bookings')) || [];
                appState.rooms = JSON.parse(localStorage.getItem('w_rooms')) || [];
                appState.reports = JSON.parse(localStorage.getItem('w_reports')) || [];
                renderAdminStats();
            }
        }

        function logout() {
            appState.currentUser = null;
            sessionStorage.removeItem('w_adminUser');
            localStorage.removeItem('w_adminUser');
            sessionStorage.removeItem('w_currentUser');
            localStorage.removeItem('w_currentUser');
            // Destroy PHP session and redirect to main site
            window.location.href = '../logout.php?type=admin';
        }

        // --- UI UTILS ---
        function toggleAdminSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            const overlay = document.getElementById('admin-sidebar-overlay');
            if(sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }

        function setActiveAdminLink(element) {
            document.querySelectorAll('.admin-link').forEach(link => {
                link.classList.remove('bg-blue-50', 'text-brand-purple', 'font-medium');
                link.classList.add('text-gray-600');
            });
            element.classList.add('bg-blue-50', 'text-brand-purple', 'font-medium');
            element.classList.remove('text-gray-600');
            
            // Close sidebar on mobile after clicking
            if(window.innerWidth < 768) {
                toggleAdminSidebar();
            }
        }

       

        function initCharts() {
            if(revenueChart) revenueChart.destroy();
            if(roomStatusChart) roomStatusChart.destroy();

            // --- 1. Revenue Chart (Line) ---
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            
            // Generate labels for the last 6 months
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const today = new Date();
            const chartLabels = [];
            const revenueData = [];

            for (let i = 5; i >= 0; i--) {
                const d = new Date(today.getFullYear(), today.getMonth() - i, 1);
                chartLabels.push(monthNames[d.getMonth()]);
                
                // Filter confirmed bookings for that month and year
                const monthlyRevenue = appState.bookings
                    .filter(b => {
                        const bookingDate = new Date(b.checkIn); // Or b.created_at depending on logic
                        return b.status === 'Confirmed' &&
                            bookingDate.getMonth() === d.getMonth() &&
                            bookingDate.getFullYear() === d.getFullYear();
                    })
                    .reduce((sum, b) => sum + (parseFloat(b.total) || 0), 0);
                    
                revenueData.push(monthlyRevenue);
            }

            revenueChart = new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Revenue (₱)',
                        data: revenueData, // Use dynamic data
                        borderColor: '#6C5DD3',
                        backgroundColor: 'rgba(108, 93, 211, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += '₱' + context.parsed.y.toLocaleString();
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: { 
                        y: { 
                            beginAtZero: true, 
                            grid: { borderDash: [5, 5] },
                            ticks: { callback: function(value) { return '₱' + value; } }
                        }, 
                        x: { grid: { display: false } } 
                    }
                }
            });

            // --- 2. Booking Status Chart (Doughnut) ---
            const statusCtx = document.getElementById('roomStatusChart').getContext('2d');
            const pendingCount = appState.bookings.filter(b => b.status === 'Pending').length;
            const confirmedCount = appState.bookings.filter(b => b.status === 'Confirmed').length;
            const cancelledCount = appState.bookings.filter(b => b.status === 'Cancelled').length;
            
            roomStatusChart = new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Confirmed', 'Pending', 'Cancelled'],
                    datasets: [{
                        data: [confirmedCount, pendingCount, cancelledCount],
                        backgroundColor: ['#22c55e', '#f59e0b', '#ef4444'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    cutout: '75%',
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }

        // --- 1. DASHBOARD OVERVIEW ---
        function renderAdminStats() {
            // This function is called after data is loaded.
            // It renders the main dashboard view into the #admin-overview container.
            const container = document.getElementById('admin-overview');
            if (!container) {
                console.error("Dashboard container #admin-overview not found!");
                return;
            }

            document.getElementById('admin-page-title').innerText = "Dashboard Overview";

            // --- Calculate Stats ---
            // Use 'Confirmed' for revenue, as it's earned income.
            const totalRevenue = appState.bookings
                .filter(b => b.status === 'Confirmed')
                .reduce((sum, b) => sum + (parseFloat(b.total) || 0), 0);
            
            const totalBookings = appState.bookings.length;
            const totalUsers = appState.users.length;
            const totalFeedback = appState.reports ? appState.reports.length : 0; // Use reports length

            // --- Get Recent Bookings ---
            // Slice last 5, then reverse to show most recent first.
            const recentBookings = appState.bookings.slice(-5).reverse();

            // --- Generate HTML ---
            container.innerHTML = `
                <!-- Top Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-50 flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-400 font-medium">Total Revenue</p>
                            <h3 class="text-2xl font-bold text-brand-dark mt-1">₱${totalRevenue.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</h3>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-blue-50 text-brand-blue flex items-center justify-center text-xl"><i class="fas fa-peso-sign"></i></div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-50 flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-400 font-medium">Total Bookings</p>
                            <h3 class="text-2xl font-bold text-brand-dark mt-1">${totalBookings.toLocaleString()}</h3>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-purple-50 text-brand-purple flex items-center justify-center text-xl"><i class="fas fa-bookmark"></i></div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-50 flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-400 font-medium">Total Users</p>
                            <h3 class="text-2xl font-bold text-brand-dark mt-1">${totalUsers.toLocaleString()}</h3>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-green-50 text-green-500 flex items-center justify-center text-xl"><i class="fas fa-users"></i></div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-50 flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-400 font-medium">Feedback</p>
                            <h3 class="text-2xl font-bold text-brand-dark mt-1">${totalFeedback.toLocaleString()}</h3>
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
                        <button onclick="showAdminSection('manage-bookings')" class="text-sm text-brand-purple hover:underline">View All</button>
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
                                ${recentBookings.map(b => `
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="p-4 flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-600">${b.guestName.charAt(0)}</div>
                                            <span class="font-medium text-gray-700">${b.guestName}</span>
                                        </td>
                                        <td class="p-4 text-gray-500">${b.roomName}</td>
                                        <td class="p-4 font-bold text-gray-700">₱${(parseFloat(b.total) || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                        <td class="p-4"><span class="px-3 py-1 rounded-full text-xs font-medium ${b.status === 'Confirmed' ? 'bg-green-100 text-green-600' : (b.status === 'Cancelled' ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-600')}">${b.status}</span></td>
                                    </tr>
                                `).join('')}
                                ${recentBookings.length === 0 ? '<tr><td colspan="4" class="p-8 text-center text-gray-400">No bookings yet.</td></tr>' : ''}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
            
            // Initialize charts with the new data
            initCharts();
        }

        // Start App
        window.addEventListener('DOMContentLoaded', initAdmin);