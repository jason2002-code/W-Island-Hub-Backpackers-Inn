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
                    <div class="dash-card bg-white p-6 rounded-2xl shadow-sm border border-gray-50 flex items-center justify-between cursor-pointer hover:shadow-lg transition-all duration-300 hover:-translate-y-1" onclick="showRevenueDetailsModal()">
                        <div>
                            <p class="text-sm text-gray-400 font-medium">Total Revenue <i class="fas fa-info-circle text-brand-purple ml-1" title="Click to view details"></i></p>
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

        // --- REVENUE DETAILS MODAL ---
        function showRevenueDetailsModal() {
            let modal = document.getElementById('revenue-details-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'revenue-details-modal';
                modal.className = 'fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 opacity-0 transition-opacity duration-300 hidden';
                modal.innerHTML = `
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col transform scale-95 transition-transform duration-300">
                        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50 rounded-t-2xl">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-800">Revenue & Booking Trends</h3>
                                <p class="text-sm text-gray-500 mt-1">Analyze your booking performance over time</p>
                            </div>
                            <button onclick="closeRevenueDetailsModal()" class="w-10 h-10 rounded-full bg-white border border-gray-200 text-gray-500 hover:text-red-500 hover:border-red-200 flex items-center justify-center transition-all shadow-sm">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="p-6 overflow-y-auto flex-1 bg-white">
                            <div class="flex justify-between items-center mb-6">
                                <h4 class="font-bold text-gray-700">Performance Chart</h4>
                                <select id="trend-timeframe" class="bg-white border border-gray-300 text-gray-700 py-2.5 px-4 rounded-xl outline-none focus:ring-2 focus:ring-brand-purple focus:border-transparent shadow-sm text-sm font-medium" onchange="renderTrendChart()">
                                    <option value="monthly">Monthly View (Last 12 Months)</option>
                                    <option value="daily">Daily View (Last 30 Days)</option>
                                </select>
                            </div>
                            <div class="h-80 w-full relative mb-8 p-4 border border-gray-100 rounded-xl bg-gray-50/50">
                                <canvas id="trendChart"></canvas>
                            </div>
                            
                            <div>
                                <h4 class="font-bold text-gray-700 mb-4">Detailed Breakdown</h4>
                                <div class="overflow-x-auto border border-gray-100 rounded-xl shadow-sm">
                                    <table class="w-full text-left border-collapse">
                                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                                            <tr>
                                                <th class="p-4 font-medium">Period</th>
                                                <th class="p-4 font-medium">Total Bookings</th>
                                                <th class="p-4 font-medium">Confirmed Revenue</th>
                                                <th class="p-4 font-medium">Trend vs Previous</th>
                                            </tr>
                                        </thead>
                                        <tbody id="trend-table-body" class="text-sm divide-y divide-gray-100 bg-white">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }
            
            // Show modal
            modal.classList.remove('hidden');
            // Trigger reflow
            void modal.offsetWidth;
            modal.classList.remove('opacity-0');
            modal.querySelector('div').classList.remove('scale-95');
            
            renderTrendChart();
        }

        function closeRevenueDetailsModal() {
            const modal = document.getElementById('revenue-details-modal');
            if (modal) {
                modal.classList.add('opacity-0');
                modal.querySelector('div').classList.add('scale-95');
                setTimeout(() => {
                    modal.classList.add('hidden');
                    if (window.trendChartInstance) {
                        window.trendChartInstance.destroy();
                        window.trendChartInstance = null;
                    }
                }, 300);
            }
        }
        
        function renderTrendChart() {
            const timeframe = document.getElementById('trend-timeframe').value;
            const ctx = document.getElementById('trendChart').getContext('2d');
            
            if (window.trendChartInstance) {
                window.trendChartInstance.destroy();
            }

            let labels = [];
            let revenueData = [];
            let bookingsData = [];
            let tableData = [];

            const confirmedBookings = appState.bookings.filter(b => b.status === 'Confirmed');

            if (timeframe === 'monthly') {
                const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                const today = new Date();
                
                for (let i = 11; i >= 0; i--) {
                    const d = new Date(today.getFullYear(), today.getMonth() - i, 1);
                    const label = monthNames[d.getMonth()] + ' ' + d.getFullYear();
                    labels.push(label);
                    
                    const monthBookings = confirmedBookings.filter(b => {
                        const bDate = new Date(b.created_at || b.checkIn || b.dateBooked);
                        return bDate.getMonth() === d.getMonth() && bDate.getFullYear() === d.getFullYear();
                    });
                    
                    const rev = monthBookings.reduce((sum, b) => sum + (parseFloat(b.total) || 0), 0);
                    const count = monthBookings.length;
                    
                    revenueData.push(rev);
                    bookingsData.push(count);
                    tableData.push({ period: label, revenue: rev, bookings: count });
                }
            } else {
                const today = new Date();
                
                for (let i = 29; i >= 0; i--) {
                    const d = new Date(today);
                    d.setDate(d.getDate() - i);
                    const label = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                    labels.push(label);
                    
                    const dayBookings = confirmedBookings.filter(b => {
                        const bDate = new Date(b.created_at || b.checkIn || b.dateBooked);
                        return bDate.getDate() === d.getDate() && bDate.getMonth() === d.getMonth() && bDate.getFullYear() === d.getFullYear();
                    });
                    
                    const rev = dayBookings.reduce((sum, b) => sum + (parseFloat(b.total) || 0), 0);
                    const count = dayBookings.length;
                    
                    revenueData.push(rev);
                    bookingsData.push(count);
                    tableData.push({ period: label, revenue: rev, bookings: count });
                }
            }

            window.trendChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Revenue (₱)',
                            data: revenueData,
                            backgroundColor: 'rgba(108, 93, 211, 0.8)',
                            borderColor: '#6C5DD3',
                            borderWidth: 0,
                            borderRadius: 4,
                            yAxisID: 'y',
                            order: 2
                        },
                        {
                            label: 'Bookings Count',
                            data: bookingsData,
                            type: 'line',
                            borderColor: '#22c55e',
                            backgroundColor: '#22c55e',
                            borderWidth: 3,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#22c55e',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            tension: 0.4,
                            yAxisID: 'y1',
                            order: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { position: 'top', align: 'end' },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.95)',
                            titleColor: '#1f2937',
                            bodyColor: '#4b5563',
                            borderColor: '#e5e7eb',
                            borderWidth: 1,
                            padding: 12,
                            boxPadding: 6,
                            usePointStyle: true,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) label += ': ';
                                    if (context.datasetIndex === 0) {
                                        label += '₱' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2});
                                    } else {
                                        label += context.parsed.y;
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: { 
                            type: 'linear', 
                            display: true, 
                            position: 'left',
                            grid: { borderDash: [5, 5], color: '#f3f4f6' },
                            ticks: { callback: value => '₱' + value.toLocaleString() }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            grid: { drawOnChartArea: false },
                            ticks: { stepSize: 1, precision: 0 }
                        }
                    }
                }
            });

            const tbody = document.getElementById('trend-table-body');
            const displayData = [...tableData].reverse();
            
            tbody.innerHTML = displayData.map((row, index) => {
                let trendHtml = '-';
                if (index < displayData.length - 1) {
                    const prevRow = displayData[index + 1];
                    if (row.revenue > prevRow.revenue) {
                        trendHtml = '<span class="text-green-600 bg-green-50 px-2 py-1 rounded-md text-xs font-bold"><i class="fas fa-arrow-up mr-1"></i> Higher</span>';
                    } else if (row.revenue < prevRow.revenue) {
                        trendHtml = '<span class="text-red-600 bg-red-50 px-2 py-1 rounded-md text-xs font-bold"><i class="fas fa-arrow-down mr-1"></i> Lower</span>';
                    } else {
                        trendHtml = '<span class="text-gray-500 bg-gray-50 px-2 py-1 rounded-md text-xs font-bold"><i class="fas fa-minus mr-1"></i> Same</span>';
                    }
                }
                
                return `
                    <tr class="hover:bg-gray-50 transition border-l-4 ${row.revenue > 0 ? 'border-l-brand-purple' : 'border-l-transparent'}">
                        <td class="p-4 font-medium text-gray-800">${row.period}</td>
                        <td class="p-4"><span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs font-bold">${row.bookings}</span></td>
                        <td class="p-4 font-bold text-brand-dark text-base">₱${row.revenue.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                        <td class="p-4">${trendHtml}</td>
                    </tr>
                `;
            }).join('');
        }