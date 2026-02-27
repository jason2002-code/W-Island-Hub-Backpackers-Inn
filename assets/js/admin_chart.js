  // --- DATA & STATE MANAGEMENT ---
        // Reads from the exact same localStorage keys as index.html
        let appState = {
            users: JSON.parse(localStorage.getItem('w_users')) || [],
            bookings: JSON.parse(localStorage.getItem('w_bookings')) || [],
            rooms: JSON.parse(localStorage.getItem('w_rooms')) || [],
            reviews: JSON.parse(localStorage.getItem('w_reviews')) || [],
            currentUser: JSON.parse(sessionStorage.getItem('w_currentUser')) || null
        };

        const saveState = () => {
            localStorage.setItem('w_users', JSON.stringify(appState.users));
            localStorage.setItem('w_bookings', JSON.stringify(appState.bookings));
            localStorage.setItem('w_rooms', JSON.stringify(appState.rooms));
            localStorage.setItem('w_reviews', JSON.stringify(appState.reviews));
            if(appState.currentUser) sessionStorage.setItem('w_currentUser', JSON.stringify(appState.currentUser));
            else sessionStorage.removeItem('w_currentUser');
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
                
                clearTimeout(timeoutId);
                renderAdminStats();
                
            } catch (error) {
                console.error('Error loading data:', error);
                // Fallback to localStorage if API fails
                appState.users = JSON.parse(localStorage.getItem('w_users')) || [];
                appState.bookings = JSON.parse(localStorage.getItem('w_bookings')) || [];
                appState.rooms = JSON.parse(localStorage.getItem('w_rooms')) || [];
                appState.reviews = JSON.parse(localStorage.getItem('w_reviews')) || [];
                renderAdminStats();
            }
        }

        function logout() {
            // Destroy PHP session and redirect to main site
            window.location.href = '../logout.php';
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

            const ctx1 = document.getElementById('revenueChart').getContext('2d');
            const totalRev = appState.bookings.filter(b => b.status !== 'Cancelled').reduce((sum,b)=>sum+b.total,0) || 6000;
            revenueChart = new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Revenue ($)',
                        data: [1200, 1900, 3000, 5000, 4500, totalRev],
                        borderColor: '#6C5DD3',
                        backgroundColor: 'rgba(108, 93, 211, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, grid: { borderDash: [5, 5] } }, x: { grid: { display: false } } }
                }
            });

            const ctx2 = document.getElementById('roomStatusChart').getContext('2d');
            const pendingCount = appState.bookings.filter(b => b.status === 'Pending').length;
            const confirmedCount = appState.bookings.filter(b => b.status === 'Confirmed').length;
            const cancelledCount = appState.bookings.filter(b => b.status === 'Cancelled').length;
            
            roomStatusChart = new Chart(ctx2, {
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

        // --- 1. DASHBOARD STATS ---
        function renderAdminStats() {
            const container = document.getElementById('admin-overview');
            if(!container) return;
            
            // Calculate stats
            const totalRevenue = appState.bookings.filter(b => b.status === 'Confirmed').reduce((sum, b) => sum + (parseFloat(b.total) || 0), 0);
            const totalBookings = appState.bookings.length;
            const totalUsers = appState.users.length;
            const totalRooms = appState.rooms.length;

            container.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-50 flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-400 font-medium">Total Revenue</p>
                            <h3 class="text-2xl font-bold text-brand-dark mt-1">$${totalRevenue.toLocaleString()}</h3>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-blue-50 text-brand-blue flex items-center justify-center text-xl"><i class="fas fa-dollar-sign"></i></div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-50 flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-400 font-medium">Total Bookings</p>
                            <h3 class="text-2xl font-bold text-brand-dark mt-1">${totalBookings}</h3>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-purple-50 text-brand-purple flex items-center justify-center text-xl"><i class="fas fa-bookmark"></i></div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-50 flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-400 font-medium">Total Users</p>
                            <h3 class="text-2xl font-bold text-brand-dark mt-1">${totalUsers}</h3>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-green-50 text-green-500 flex items-center justify-center text-xl"><i class="fas fa-users"></i></div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-50 flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-400 font-medium">Total Rooms</p>
                            <h3 class="text-2xl font-bold text-brand-dark mt-1">${totalRooms}</h3>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-yellow-50 text-yellow-500 flex items-center justify-center text-xl"><i class="fas fa-door-open"></i></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-50">
                        <h4 class="font-bold text-gray-700 mb-4">Revenue Analytics</h4>
                        <div class="h-64">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-50">
                        <h4 class="font-bold text-gray-700 mb-4">Booking Status</h4>
                        <div class="h-52 flex justify-center">
                            <canvas id="roomStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            `;
            
            initCharts();
        }

        // Start App
        window.addEventListener('DOMContentLoaded', initAdmin);