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
            if (typeof window.toggleAdminSidebar === 'function') {
                window.toggleAdminSidebar();
            }
        }

        function setActiveAdminLink(element) {
            if (typeof window.setActiveAdminLink === 'function') {
                window.setActiveAdminLink(element);
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
            // It now only updates the dynamic values in the existing PHP-rendered dashboard.
            const container = document.getElementById('admin-overview');
            if (!container) {
                console.error("Dashboard container #admin-overview not found!");
                return;
            }
        
            // --- Calculate Stats ---
            const totalRevenue = appState.bookings
                .filter(b => b.status === 'Confirmed')
                .reduce((sum, b) => sum + (parseFloat(b.total) || 0), 0);
            const totalBookings = appState.bookings.length;
            const totalUsers = appState.users.length;
            const totalRooms = appState.rooms.length;
        
            // --- Update UI Elements ---
            // Do not change the title here, keep the one from PHP
            // document.getElementById('admin-page-title').innerText = "Analytics Overview";
        
            // Update stat cards by ID with selector fallback
            const revenueCard = document.getElementById('dash-total-revenue') || container.querySelector('h3.text-2xl.font-bold');
            if (revenueCard) revenueCard.innerHTML = `₱${totalRevenue.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        
            const bookingCard = document.getElementById('dash-total-bookings') || container.querySelectorAll('h3.text-2xl.font-bold')[1];
            if (bookingCard) bookingCard.textContent = totalBookings.toLocaleString();
        
            const userCard = document.getElementById('dash-total-users') || container.querySelectorAll('h3.text-2xl.font-bold')[2];
            if (userCard) userCard.textContent = totalUsers.toLocaleString();
        
            const roomCard = document.getElementById('dash-total-rooms') || container.querySelectorAll('h3.text-2xl.font-bold')[3];
            if (roomCard) roomCard.textContent = totalRooms.toLocaleString();
        
            // Initialize charts with the new data
            initCharts();
        }

        // Start App
        window.addEventListener('DOMContentLoaded', initAdmin);

        // --- REVENUE DETAILS MODAL ---
        // --- REVENUE DETAILS MODAL & CUSTOM DATE TRENDS ---
        function formatTrendYMD(d) {
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${day}`;
        }

        function showRevenueDetailsModal() {
            let modal = document.getElementById('revenue-details-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'revenue-details-modal';
                modal.className = 'fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-3 sm:p-4 opacity-0 transition-opacity duration-300 hidden';
                modal.innerHTML = `
                    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-5xl max-h-[92vh] flex flex-col transform scale-95 transition-transform duration-300 overflow-hidden">
                        <!-- Modal Header -->
                        <div class="p-5 sm:p-6 border-b border-gray-100 flex flex-wrap justify-between items-center gap-4 bg-gray-50/80">
                            <div>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl bg-purple-100 text-brand-purple flex items-center justify-center text-xl shadow-xs">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xl sm:text-2xl font-bold text-gray-800">Revenue & Booking Trends</h3>
                                        <p class="text-xs sm:text-sm text-gray-500">Analyze performance over standard or custom date ranges</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button onclick="exportTrendDataToCSV()" title="Export current trend data to CSV" class="h-10 px-3.5 rounded-xl bg-white border border-gray-200 text-gray-600 hover:text-brand-purple hover:border-brand-purple text-xs font-semibold flex items-center gap-1.5 transition-all shadow-xs cursor-pointer">
                                    <i class="fas fa-file-csv text-brand-purple text-sm"></i> <span class="hidden sm:inline">Export CSV</span>
                                </button>
                                <button onclick="closeRevenueDetailsModal()" class="w-10 h-10 rounded-xl bg-white border border-gray-200 text-gray-400 hover:text-red-500 hover:border-red-200 flex items-center justify-center transition-all shadow-xs cursor-pointer">
                                    <i class="fas fa-times text-base"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Modal Body -->
                        <div class="p-5 sm:p-6 overflow-y-auto flex-1 bg-white space-y-6">
                            <!-- Controls Row -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-1">
                                <div>
                                    <span class="text-xs text-gray-500 font-medium" id="trend-period-label">Select timeframe to update metrics</span>
                                </div>
                                <div class="flex items-center gap-2.5">
                                    <label for="trend-timeframe" class="text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:inline">Timeframe:</label>
                                    <select id="trend-timeframe" class="bg-white border border-gray-200 text-gray-700 py-2 px-3.5 rounded-xl outline-none focus:ring-2 focus:ring-brand-purple/20 focus:border-brand-purple shadow-xs text-sm font-semibold cursor-pointer" onchange="handleTrendTimeframeChange()">
                                        <option value="monthly">Monthly View (Last 12 Months)</option>
                                        <option value="weekly">Weekly View (Last 12 Weeks)</option>
                                        <option value="daily">Daily View (Last 30 Days)</option>
                                        <option value="custom">📅 Custom Date Range</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Custom Date Range Bar -->
                            <div id="trend-custom-date-container" class="hidden p-4 bg-slate-50/80 border border-slate-200/90 rounded-2xl space-y-3 transition-all">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div class="flex flex-wrap items-center gap-2.5">
                                        <!-- From Date -->
                                        <div class="flex items-center gap-2 bg-white px-3 py-1.5 border border-gray-200 rounded-xl shadow-xs">
                                            <span class="text-xs font-bold text-gray-400 uppercase">From:</span>
                                            <input type="date" id="trend-start-date" class="text-xs sm:text-sm font-semibold text-gray-700 outline-none bg-transparent cursor-pointer" onchange="renderTrendChart()">
                                        </div>
                                        <!-- To Date -->
                                        <div class="flex items-center gap-2 bg-white px-3 py-1.5 border border-gray-200 rounded-xl shadow-xs">
                                            <span class="text-xs font-bold text-gray-400 uppercase">To:</span>
                                            <input type="date" id="trend-end-date" class="text-xs sm:text-sm font-semibold text-gray-700 outline-none bg-transparent cursor-pointer" onchange="renderTrendChart()">
                                        </div>
                                        <!-- Group By -->
                                        <div class="flex items-center gap-2 bg-white px-3 py-1.5 border border-gray-200 rounded-xl shadow-xs">
                                            <span class="text-xs font-bold text-gray-400 uppercase">Interval:</span>
                                            <select id="trend-group-by" class="text-xs sm:text-sm font-semibold text-gray-700 outline-none bg-transparent cursor-pointer" onchange="renderTrendChart()">
                                                <option value="auto">Auto (Smart)</option>
                                                <option value="daily">Daily</option>
                                                <option value="weekly">Weekly</option>
                                                <option value="monthly">Monthly</option>
                                            </select>
                                        </div>
                                        <!-- Apply -->
                                        <button type="button" onclick="renderTrendChart()" class="bg-brand-purple hover:bg-indigo-700 text-white text-xs font-semibold px-4 py-2 rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                                            <i class="fas fa-filter text-xs"></i> Apply Range
                                        </button>
                                    </div>
                                    <!-- Quick Presets -->
                                    <div class="flex flex-wrap items-center gap-1.5 text-xs">
                                        <span class="text-gray-400 font-semibold mr-1 hidden lg:inline">Presets:</span>
                                        <button type="button" onclick="setTrendQuickRange('last_7_days')" class="px-2.5 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600 hover:text-brand-purple hover:border-brand-purple font-semibold transition cursor-pointer">7D</button>
                                        <button type="button" onclick="setTrendQuickRange('last_30_days')" class="px-2.5 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600 hover:text-brand-purple hover:border-brand-purple font-semibold transition cursor-pointer">30D</button>
                                        <button type="button" onclick="setTrendQuickRange('this_month')" class="px-2.5 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600 hover:text-brand-purple hover:border-brand-purple font-semibold transition cursor-pointer">This Month</button>
                                        <button type="button" onclick="setTrendQuickRange('last_month')" class="px-2.5 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600 hover:text-brand-purple hover:border-brand-purple font-semibold transition cursor-pointer">Last Month</button>
                                        <button type="button" onclick="setTrendQuickRange('this_year')" class="px-2.5 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600 hover:text-brand-purple hover:border-brand-purple font-semibold transition cursor-pointer">This Year</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Summary Metrics Cards -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="bg-gradient-to-br from-purple-50 to-white border border-purple-100 rounded-2xl p-4 shadow-xs">
                                    <div class="text-[11px] font-bold text-brand-purple uppercase tracking-wider flex items-center justify-between">
                                        <span>Period Revenue</span>
                                        <i class="fas fa-peso-sign text-brand-purple/60"></i>
                                    </div>
                                    <div id="trend-summary-revenue" class="text-2xl font-bold text-gray-800 mt-2">₱0.00</div>
                                </div>
                                <div class="bg-gradient-to-br from-emerald-50 to-white border border-emerald-100 rounded-2xl p-4 shadow-xs">
                                    <div class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider flex items-center justify-between">
                                        <span>Confirmed Bookings</span>
                                        <i class="fas fa-calendar-check text-emerald-500/60"></i>
                                    </div>
                                    <div id="trend-summary-bookings" class="text-2xl font-bold text-gray-800 mt-2">0</div>
                                </div>
                                <div class="bg-gradient-to-br from-blue-50 to-white border border-blue-100 rounded-2xl p-4 shadow-xs">
                                    <div class="text-[11px] font-bold text-brand-blue uppercase tracking-wider flex items-center justify-between">
                                        <span>Average Booking Value</span>
                                        <i class="fas fa-calculator text-brand-blue/60"></i>
                                    </div>
                                    <div id="trend-summary-avg" class="text-2xl font-bold text-gray-800 mt-2">₱0.00</div>
                                </div>
                            </div>

                            <!-- Chart Canvas Container -->
                            <div class="h-80 w-full relative p-4 border border-gray-100 rounded-2xl bg-gray-50/50 shadow-inner">
                                <canvas id="trendChart"></canvas>
                            </div>
                            
                            <!-- Detailed Table -->
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="font-bold text-gray-700 text-sm">Detailed Breakdown</h4>
                                </div>
                                <div class="overflow-x-auto border border-gray-100 rounded-2xl shadow-xs">
                                    <table class="w-full text-left border-collapse">
                                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                                            <tr>
                                                <th class="p-4 font-semibold">Period</th>
                                                <th class="p-4 font-semibold">Total Bookings</th>
                                                <th class="p-4 font-semibold">Confirmed Revenue</th>
                                                <th class="p-4 font-semibold">Avg. / Booking</th>
                                                <th class="p-4 font-semibold">Trend vs Previous</th>
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

            // Set default dates for custom range if not already populated
            const today = new Date();
            const past30 = new Date(today);
            past30.setDate(today.getDate() - 30);

            const startInput = document.getElementById('trend-start-date');
            const endInput = document.getElementById('trend-end-date');
            if (startInput && !startInput.value) startInput.value = formatTrendYMD(past30);
            if (endInput && !endInput.value) endInput.value = formatTrendYMD(today);
            
            // Show modal
            modal.classList.remove('hidden');
            // Trigger reflow
            void modal.offsetWidth;
            modal.classList.remove('opacity-0');
            const inner = modal.querySelector('div');
            if (inner) inner.classList.remove('scale-95');
            
            renderTrendChart();
        }

        function closeRevenueDetailsModal() {
            const modal = document.getElementById('revenue-details-modal');
            if (modal) {
                modal.classList.add('opacity-0');
                const inner = modal.querySelector('div');
                if (inner) inner.classList.add('scale-95');
                setTimeout(() => {
                    modal.classList.add('hidden');
                    if (window.trendChartInstance) {
                        window.trendChartInstance.destroy();
                        window.trendChartInstance = null;
                    }
                }, 300);
            }
        }

        function handleTrendTimeframeChange() {
            const timeframe = document.getElementById('trend-timeframe').value;
            const customContainer = document.getElementById('trend-custom-date-container');
            if (customContainer) {
                if (timeframe === 'custom') {
                    customContainer.classList.remove('hidden');
                } else {
                    customContainer.classList.add('hidden');
                }
            }
            renderTrendChart();
        }

        function setTrendQuickRange(type) {
            const today = new Date();
            const startInput = document.getElementById('trend-start-date');
            const endInput = document.getElementById('trend-end-date');
            const timeframeSelect = document.getElementById('trend-timeframe');
            const customContainer = document.getElementById('trend-custom-date-container');

            if (timeframeSelect && timeframeSelect.value !== 'custom') {
                timeframeSelect.value = 'custom';
                if (customContainer) customContainer.classList.remove('hidden');
            }

            if (!startInput || !endInput) return;

            if (type === 'last_7_days') {
                const d = new Date(today);
                d.setDate(today.getDate() - 6);
                startInput.value = formatTrendYMD(d);
                endInput.value = formatTrendYMD(today);
            } else if (type === 'last_30_days') {
                const d = new Date(today);
                d.setDate(today.getDate() - 29);
                startInput.value = formatTrendYMD(d);
                endInput.value = formatTrendYMD(today);
            } else if (type === 'this_month') {
                const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                startInput.value = formatTrendYMD(firstDay);
                endInput.value = formatTrendYMD(today);
            } else if (type === 'last_month') {
                const firstDayLastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                const lastDayLastMonth = new Date(today.getFullYear(), today.getMonth(), 0);
                startInput.value = formatTrendYMD(firstDayLastMonth);
                endInput.value = formatTrendYMD(lastDayLastMonth);
            } else if (type === 'this_year') {
                const firstDayYear = new Date(today.getFullYear(), 0, 1);
                startInput.value = formatTrendYMD(firstDayYear);
                endInput.value = formatTrendYMD(today);
            }

            renderTrendChart();
        }

        function exportTrendDataToCSV() {
            const tableBody = document.getElementById('trend-table-body');
            if (!tableBody) return;
            const rows = tableBody.querySelectorAll('tr');
            if (!rows.length) {
                alert('No trend data available to export.');
                return;
            }

            let csv = "Period,Total Bookings,Confirmed Revenue,Average per Booking\n";
            rows.forEach(r => {
                const cells = r.querySelectorAll('td');
                if (cells.length >= 4) {
                    const period = `"${cells[0].innerText.trim()}"`;
                    const bookings = `"${cells[1].innerText.trim()}"`;
                    const revenue = `"${cells[2].innerText.replace('₱', '').replace(/,/g, '').trim()}"`;
                    const avg = `"${cells[3].innerText.replace('₱', '').replace(/,/g, '').trim()}"`;
                    csv += `${period},${bookings},${revenue},${avg}\n`;
                }
            });

            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', `revenue_booking_trends_${formatTrendYMD(new Date())}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        
        function renderTrendChart() {
            const timeframeSelect = document.getElementById('trend-timeframe');
            if (!timeframeSelect) return;
            const timeframe = timeframeSelect.value;
            const canvas = document.getElementById('trendChart');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            
            if (window.trendChartInstance) {
                window.trendChartInstance.destroy();
                window.trendChartInstance = null;
            }

            let labels = [];
            let revenueData = [];
            let bookingsData = [];
            let tableData = [];
            let periodSubtitle = '';

            const confirmedBookings = (appState.bookings || []).filter(b => b.status === 'Confirmed');

            const getBookingDate = (b) => {
                const raw = b.created_at || b.checkIn || b.dateBooked || '';
                if (!raw) return null;
                const d = new Date(raw.includes(' ') ? raw.replace(' ', 'T') : raw);
                return isNaN(d.getTime()) ? null : d;
            };

            if (timeframe === 'monthly') {
                const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                const today = new Date();
                periodSubtitle = 'Monthly view for the past 12 months';
                
                for (let i = 11; i >= 0; i--) {
                    const d = new Date(today.getFullYear(), today.getMonth() - i, 1);
                    const label = monthNames[d.getMonth()] + ' ' + d.getFullYear();
                    labels.push(label);
                    
                    const monthBookings = confirmedBookings.filter(b => {
                        const bDate = getBookingDate(b);
                        return bDate && bDate.getMonth() === d.getMonth() && bDate.getFullYear() === d.getFullYear();
                    });
                    
                    const rev = monthBookings.reduce((sum, b) => sum + (parseFloat(b.total) || 0), 0);
                    const count = monthBookings.length;
                    
                    revenueData.push(rev);
                    bookingsData.push(count);
                    tableData.push({ period: label, revenue: rev, bookings: count });
                }
            } else if (timeframe === 'weekly') {
                const today = new Date();
                today.setHours(23, 59, 59, 999);
                periodSubtitle = 'Weekly view for the past 12 weeks';
                
                for (let i = 11; i >= 0; i--) {
                    const weekEnd = new Date(today);
                    weekEnd.setDate(today.getDate() - (i * 7));
                    const weekStart = new Date(weekEnd);
                    weekStart.setDate(weekEnd.getDate() - 6);
                    weekStart.setHours(0, 0, 0, 0);

                    const startStr = weekStart.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                    const endStr = weekEnd.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                    const label = `${startStr} - ${endStr}`;
                    labels.push(label);

                    const weekBookings = confirmedBookings.filter(b => {
                        const bDate = getBookingDate(b);
                        return bDate && bDate >= weekStart && bDate <= weekEnd;
                    });

                    const rev = weekBookings.reduce((sum, b) => sum + (parseFloat(b.total) || 0), 0);
                    const count = weekBookings.length;

                    revenueData.push(rev);
                    bookingsData.push(count);
                    tableData.push({ period: label, revenue: rev, bookings: count });
                }
            } else if (timeframe === 'daily') {
                const today = new Date();
                periodSubtitle = 'Daily view for the past 30 days';
                
                for (let i = 29; i >= 0; i--) {
                    const d = new Date(today);
                    d.setDate(d.getDate() - i);
                    const label = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                    labels.push(label);
                    
                    const dayBookings = confirmedBookings.filter(b => {
                        const bDate = getBookingDate(b);
                        return bDate && bDate.getDate() === d.getDate() && bDate.getMonth() === d.getMonth() && bDate.getFullYear() === d.getFullYear();
                    });
                    
                    const rev = dayBookings.reduce((sum, b) => sum + (parseFloat(b.total) || 0), 0);
                    const count = dayBookings.length;
                    
                    revenueData.push(rev);
                    bookingsData.push(count);
                    tableData.push({ period: label, revenue: rev, bookings: count });
                }
            } else if (timeframe === 'custom') {
                const startInput = document.getElementById('trend-start-date');
                const endInput = document.getElementById('trend-end-date');
                const groupBySelect = document.getElementById('trend-group-by');

                let startDate = startInput && startInput.value ? new Date(startInput.value + 'T00:00:00') : new Date();
                let endDate = endInput && endInput.value ? new Date(endInput.value + 'T23:59:59') : new Date();

                if (isNaN(startDate.getTime())) {
                    startDate = new Date();
                    startDate.setDate(startDate.getDate() - 30);
                }
                if (isNaN(endDate.getTime())) {
                    endDate = new Date();
                }
                if (startDate > endDate) {
                    const temp = startDate;
                    startDate = endDate;
                    endDate = temp;
                    if (startInput) startInput.value = formatTrendYMD(startDate);
                    if (endInput) endInput.value = formatTrendYMD(endDate);
                }

                startDate.setHours(0, 0, 0, 0);
                endDate.setHours(23, 59, 59, 999);

                const diffTime = Math.abs(endDate - startDate);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) || 1;

                periodSubtitle = `Custom range: ${startDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })} to ${endDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })} (${diffDays} days)`;

                let groupBy = groupBySelect ? groupBySelect.value : 'auto';
                if (groupBy === 'auto') {
                    if (diffDays <= 31) groupBy = 'daily';
                    else if (diffDays <= 90) groupBy = 'weekly';
                    else groupBy = 'monthly';
                }

                if (groupBy === 'daily') {
                    const cur = new Date(startDate);
                    while (cur <= endDate) {
                        const dayStart = new Date(cur);
                        dayStart.setHours(0, 0, 0, 0);
                        const dayEnd = new Date(cur);
                        dayEnd.setHours(23, 59, 59, 999);

                        const label = cur.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: diffDays > 365 ? '2-digit' : undefined });
                        labels.push(label);

                        const dayBookings = confirmedBookings.filter(b => {
                            const bDate = getBookingDate(b);
                            return bDate && bDate >= dayStart && bDate <= dayEnd;
                        });

                        const rev = dayBookings.reduce((sum, b) => sum + (parseFloat(b.total) || 0), 0);
                        const count = dayBookings.length;

                        revenueData.push(rev);
                        bookingsData.push(count);
                        tableData.push({ period: label, revenue: rev, bookings: count });

                        cur.setDate(cur.getDate() + 1);
                    }
                } else if (groupBy === 'weekly') {
                    const cur = new Date(startDate);
                    while (cur <= endDate) {
                        const weekStart = new Date(cur);
                        weekStart.setHours(0, 0, 0, 0);
                        const weekEnd = new Date(cur);
                        weekEnd.setDate(weekEnd.getDate() + 6);
                        weekEnd.setHours(23, 59, 59, 999);
                        if (weekEnd > endDate) weekEnd.setTime(endDate.getTime());

                        const label = `${weekStart.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${weekEnd.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}`;
                        labels.push(label);

                        const weekBookings = confirmedBookings.filter(b => {
                            const bDate = getBookingDate(b);
                            return bDate && bDate >= weekStart && bDate <= weekEnd;
                        });

                        const rev = weekBookings.reduce((sum, b) => sum + (parseFloat(b.total) || 0), 0);
                        const count = weekBookings.length;

                        revenueData.push(rev);
                        bookingsData.push(count);
                        tableData.push({ period: label, revenue: rev, bookings: count });

                        cur.setDate(cur.getDate() + 7);
                    }
                } else if (groupBy === 'monthly') {
                    const cur = new Date(startDate.getFullYear(), startDate.getMonth(), 1);
                    const endMonth = new Date(endDate.getFullYear(), endDate.getMonth(), 1);
                    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

                    while (cur <= endMonth) {
                        const monthStart = new Date(cur.getFullYear(), cur.getMonth(), 1, 0, 0, 0, 0);
                        const monthEnd = new Date(cur.getFullYear(), cur.getMonth() + 1, 0, 23, 59, 59, 999);

                        const effectiveStart = monthStart < startDate ? startDate : monthStart;
                        const effectiveEnd = monthEnd > endDate ? endDate : monthEnd;

                        const label = `${monthNames[cur.getMonth()]} ${cur.getFullYear()}`;
                        labels.push(label);

                        const monthBookings = confirmedBookings.filter(b => {
                            const bDate = getBookingDate(b);
                            return bDate && bDate >= effectiveStart && bDate <= effectiveEnd;
                        });

                        const rev = monthBookings.reduce((sum, b) => sum + (parseFloat(b.total) || 0), 0);
                        const count = monthBookings.length;

                        revenueData.push(rev);
                        bookingsData.push(count);
                        tableData.push({ period: label, revenue: rev, bookings: count });

                        cur.setMonth(cur.getMonth() + 1);
                    }
                }
            }

            // Update subtitle and summary metrics
            const periodLabelEl = document.getElementById('trend-period-label');
            if (periodLabelEl) periodLabelEl.textContent = periodSubtitle;

            const totalPeriodRevenue = revenueData.reduce((sum, v) => sum + v, 0);
            const totalPeriodBookings = bookingsData.reduce((sum, v) => sum + v, 0);
            const avgBookingValue = totalPeriodBookings > 0 ? (totalPeriodRevenue / totalPeriodBookings) : 0;

            const elRev = document.getElementById('trend-summary-revenue');
            const elBookings = document.getElementById('trend-summary-bookings');
            const elAvg = document.getElementById('trend-summary-avg');
            const elCount = document.getElementById('trend-record-count');

            if (elRev) elRev.innerText = '₱' + totalPeriodRevenue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            if (elBookings) elBookings.innerText = totalPeriodBookings.toLocaleString();
            if (elAvg) elAvg.innerText = '₱' + avgBookingValue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            if (elCount) elCount.innerText = `${tableData.length} ${tableData.length === 1 ? 'interval' : 'intervals'}`;

            // Initialize Chart instance
            window.trendChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Revenue (₱)',
                            data: revenueData,
                            backgroundColor: 'rgba(108, 93, 211, 0.85)',
                            hoverBackgroundColor: 'rgba(108, 93, 211, 1)',
                            borderColor: '#6C5DD3',
                            borderWidth: 0,
                            borderRadius: 6,
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
                            tension: 0.35,
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
                            backgroundColor: 'rgba(255, 255, 255, 0.98)',
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
                                        label += '₱' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                    } else {
                                        label += context.parsed.y;
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: { 
                            grid: { display: false },
                            ticks: { maxRotation: 45, minRotation: 0, font: { size: 11 } }
                        },
                        y: { 
                            type: 'linear', 
                            display: true, 
                            position: 'left',
                            beginAtZero: true,
                            grid: { borderDash: [5, 5], color: '#f3f4f6' },
                            ticks: { callback: value => '₱' + value.toLocaleString() }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            beginAtZero: true,
                            grid: { drawOnChartArea: false },
                            ticks: { stepSize: 1, precision: 0 }
                        }
                    }
                }
            });

            // Populate Breakdown Table
            const tbody = document.getElementById('trend-table-body');
            const displayData = [...tableData].reverse();
            
            if (displayData.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="p-8 text-center text-gray-400">
                            <i class="fas fa-calendar-times text-2xl mb-2 text-gray-300 block"></i>
                            No confirmed bookings found for the selected timeframe.
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = displayData.map((row, index) => {
                let trendHtml = '-';
                if (index < displayData.length - 1) {
                    const prevRow = displayData[index + 1];
                    if (row.revenue > prevRow.revenue) {
                        trendHtml = '<span class="text-green-600 bg-green-50 px-2.5 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1"><i class="fas fa-arrow-up text-[10px]"></i> Higher</span>';
                    } else if (row.revenue < prevRow.revenue) {
                        trendHtml = '<span class="text-red-600 bg-red-50 px-2.5 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1"><i class="fas fa-arrow-down text-[10px]"></i> Lower</span>';
                    } else {
                        trendHtml = '<span class="text-gray-500 bg-gray-50 px-2.5 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1"><i class="fas fa-minus text-[10px]"></i> Same</span>';
                    }
                }
                
                const avg = row.bookings > 0 ? (row.revenue / row.bookings) : 0;

                return `
                    <tr class="hover:bg-gray-50/80 transition border-l-4 ${row.revenue > 0 ? 'border-l-brand-purple' : 'border-l-transparent'}">
                        <td class="p-4 font-semibold text-gray-800">${row.period}</td>
                        <td class="p-4"><span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs font-bold">${row.bookings}</span></td>
                        <td class="p-4 font-bold text-brand-dark text-sm">₱${row.revenue.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="p-4 text-xs font-medium text-gray-500">₱${avg.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="p-4">${trendHtml}</td>
                    </tr>
                `;
            }).join('');
        }

        // Global exports for inline HTML handlers
        window.showRevenueDetailsModal = showRevenueDetailsModal;
        window.closeRevenueDetailsModal = closeRevenueDetailsModal;
        window.handleTrendTimeframeChange = handleTrendTimeframeChange;
        window.setTrendQuickRange = setTrendQuickRange;
        window.exportTrendDataToCSV = exportTrendDataToCSV;
        window.renderTrendChart = renderTrendChart;