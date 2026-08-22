<!-- ================= Hotel Reports & Feedback Center ================= -->
<div id="manage-reports" class="page-section">
    <div class="p-6">
        <!-- Header & Tab Navigation -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h3 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-file-invoice-dollar text-brand-purple"></i> Hotel Reports & Analytics
                </h3>
                <p class="text-sm text-gray-500 mt-1">Generate, analyze, export to CSV, and print official hotel operational and financial reports</p>
            </div>
            
            <!-- Tab Switcher -->
            <div class="flex bg-gray-100 p-1 rounded-xl shadow-inner text-sm font-medium self-start md:self-auto">
                <button onclick="switchReportTab('reports-tab')" id="tab-btn-reports" class="report-tab-btn px-4 py-2 rounded-lg transition-all flex items-center gap-2 bg-white text-brand-purple shadow-sm font-bold">
                    <i class="fas fa-chart-line"></i> Reports & Printing
                </button>
                <button onclick="switchReportTab('feedback-tab')" id="tab-btn-feedback" class="report-tab-btn px-4 py-2 rounded-lg transition-all flex items-center gap-2 text-gray-600 hover:text-gray-900 font-medium">
                    <i class="fas fa-comments"></i> Guest Feedback
                    <span id="feedback-badge" class="ml-1 px-1.5 py-0.2 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full hidden">0</span>
                </button>
            </div>
        </div>

        <!-- ================= TAB 1: HOTEL REPORTS & PRINTING ================= -->
        <div id="reports-tab-content" class="report-tab-pane space-y-6">
            <!-- Filter & Controls Card -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-5">
                    <h4 class="font-bold text-gray-800 flex items-center gap-2 text-base">
                        <i class="fas fa-sliders-h text-brand-blue"></i> Report Parameters & Filters
                    </h4>
                    <span class="text-xs bg-purple-50 text-brand-purple font-semibold px-2.5 py-1 rounded-full" id="report-ref-badge">
                        Ref: RPT-READY
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
                    <!-- 1. Report Type -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Report Type</label>
                        <select id="hotel-report-type" onchange="handleReportTypeChange()" class="w-full bg-gray-50 border border-gray-200 text-gray-800 text-sm rounded-xl p-2.5 outline-none focus:ring-2 focus:ring-brand-purple/20 focus:border-brand-purple font-medium">
                            <option value="financial">💰 Financial & Revenue Summary</option>
                            <option value="bookings">📑 Bookings & Reservations Register</option>
                            <option value="daily_manifest">📋 Front Desk Daily Manifest</option>
                            <option value="room_performance">🛏️ Room Occupancy & Performance</option>
                            <option value="feedback">⭐ Guest Feedback & Satisfaction</option>
                        </select>
                    </div>

                    <!-- 2. Date Range Preset -->
                    <div id="date-preset-container">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Date Period</label>
                        <select id="hotel-report-period" onchange="handleDatePeriodChange()" class="w-full bg-gray-50 border border-gray-200 text-gray-800 text-sm rounded-xl p-2.5 outline-none focus:ring-2 focus:ring-brand-purple/20 focus:border-brand-purple font-medium">
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="last_7_days">Last 7 Days</option>
                            <option value="this_month" selected>This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="this_year">This Year</option>
                            <option value="all_time">All Time History</option>
                            <option value="custom">Custom Date Range</option>
                        </select>
                    </div>

                    <!-- 3. Status Filter (For Bookings/Financial) -->
                    <div id="status-filter-container">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Booking Status</label>
                        <select id="hotel-report-status" class="w-full bg-gray-50 border border-gray-200 text-gray-800 text-sm rounded-xl p-2.5 outline-none focus:ring-2 focus:ring-brand-purple/20 focus:border-brand-purple font-medium">
                            <option value="all">All Statuses (Confirmed, Pending, Cancelled)</option>
                            <option value="Confirmed">Confirmed Only (Paid/Valid)</option>
                            <option value="Pending">Pending Only</option>
                            <option value="Cancelled">Cancelled Only</option>
                        </select>
                    </div>

                    <!-- 4. Room Type Filter -->
                    <div id="room-type-filter-container">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Room Type</label>
                        <select id="hotel-report-room-type" class="w-full bg-gray-50 border border-gray-200 text-gray-800 text-sm rounded-xl p-2.5 outline-none focus:ring-2 focus:ring-brand-purple/20 focus:border-brand-purple font-medium">
                            <option value="all">All Room Types</option>
                            <option value="Dorm">Backpacker Dorm</option>
                            <option value="Standard">Standard Double</option>
                            <option value="Deluxe">Ocean Deluxe</option>
                            <option value="Family">Family Suite</option>
                        </select>
                    </div>
                </div>

                <!-- Custom Date Range Row (Hidden unless 'custom' is picked or for manifest target date) -->
                <div id="custom-date-row" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-3 border-t border-gray-50 hidden">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">From Date</label>
                        <input type="date" id="hotel-report-from" class="w-full bg-gray-50 border border-gray-200 text-gray-800 text-sm rounded-xl p-2.5 outline-none focus:ring-2 focus:ring-brand-purple/20 focus:border-brand-purple">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">To Date</label>
                        <input type="date" id="hotel-report-to" class="w-full bg-gray-50 border border-gray-200 text-gray-800 text-sm rounded-xl p-2.5 outline-none focus:ring-2 focus:ring-brand-purple/20 focus:border-brand-purple">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Date Basis</label>
                        <select id="hotel-report-date-field" class="w-full bg-gray-50 border border-gray-200 text-gray-800 text-sm rounded-xl p-2.5 outline-none focus:ring-2 focus:ring-brand-purple/20 focus:border-brand-purple">
                            <option value="created_at">Booking Date (Transaction Created)</option>
                            <option value="check_in">Stay Date (Check-in Date)</option>
                        </select>
                    </div>
                </div>

                <!-- Action Buttons Toolbar -->
                <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-gray-100 mt-4">
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <i class="fas fa-info-circle text-blue-500"></i>
                        <span id="active-filter-summary">Showing: This Month • All Statuses • All Types</span>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button onclick="generateHotelReportData()" id="btn-generate-report" class="bg-brand-purple text-white px-5 py-2.5 rounded-xl font-bold shadow-md hover:bg-purple-700 transition flex items-center gap-2 text-sm">
                            <i class="fas fa-bolt"></i> Generate Report
                        </button>
                        <button onclick="printHotelReport()" id="btn-print-report" class="bg-brand-blue text-white px-5 py-2.5 rounded-xl font-bold shadow-md hover:bg-sky-600 transition flex items-center gap-2 text-sm">
                            <i class="fas fa-print"></i> Print Report
                        </button>
                        <button onclick="exportReportCSV()" id="btn-export-csv" class="bg-emerald-600 text-white px-4 py-2.5 rounded-xl font-bold shadow-md hover:bg-emerald-700 transition flex items-center gap-2 text-sm">
                            <i class="fas fa-file-excel"></i> Export CSV
                        </button>
                        <button onclick="generateHotelReportData(true)" class="bg-gray-100 text-gray-600 px-3.5 py-2.5 rounded-xl font-semibold hover:bg-gray-200 transition" title="Refresh">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Summary KPI Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" id="report-kpi-cards">
                <!-- Card 1 -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider" id="kpi-1-label">Confirmed Revenue</p>
                        <h4 class="text-2xl font-bold text-brand-dark mt-1" id="kpi-1-val">₱0.00</h4>
                        <p class="text-xs text-gray-500 mt-1" id="kpi-1-sub">Net Room: ₱0.00</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-brand-blue flex items-center justify-center text-xl">
                        <i class="fas fa-peso-sign"></i>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider" id="kpi-2-label">Total Bookings</p>
                        <h4 class="text-2xl font-bold text-brand-dark mt-1" id="kpi-2-val">0</h4>
                        <p class="text-xs text-emerald-600 font-semibold mt-1" id="kpi-2-sub">0 Confirmed</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-purple-50 text-brand-purple flex items-center justify-center text-xl">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider" id="kpi-3-label">Occupied Nights</p>
                        <h4 class="text-2xl font-bold text-brand-dark mt-1" id="kpi-3-val">0</h4>
                        <p class="text-xs text-gray-500 mt-1" id="kpi-3-sub">ADR: ₱0.00 / night</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                        <i class="fas fa-bed"></i>
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider" id="kpi-4-label">Pending / Cancelled</p>
                        <h4 class="text-2xl font-bold text-yellow-600 mt-1" id="kpi-4-val">0 / 0</h4>
                        <p class="text-xs text-gray-500 mt-1" id="kpi-4-sub">₱0.00 pending value</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-yellow-50 text-yellow-600 flex items-center justify-center text-xl">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
            </div>

            <!-- Distribution & Breakdown Pill Bar (For Financial & Performance) -->
            <div id="breakdown-container" class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h5 class="font-bold text-gray-800 text-sm">Category & Payment Distribution</h5>
                        <p class="text-xs text-gray-400">Proportion of reservations and earnings by category</p>
                    </div>
                    <div id="breakdown-pills" class="flex flex-wrap gap-2">
                        <span class="px-3 py-1 bg-gray-50 text-gray-600 text-xs rounded-lg border border-gray-100">Loading metrics...</span>
                    </div>
                </div>
            </div>

            <!-- Report Results Table Container -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 bg-gray-50/50">
                    <div>
                        <h4 class="font-bold text-gray-800 text-base" id="report-table-title">Financial & Revenue Report Details</h4>
                        <p class="text-xs text-gray-500" id="report-table-subtitle">Showing all matching records within the selected timeframe</p>
                    </div>
                    <div class="flex items-center gap-3 text-xs text-gray-500">
                        <span id="record-count-badge" class="bg-white px-3 py-1 rounded-lg border border-gray-200 font-bold text-gray-700">0 Records</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[900px]">
                        <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100" id="report-table-head">
                            <tr>
                                <th class="p-4">Ref ID</th>
                                <th class="p-4">Guest Name</th>
                                <th class="p-4">Room & Type</th>
                                <th class="p-4">Stay Dates</th>
                                <th class="p-4">Nights</th>
                                <th class="p-4">Payment</th>
                                <th class="p-4">Amount</th>
                                <th class="p-4">Status</th>
                                <th class="p-4">Booked On</th>
                            </tr>
                        </thead>
                        <tbody id="report-table-body" class="text-sm divide-y divide-gray-100">
                            <tr>
                                <td colspan="9" class="p-12 text-center text-gray-400">
                                    <i class="fas fa-spinner fa-spin text-2xl mb-3 block text-brand-purple"></i>
                                    Generating report data...
                                </td>
                            </tr>
                        </tbody>
                        <tfoot id="report-table-foot" class="bg-gray-50/80 text-sm font-bold text-gray-800 border-t-2 border-gray-200">
                            <!-- Populated dynamically -->
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================= TAB 2: GUEST FEEDBACK & INQUIRIES ================= -->
        <div id="feedback-tab-content" class="report-tab-pane space-y-6 hidden">
            <div class="flex justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div>
                    <h4 class="font-bold text-gray-800 text-lg">Guest Inquiries & Feedback</h4>
                    <p class="text-sm text-gray-500 mt-1">Review feedback, reply to guests, and mark issues as resolved</p>
                </div>
                <button onclick="loadGuestFeedback()" class="bg-brand-blue text-white px-4 py-2 rounded-xl shadow hover:bg-sky-600 text-sm font-bold transition flex items-center gap-2">
                    <i class="fas fa-sync-alt"></i> Refresh Feedback
                </button>
            </div>
            
            <div id="reports-list" class="grid gap-4">
                <div class="text-center text-gray-400 py-12 bg-white rounded-2xl border border-gray-100">
                    <i class="fas fa-spinner fa-spin text-2xl mb-2 text-brand-purple block"></i>
                    Loading feedback...
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Guest Reply Modal -->
<div id="reply-modal" class="fixed inset-0 bg-black/50 z-[100] hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-800">Reply to Guest</h3>
            <button onclick="closeReplyModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <input type="hidden" id="reply-report-id">
        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Your Official Response</label>
        <textarea id="reply-message" rows="4" class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-brand-purple outline-none mb-4 text-sm" placeholder="Type your response to the guest..."></textarea>
        <div class="flex gap-2">
            <button onclick="submitReply()" class="flex-1 bg-brand-purple text-white py-2.5 rounded-xl font-bold hover:bg-purple-700 transition shadow">Send Reply</button>
            <button onclick="closeReplyModal()" class="flex-1 bg-gray-100 text-gray-600 py-2.5 rounded-xl font-bold hover:bg-gray-200 transition">Cancel</button>
        </div>
    </div>
</div>

<script>
/**
 * Enhanced Hotel Reports & Printing Script
 */
let currentReportData = null;
let currentReportType = 'financial';

// Format Philippine Peso
function formatCurrency(amount) {
    const num = parseFloat(amount) || 0;
    return '₱' + num.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// Switch between Reports and Feedback tabs
function switchReportTab(tabId) {
    document.querySelectorAll('.report-tab-btn').forEach(btn => {
        btn.classList.remove('bg-white', 'text-brand-purple', 'shadow-sm', 'font-bold');
        btn.classList.add('text-gray-600', 'font-medium');
    });

    document.querySelectorAll('.report-tab-pane').forEach(pane => {
        pane.classList.add('hidden');
    });

    if (tabId === 'reports-tab') {
        document.getElementById('tab-btn-reports').classList.add('bg-white', 'text-brand-purple', 'shadow-sm', 'font-bold');
        document.getElementById('tab-btn-reports').classList.remove('text-gray-600', 'font-medium');
        document.getElementById('reports-tab-content').classList.remove('hidden');
    } else {
        document.getElementById('tab-btn-feedback').classList.add('bg-white', 'text-brand-purple', 'shadow-sm', 'font-bold');
        document.getElementById('tab-btn-feedback').classList.remove('text-gray-600', 'font-medium');
        document.getElementById('feedback-tab-content').classList.remove('hidden');
        loadGuestFeedback();
    }
}

// Handle UI adjustments on Report Type change
function handleReportTypeChange() {
    const type = document.getElementById('hotel-report-type').value;
    currentReportType = type;
    
    const datePresetContainer = document.getElementById('date-preset-container');
    const statusContainer = document.getElementById('status-filter-container');
    const roomTypeContainer = document.getElementById('room-type-filter-container');
    const customDateRow = document.getElementById('custom-date-row');
    const breakdownContainer = document.getElementById('breakdown-container');

    if (type === 'daily_manifest') {
        // Daily Manifest works on a single target date
        statusContainer.classList.add('hidden');
        roomTypeContainer.classList.add('hidden');
        breakdownContainer.classList.add('hidden');
    } else if (type === 'room_performance') {
        statusContainer.classList.add('hidden');
        roomTypeContainer.classList.remove('hidden');
        breakdownContainer.classList.remove('hidden');
    } else if (type === 'feedback') {
        statusContainer.classList.add('hidden');
        roomTypeContainer.classList.add('hidden');
        breakdownContainer.classList.remove('hidden');
    } else {
        // financial & bookings
        statusContainer.classList.remove('hidden');
        roomTypeContainer.classList.remove('hidden');
        breakdownContainer.classList.remove('hidden');
    }

    handleDatePeriodChange();
    generateHotelReportData();
}

// Handle Custom Date Range toggle
function handleDatePeriodChange() {
    const period = document.getElementById('hotel-report-period').value;
    const customRow = document.getElementById('custom-date-row');
    const type = document.getElementById('hotel-report-type').value;

    if (period === 'custom' || type === 'daily_manifest') {
        customRow.classList.remove('hidden');
        if (!document.getElementById('hotel-report-from').value) {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('hotel-report-from').value = today;
            document.getElementById('hotel-report-to').value = today;
        }
    } else {
        customRow.classList.add('hidden');
    }
}

// Generate Report Data from API
function generateHotelReportData(forceRefresh = false) {
    const reportType = document.getElementById('hotel-report-type').value;
    const dateRange = document.getElementById('hotel-report-period').value;
    const fromDate = document.getElementById('hotel-report-from').value;
    const toDate = document.getElementById('hotel-report-to').value;
    const status = document.getElementById('hotel-report-status').value;
    const roomType = document.getElementById('hotel-report-room-type').value;
    const dateField = document.getElementById('hotel-report-date-field') ? document.getElementById('hotel-report-date-field').value : 'created_at';

    // Show loading state
    const tbody = document.getElementById('report-table-body');
    tbody.innerHTML = `
        <tr>
            <td colspan="10" class="p-12 text-center text-gray-400">
                <i class="fas fa-spinner fa-spin text-2xl mb-3 block text-brand-purple"></i>
                Fetching official hotel data...
            </td>
        </tr>
    `;

    const queryParams = new URLSearchParams({
        action: 'generate_report',
        report_type: reportType,
        date_range: dateRange,
        from_date: fromDate,
        to_date: toDate,
        status: status,
        room_type: roomType,
        date_field: dateField
    });

    fetch(`../actions/reports_action.php?${queryParams.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentReportData = data;
                renderReportView(data);
                if (forceRefresh && typeof showToast === 'function') {
                    showToast('Report updated with latest records', 'success');
                }
            } else {
                tbody.innerHTML = `<tr><td colspan="10" class="p-12 text-center text-red-500 font-medium">${data.message || 'Error loading report'}</td></tr>`;
            }
        })
        .catch(err => {
            console.error('Report API Error:', err);
            tbody.innerHTML = `<tr><td colspan="10" class="p-12 text-center text-red-500 font-medium">Failed to connect to reporting server. Please check database connection.</td></tr>`;
        });
}

// Render report data into UI
function renderReportView(data) {
    document.getElementById('report-ref-badge').innerText = `Ref: ${data.report_ref || 'RPT-READY'}`;
    document.getElementById('active-filter-summary').innerText = `Scope: ${data.date_range_label || 'Current Period'} • Ref: ${data.report_ref}`;

    if (data.report_type === 'daily_manifest') {
        renderManifestView(data);
    } else if (data.report_type === 'room_performance') {
        renderPerformanceView(data);
    } else if (data.report_type === 'feedback') {
        renderFeedbackReportView(data);
    } else {
        renderFinancialOrBookingsView(data);
    }
}

// 1. Render Financial & Bookings Register
function renderFinancialOrBookingsView(data) {
    const s = data.summary || {};
    
    // KPI Cards
    document.getElementById('kpi-1-label').innerText = 'Confirmed Revenue';
    document.getElementById('kpi-1-val').innerText = formatCurrency(s.confirmed_revenue);
    document.getElementById('kpi-1-sub').innerText = `Net Room: ${formatCurrency(s.net_room_revenue)}`;

    document.getElementById('kpi-2-label').innerText = 'Total Bookings';
    document.getElementById('kpi-2-val').innerText = s.total_bookings || 0;
    document.getElementById('kpi-2-sub').innerText = `${s.confirmed_bookings || 0} Confirmed • ${s.pending_bookings || 0} Pending`;

    document.getElementById('kpi-3-label').innerText = 'Nights Sold';
    document.getElementById('kpi-3-val').innerText = s.total_nights_sold || 0;
    document.getElementById('kpi-3-sub').innerText = `ADR: ${formatCurrency(s.average_daily_rate)} / night`;

    document.getElementById('kpi-4-label').innerText = 'Pending / Cancelled';
    document.getElementById('kpi-4-val').innerText = `${s.pending_bookings || 0} / ${s.cancelled_bookings || 0}`;
    document.getElementById('kpi-4-sub').innerText = `Pending Val: ${formatCurrency(s.pending_revenue)}`;

    // Breakdown Pills
    const pillsContainer = document.getElementById('breakdown-pills');
    let pillsHtml = '';

    if (data.room_type_summary) {
        for (const [type, info] of Object.entries(data.room_type_summary)) {
            pillsHtml += `<span class="px-3 py-1 bg-purple-50 text-brand-purple text-xs rounded-lg border border-purple-100 font-semibold">${type}: ${formatCurrency(info.revenue)} (${info.count} bks)</span>`;
        }
    }
    if (data.payment_summary) {
        for (const [method, info] of Object.entries(data.payment_summary)) {
            pillsHtml += `<span class="px-3 py-1 bg-blue-50 text-brand-blue text-xs rounded-lg border border-blue-100 font-semibold">${method}: ${formatCurrency(info.revenue)}</span>`;
        }
    }
    pillsContainer.innerHTML = pillsHtml || '<span class="text-xs text-gray-400">No category breakdown data available for this range</span>';

    // Titles
    document.getElementById('report-table-title').innerText = data.report_type === 'financial' ? 'Financial & Revenue Ledger' : 'Bookings & Reservations Register';
    document.getElementById('report-table-subtitle').innerText = `Period: ${data.date_range_label} • Showing ${data.rows ? data.rows.length : 0} bookings`;
    document.getElementById('record-count-badge').innerText = `${data.rows ? data.rows.length : 0} Records`;

    // Table Header
    document.getElementById('report-table-head').innerHTML = `
        <tr>
            <th class="p-4">Ref ID</th>
            <th class="p-4">Guest Name</th>
            <th class="p-4">Room & Type</th>
            <th class="p-4">Check-In</th>
            <th class="p-4">Check-Out</th>
            <th class="p-4">Nights</th>
            <th class="p-4">Payment</th>
            <th class="p-4">Amount</th>
            <th class="p-4">Status</th>
            <th class="p-4">Booked Date</th>
        </tr>
    `;

    // Table Body
    const tbody = document.getElementById('report-table-body');
    const rows = data.rows || [];

    if (rows.length === 0) {
        tbody.innerHTML = `<tr><td colspan="10" class="p-12 text-center text-gray-400">No reservations found matching the selected parameters.</td></tr>`;
    } else {
        tbody.innerHTML = rows.map(b => {
            const statusClass = b.status === 'Confirmed' ? 'bg-green-100 text-green-700' : (b.status === 'Cancelled' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700');
            const checkInFormatted = new Date(b.check_in).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            const checkOutFormatted = new Date(b.check_out).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            const createdFormatted = b.created_at ? new Date(b.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '-';

            return `
                <tr class="hover:bg-gray-50/80 transition">
                    <td class="p-4 font-mono font-bold text-gray-600 text-xs">${b.ref_id}</td>
                    <td class="p-4">
                        <div class="font-bold text-gray-800">${b.final_guest_name}</div>
                        <div class="text-xs text-gray-400">${b.guest_email || ''}</div>
                    </td>
                    <td class="p-4">
                        <span class="font-medium text-gray-800">${b.room_name || 'Room'}</span>
                        <span class="block text-xs text-gray-400">${b.room_type || 'Standard'}</span>
                    </td>
                    <td class="p-4 text-gray-600">${checkInFormatted}</td>
                    <td class="p-4 text-gray-600">${checkOutFormatted}</td>
                    <td class="p-4 text-center font-medium">${b.nights || 1}</td>
                    <td class="p-4 text-xs font-medium text-gray-600">${b.payment_method || 'Cash / Hotel'}</td>
                    <td class="p-4 font-bold text-brand-dark">${formatCurrency(b.total_price)}</td>
                    <td class="p-4"><span class="px-2.5 py-1 rounded-full text-xs font-bold ${statusClass}">${b.status}</span></td>
                    <td class="p-4 text-xs text-gray-500">${createdFormatted}</td>
                </tr>
            `;
        }).join('');
    }

    // Footer Totals
    document.getElementById('report-table-foot').innerHTML = `
        <tr>
            <td colspan="5" class="p-4 text-right">Confirmed Total:</td>
            <td class="p-4 text-center">${s.total_nights_sold || 0} nts</td>
            <td class="p-4"></td>
            <td class="p-4 text-brand-purple text-base">${formatCurrency(s.confirmed_revenue)}</td>
            <td colspan="2" class="p-4 text-xs text-gray-500 font-normal">Taxes/Fees Included: ${formatCurrency(s.tax_and_fees)}</td>
        </tr>
    `;
}

// 2. Render Daily Front Desk Manifest
function renderManifestView(data) {
    const s = data.summary || {};
    
    // KPI Cards
    document.getElementById('kpi-1-label').innerText = 'Expected Arrivals';
    document.getElementById('kpi-1-val').innerText = s.expected_arrivals || 0;
    document.getElementById('kpi-1-sub').innerText = 'Check-ins today';

    document.getElementById('kpi-2-label').innerText = 'Expected Departures';
    document.getElementById('kpi-2-val').innerText = s.expected_departures || 0;
    document.getElementById('kpi-2-sub').innerText = 'Check-outs today';

    document.getElementById('kpi-3-label').innerText = 'In-House Guests';
    document.getElementById('kpi-3-val').innerText = s.in_house_guests || 0;
    document.getElementById('kpi-3-sub').innerText = 'Staying overnight';

    document.getElementById('kpi-4-label').innerText = 'Estimated Occupancy';
    document.getElementById('kpi-4-val').innerText = `${s.estimated_occupancy_rate || 0}%`;
    document.getElementById('kpi-4-sub').innerText = `${s.total_active_rooms || 0} active rooms`;

    document.getElementById('report-table-title').innerText = `Daily Front Desk Operations Manifest`;
    document.getElementById('report-table-subtitle').innerText = `Date: ${data.target_date_formatted || data.target_date} • Operational Room & Guest Schedule`;

    const arrivals = data.arrivals || [];
    const departures = data.departures || [];
    const inHouse = data.in_house || [];

    document.getElementById('record-count-badge').innerText = `${arrivals.length + departures.length + inHouse.length} Total Movements`;

    document.getElementById('report-table-head').innerHTML = `
        <tr>
            <th class="p-4">Activity Type</th>
            <th class="p-4">Ref ID</th>
            <th class="p-4">Guest Name</th>
            <th class="p-4">Room & Type</th>
            <th class="p-4">Stay Duration</th>
            <th class="p-4">Payment Method</th>
            <th class="p-4">Total Amount</th>
            <th class="p-4">Status</th>
        </tr>
    `;

    let rowsHtml = '';

    if (arrivals.length === 0 && departures.length === 0 && inHouse.length === 0) {
        rowsHtml = `<tr><td colspan="8" class="p-12 text-center text-gray-400">No scheduled arrivals, departures, or staying guests on this date.</td></tr>`;
    } else {
        // Arrivals Section
        if (arrivals.length > 0) {
            rowsHtml += `<tr class="bg-blue-50/70 font-bold text-brand-blue text-xs uppercase"><td colspan="8" class="p-3"><i class="fas fa-plane-arrival mr-2"></i> Expected Arrivals / Check-Ins (${arrivals.length})</td></tr>`;
            arrivals.forEach(b => {
                rowsHtml += renderManifestRow(b, 'Arrival (Check-in)', 'text-blue-600 bg-blue-50');
            });
        }

        // Departures Section
        if (departures.length > 0) {
            rowsHtml += `<tr class="bg-orange-50/70 font-bold text-orange-700 text-xs uppercase"><td colspan="8" class="p-3"><i class="fas fa-plane-departure mr-2"></i> Expected Departures / Check-Outs (${departures.length})</td></tr>`;
            departures.forEach(b => {
                rowsHtml += renderManifestRow(b, 'Departure (Check-out)', 'text-orange-600 bg-orange-50');
            });
        }

        // In-House Section
        if (inHouse.length > 0) {
            rowsHtml += `<tr class="bg-emerald-50/70 font-bold text-emerald-700 text-xs uppercase"><td colspan="8" class="p-3"><i class="fas fa-house-user mr-2"></i> Current In-House Guests (${inHouse.length})</td></tr>`;
            inHouse.forEach(b => {
                rowsHtml += renderManifestRow(b, 'In-House Stay', 'text-emerald-600 bg-emerald-50');
            });
        }
    }

    document.getElementById('report-table-body').innerHTML = rowsHtml;
    document.getElementById('report-table-foot').innerHTML = '';
}

function renderManifestRow(b, typeLabel, badgeClass) {
    const statusClass = b.status === 'Confirmed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700';
    return `
        <tr class="hover:bg-gray-50/80 transition">
            <td class="p-4"><span class="px-2.5 py-1 rounded-lg text-xs font-bold ${badgeClass}">${typeLabel}</span></td>
            <td class="p-4 font-mono font-bold text-gray-600 text-xs">${b.ref_id}</td>
            <td class="p-4">
                <div class="font-bold text-gray-800">${b.final_guest_name}</div>
                <div class="text-xs text-gray-400">${b.guest_email || ''}</div>
            </td>
            <td class="p-4 font-medium text-gray-800">${b.room_name} <span class="text-xs text-gray-400 font-normal">(${b.room_type})</span></td>
            <td class="p-4 text-xs text-gray-600">${b.check_in} to ${b.check_out} (${b.nights} nts)</td>
            <td class="p-4 text-xs text-gray-600 font-medium">${b.payment_method || 'Cash / Desk'}</td>
            <td class="p-4 font-bold text-brand-dark">${formatCurrency(b.total_price)}</td>
            <td class="p-4"><span class="px-2 py-0.5 rounded text-xs font-bold ${statusClass}">${b.status}</span></td>
        </tr>
    `;
}

// 3. Render Room Performance & Occupancy
function renderPerformanceView(data) {
    const s = data.summary || {};
    
    document.getElementById('kpi-1-label').innerText = 'Hotel Room Revenue';
    document.getElementById('kpi-1-val').innerText = formatCurrency(s.total_revenue);
    document.getElementById('kpi-1-sub').innerText = `Avg/Room: ${formatCurrency(s.average_revenue_per_room)}`;

    document.getElementById('kpi-2-label').innerText = 'Total Reservations';
    document.getElementById('kpi-2-val').innerText = s.total_confirmed_bookings || 0;
    document.getElementById('kpi-2-sub').innerText = `Across ${s.total_rooms || 0} Rooms`;

    document.getElementById('kpi-3-label').innerText = 'Total Nights Sold';
    document.getElementById('kpi-3-val').innerText = s.total_nights_sold || 0;
    document.getElementById('kpi-3-sub').innerText = `In ${data.days_in_period || 30} Days Period`;

    document.getElementById('kpi-4-label').innerText = 'Active Inventory';
    document.getElementById('kpi-4-val').innerText = s.total_rooms || 0;
    document.getElementById('kpi-4-sub').innerText = 'Total Managed Rooms';

    document.getElementById('report-table-title').innerText = 'Room Occupancy & Utilization Performance';
    document.getElementById('report-table-subtitle').innerText = `Period: ${data.date_range_label} (${data.days_in_period} days)`;

    const rooms = data.rooms_performance || [];
    document.getElementById('record-count-badge').innerText = `${rooms.length} Rooms`;

    document.getElementById('report-table-head').innerHTML = `
        <tr>
            <th class="p-4">Room Name</th>
            <th class="p-4">Type</th>
            <th class="p-4">Base Rate</th>
            <th class="p-4">Capacity</th>
            <th class="p-4">Total Bookings</th>
            <th class="p-4">Nights Sold</th>
            <th class="p-4">Est. Occupancy</th>
            <th class="p-4">Revenue Generated</th>
            <th class="p-4">Status</th>
        </tr>
    `;

    const tbody = document.getElementById('report-table-body');
    if (rooms.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="p-12 text-center text-gray-400">No room inventory found.</td></tr>`;
    } else {
        tbody.innerHTML = rooms.map(r => `
            <tr class="hover:bg-gray-50/80 transition">
                <td class="p-4 font-bold text-gray-800">${r.name}</td>
                <td class="p-4"><span class="px-2 py-0.5 bg-purple-50 text-brand-purple text-xs font-semibold rounded">${r.type}</span></td>
                <td class="p-4 font-medium text-gray-700">${formatCurrency(r.price)} / nt</td>
                <td class="p-4 text-gray-600 text-xs">${r.capacity} Guests</td>
                <td class="p-4 font-bold text-gray-800">${r.total_bookings}</td>
                <td class="p-4 font-bold text-brand-blue">${r.nights_sold} nights</td>
                <td class="p-4">
                    <div class="flex items-center gap-2">
                        <div class="w-16 bg-gray-200 rounded-full h-2">
                            <div class="bg-brand-purple h-2 rounded-full" style="width: ${Math.min(100, r.occupancy_rate)}%"></div>
                        </div>
                        <span class="text-xs font-bold text-gray-700">${r.occupancy_rate}%</span>
                    </div>
                </td>
                <td class="p-4 font-bold text-emerald-700 text-base">${formatCurrency(r.revenue_generated)}</td>
                <td class="p-4"><span class="px-2 py-0.5 rounded text-xs font-bold ${r.status === 'Maintenance' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'}">${r.status}</span></td>
            </tr>
        `).join('');
    }

    document.getElementById('report-table-foot').innerHTML = `
        <tr>
            <td colspan="4" class="p-4 text-right font-bold">Total Performance:</td>
            <td class="p-4 font-bold">${s.total_confirmed_bookings || 0}</td>
            <td class="p-4 font-bold text-brand-blue">${s.total_nights_sold || 0} nts</td>
            <td class="p-4"></td>
            <td class="p-4 font-bold text-emerald-700 text-base">${formatCurrency(s.total_revenue)}</td>
            <td class="p-4"></td>
        </tr>
    `;
}

// 4. Render Guest Feedback & Reviews Report
function renderFeedbackReportView(data) {
    const s = data.summary || {};
    
    document.getElementById('kpi-1-label').innerText = 'Average Rating';
    document.getElementById('kpi-1-val').innerText = `${s.average_rating || 5.0} / 5.0`;
    document.getElementById('kpi-1-sub').innerText = `Across ${s.total_reviews || 0} Verified Reviews`;

    document.getElementById('kpi-2-label').innerText = 'Satisfaction Rate';
    document.getElementById('kpi-2-val').innerText = `${s.satisfaction_rate || 100}%`;
    document.getElementById('kpi-2-sub').innerText = '4★ & 5★ Ratings';

    document.getElementById('kpi-3-label').innerText = 'Total Feedback Tickets';
    document.getElementById('kpi-3-val').innerText = s.total_feedback || 0;
    document.getElementById('kpi-3-sub').innerText = `${s.resolved_feedback || 0} Resolved`;

    document.getElementById('kpi-4-label').innerText = 'Pending Inquiries';
    document.getElementById('kpi-4-val').innerText = s.pending_feedback || 0;
    document.getElementById('kpi-4-sub').innerText = 'Awaiting staff response';

    document.getElementById('report-table-title').innerText = 'Guest Satisfaction & Reviews Register';
    document.getElementById('report-table-subtitle').innerText = 'Recent guest ratings, feedback submissions, and management replies';

    const reviews = data.reviews || [];
    document.getElementById('record-count-badge').innerText = `${reviews.length} Reviews`;

    document.getElementById('report-table-head').innerHTML = `
        <tr>
            <th class="p-4">Guest</th>
            <th class="p-4">Room</th>
            <th class="p-4">Rating</th>
            <th class="p-4">Feedback / Comment</th>
            <th class="p-4">Admin Reply</th>
            <th class="p-4">Date</th>
        </tr>
    `;

    const tbody = document.getElementById('report-table-body');
    if (reviews.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="p-12 text-center text-gray-400">No guest reviews recorded yet.</td></tr>`;
    } else {
        tbody.innerHTML = reviews.map(r => `
            <tr class="hover:bg-gray-50/80 transition">
                <td class="p-4">
                    <div class="font-bold text-gray-800">${r.user_name || 'Guest'}</div>
                    <div class="text-xs text-gray-400">${r.user_email || ''}</div>
                </td>
                <td class="p-4 font-medium text-gray-700">${r.room_name || 'General'}</td>
                <td class="p-4">
                    <span class="text-yellow-500 font-bold">${'★'.repeat(Math.round(r.rating || 5))}</span>
                    <span class="text-xs text-gray-400 font-semibold ml-1">(${r.rating})</span>
                </td>
                <td class="p-4 text-sm text-gray-700 max-w-xs">${r.comment || 'No comment provided'}</td>
                <td class="p-4 text-xs text-gray-600">${r.admin_reply ? `<span class="bg-blue-50 text-blue-800 p-2 rounded block border border-blue-100">${r.admin_reply}</span>` : '<span class="text-gray-400 italic">No reply yet</span>'}</td>
                <td class="p-4 text-xs text-gray-500">${r.created_at ? new Date(r.created_at).toLocaleDateString() : '-'}</td>
            </tr>
        `).join('');
    }

    document.getElementById('report-table-foot').innerHTML = '';
}

/**
 * Professional Hotel Printable Report Generator
 * Opens a clean, branded, multi-page print document ready for physical printing or PDF export
 */
function printHotelReport() {
    if (!currentReportData) {
        showToast('Please generate report data first', 'warning');
        return;
    }

    const d = currentReportData;
    const type = d.report_type || 'financial';
    const s = d.summary || {};
    const hotelName = "W Island Hub Backpackers Inn";
    const hotelAddress = "Paradise Island, Philippines • Contact: info@wislandhub.com • +63 912 345 6789";
    const reportRef = d.report_ref || 'RPT-OFFICIAL';
    const generatedDate = d.generated_at_formatted || new Date().toLocaleString();
    const datePeriod = d.date_range_label || 'Current Period';

    let reportTitle = "HOTEL FINANCIAL & REVENUE SUMMARY REPORT";
    if (type === 'bookings') reportTitle = "HOTEL RESERVATIONS & BOOKINGS REGISTER";
    else if (type === 'daily_manifest') reportTitle = "FRONT DESK DAILY OPERATIONS MANIFEST";
    else if (type === 'room_performance') reportTitle = "ROOM OCCUPANCY & UTILIZATION PERFORMANCE REPORT";
    else if (type === 'feedback') reportTitle = "GUEST SATISFACTION & REVIEWS REPORT";

    // Build KPI Summary Cards HTML for Print
    let kpiPrintHtml = `
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 20px;">
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                <div style="font-size: 10px; text-transform: uppercase; color: #64748b; font-weight: bold;">Confirmed Revenue</div>
                <div style="font-size: 18px; font-weight: bold; color: #0f172a; margin-top: 4px;">${formatCurrency(s.confirmed_revenue || s.total_revenue || 0)}</div>
                <div style="font-size: 10px; color: #64748b; margin-top: 2px;">Net: ${formatCurrency(s.net_room_revenue || s.total_revenue || 0)}</div>
            </div>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                <div style="font-size: 10px; text-transform: uppercase; color: #64748b; font-weight: bold;">Total Bookings</div>
                <div style="font-size: 18px; font-weight: bold; color: #0f172a; margin-top: 4px;">${s.total_bookings || s.total_confirmed_bookings || (d.arrivals ? (d.arrivals.length + d.departures.length) : 0)}</div>
                <div style="font-size: 10px; color: #16a34a; margin-top: 2px;">${s.confirmed_bookings || s.total_confirmed_bookings || 0} Confirmed</div>
            </div>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                <div style="font-size: 10px; text-transform: uppercase; color: #64748b; font-weight: bold;">Guest Nights Sold</div>
                <div style="font-size: 18px; font-weight: bold; color: #0f172a; margin-top: 4px;">${s.total_nights_sold || 0}</div>
                <div style="font-size: 10px; color: #64748b; margin-top: 2px;">ADR: ${formatCurrency(s.average_daily_rate || 0)} / nt</div>
            </div>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                <div style="font-size: 10px; text-transform: uppercase; color: #64748b; font-weight: bold;">Pending / Cancelled</div>
                <div style="font-size: 18px; font-weight: bold; color: #d97706; margin-top: 4px;">${s.pending_bookings || 0} / ${s.cancelled_bookings || 0}</div>
                <div style="font-size: 10px; color: #64748b; margin-top: 2px;">Value: ${formatCurrency(s.pending_revenue || 0)}</div>
            </div>
        </div>
    `;

    // Build Table Rows HTML for Print
    let tableHeadersHtml = '';
    let tableRowsHtml = '';
    let tableFootHtml = '';

    if (type === 'daily_manifest') {
        tableHeadersHtml = `
            <tr>
                <th style="padding: 8px; border: 1px solid #cbd5e1; background: #f1f5f9; text-align: left;">Activity</th>
                <th style="padding: 8px; border: 1px solid #cbd5e1; background: #f1f5f9; text-align: left;">Ref ID</th>
                <th style="padding: 8px; border: 1px solid #cbd5e1; background: #f1f5f9; text-align: left;">Guest Name</th>
                <th style="padding: 8px; border: 1px solid #cbd5e1; background: #f1f5f9; text-align: left;">Room</th>
                <th style="padding: 8px; border: 1px solid #cbd5e1; background: #f1f5f9; text-align: left;">Dates</th>
                <th style="padding: 8px; border: 1px solid #cbd5e1; background: #f1f5f9; text-align: left;">Payment</th>
                <th style="padding: 8px; border: 1px solid #cbd5e1; background: #f1f5f9; text-align: right;">Amount</th>
                <th style="padding: 8px; border: 1px solid #cbd5e1; background: #f1f5f9; text-align: center;">Status</th>
            </tr>
        `;

        const allManifest = [
            ...(d.arrivals || []).map(x => ({ ...x, _act: 'Arrival (Check-in)' })),
            ...(d.departures || []).map(x => ({ ...x, _act: 'Departure (Check-out)' })),
            ...(d.in_house || []).map(x => ({ ...x, _act: 'In-House Stay' }))
        ];

        if (allManifest.length === 0) {
            tableRowsHtml = `<tr><td colspan="8" style="padding: 16px; text-align: center; border: 1px solid #cbd5e1; color: #64748b;">No scheduled guest activity for this date.</td></tr>`;
        } else {
            tableRowsHtml = allManifest.map(b => `
                <tr>
                    <td style="padding: 6px 8px; border: 1px solid #cbd5e1; font-weight: bold; font-size: 11px;">${b._act}</td>
                    <td style="padding: 6px 8px; border: 1px solid #cbd5e1; font-family: monospace; font-size: 11px;">${b.ref_id}</td>
                    <td style="padding: 6px 8px; border: 1px solid #cbd5e1; font-weight: 600;">${b.final_guest_name}</td>
                    <td style="padding: 6px 8px; border: 1px solid #cbd5e1;">${b.room_name} (${b.room_type})</td>
                    <td style="padding: 6px 8px; border: 1px solid #cbd5e1; font-size: 11px;">${b.check_in} to ${b.check_out}</td>
                    <td style="padding: 6px 8px; border: 1px solid #cbd5e1; font-size: 11px;">${b.payment_method || 'Desk'}</td>
                    <td style="padding: 6px 8px; border: 1px solid #cbd5e1; text-align: right; font-weight: bold;">${formatCurrency(b.total_price)}</td>
                    <td style="padding: 6px 8px; border: 1px solid #cbd5e1; text-align: center; font-size: 11px; font-weight: bold;">${b.status}</td>
                </tr>
            `).join('');
        }
    } else if (type === 'room_performance') {
        tableHeadersHtml = `
            <tr>
                <th style="padding: 8px; border: 1px solid #cbd5e1; background: #f1f5f9; text-align: left;">Room Name</th>
                <th style="padding: 8px; border: 1px solid #cbd5e1; background: #f1f5f9; text-align: left;">Type</th>
                <th style="padding: 8px; border: 1px solid #cbd5e1; background: #f1f5f9; text-align: right;">Base Rate</th>
                <th style="padding: 8px; border: 1px solid #cbd5e1; background: #f1f5f9; text-align: center;">Bookings</th>
                <th style="padding: 8px; border: 1px solid #cbd5e1; background: #f1f5f9; text-align: center;">Nights Sold</th>
                <th style="padding: 8px; border: 1px solid #cbd5e1; background: #f1f5f9; text-align: center;">Occupancy %</th>
                <th style="padding: 8px; border: 1px solid #cbd5e1; background: #f1f5f9; text-align: right;">Revenue Generated</th>
                <th style="padding: 8px; border: 1px solid #cbd5e1; background: #f1f5f9; text-align: center;">Status</th>
            </tr>
        `;

        const rooms = d.rooms_performance || [];
        tableRowsHtml = rooms.map(r => `
            <tr>
                <td style="padding: 6px 8px; border: 1px solid #cbd5e1; font-weight: bold;">${r.name}</td>
                <td style="padding: 6px 8px; border: 1px solid #cbd5e1;">${r.type}</td>
                <td style="padding: 6px 8px; border: 1px solid #cbd5e1; text-align: right;">${formatCurrency(r.price)}</td>
                <td style="padding: 6px 8px; border: 1px solid #cbd5e1; text-align: center; font-weight: bold;">${r.total_bookings}</td>
                <td style="padding: 6px 8px; border: 1px solid #cbd5e1; text-align: center; font-weight: bold;">${r.nights_sold}</td>
                <td style="padding: 6px 8px; border: 1px solid #cbd5e1; text-align: center; font-weight: bold;">${r.occupancy_rate}%</td>
                <td style="padding: 6px 8px; border: 1px solid #cbd5e1; text-align: right; font-weight: bold; color: #0f172a;">${formatCurrency(r.revenue_generated)}</td>
                <td style="padding: 6px 8px; border: 1px solid #cbd5e1; text-align: center;">${r.status}</td>
            </tr>
        `).join('');

        tableFootHtml = `
            <tr style="background: #f8fafc; font-weight: bold;">
                <td colspan="3" style="padding: 8px; border: 1px solid #cbd5e1; text-align: right;">TOTAL:</td>
                <td style="padding: 8px; border: 1px solid #cbd5e1; text-align: center;">${s.total_confirmed_bookings || 0}</td>
                <td style="padding: 8px; border: 1px solid #cbd5e1; text-align: center;">${s.total_nights_sold || 0}</td>
                <td style="padding: 8px; border: 1px solid #cbd5e1;"></td>
                <td style="padding: 8px; border: 1px solid #cbd5e1; text-align: right; font-size: 14px;">${formatCurrency(s.total_revenue)}</td>
                <td style="padding: 8px; border: 1px solid #cbd5e1;"></td>
            </tr>
        `;
    } else {
        // Financial / Bookings Register
        tableHeadersHtml = `
            <tr>
                <th style="padding: 8px; border: 1px solid #cbd5e1; background: #f1f5f9; text-align: left;">Ref ID</th>
                <th style="padding: 8px; border: 1px solid #cbd5e1; background: #f1f5f9; text-align: left;">Guest Name</th>
                <th style="padding: 8px; border: 1px solid #cbd5e1; background: #f1f5f9; text-align: left;">Room</th>
                <th style="padding: 8px; border: 1px solid #cbd5e1; background: #f1f5f9; text-align: left;">Check-In</th>
                <th style="padding: 8px; border: 1px solid #cbd5e1; background: #f1f5f9; text-align: left;">Check-Out</th>
                <th style="padding: 8px; border: 1px solid #cbd5e1; background: #f1f5f9; text-align: center;">Nts</th>
                <th style="padding: 8px; border: 1px solid #cbd5e1; background: #f1f5f9; text-align: left;">Payment</th>
                <th style="padding: 8px; border: 1px solid #cbd5e1; background: #f1f5f9; text-align: right;">Total Price</th>
                <th style="padding: 8px; border: 1px solid #cbd5e1; background: #f1f5f9; text-align: center;">Status</th>
            </tr>
        `;

        const rows = d.rows || [];
        if (rows.length === 0) {
            tableRowsHtml = `<tr><td colspan="9" style="padding: 16px; text-align: center; border: 1px solid #cbd5e1; color: #64748b;">No booking transactions found.</td></tr>`;
        } else {
            tableRowsHtml = rows.map(b => `
                <tr>
                    <td style="padding: 6px 8px; border: 1px solid #cbd5e1; font-family: monospace; font-size: 11px;">${b.ref_id}</td>
                    <td style="padding: 6px 8px; border: 1px solid #cbd5e1; font-weight: 600;">${b.final_guest_name}</td>
                    <td style="padding: 6px 8px; border: 1px solid #cbd5e1;">${b.room_name} (${b.room_type})</td>
                    <td style="padding: 6px 8px; border: 1px solid #cbd5e1; font-size: 11px;">${b.check_in}</td>
                    <td style="padding: 6px 8px; border: 1px solid #cbd5e1; font-size: 11px;">${b.check_out}</td>
                    <td style="padding: 6px 8px; border: 1px solid #cbd5e1; text-align: center;">${b.nights}</td>
                    <td style="padding: 6px 8px; border: 1px solid #cbd5e1; font-size: 11px;">${b.payment_method || 'Desk'}</td>
                    <td style="padding: 6px 8px; border: 1px solid #cbd5e1; text-align: right; font-weight: bold;">${formatCurrency(b.total_price)}</td>
                    <td style="padding: 6px 8px; border: 1px solid #cbd5e1; text-align: center; font-weight: bold; font-size: 11px;">${b.status}</td>
                </tr>
            `).join('');
        }

        tableFootHtml = `
            <tr style="background: #f8fafc; font-weight: bold;">
                <td colspan="5" style="padding: 8px; border: 1px solid #cbd5e1; text-align: right;">GRAND TOTAL CONFIRMED:</td>
                <td style="padding: 8px; border: 1px solid #cbd5e1; text-align: center;">${s.total_nights_sold || 0}</td>
                <td style="padding: 8px; border: 1px solid #cbd5e1;"></td>
                <td style="padding: 8px; border: 1px solid #cbd5e1; text-align: right; font-size: 14px; color: #4338ca;">${formatCurrency(s.confirmed_revenue)}</td>
                <td style="padding: 8px; border: 1px solid #cbd5e1; text-align: center; font-size: 11px;">${s.confirmed_bookings || 0} Bks</td>
            </tr>
        `;
    }

    const printWindow = window.open('', '_blank');
    if (!printWindow) {
        alert('Please allow popups to print report');
        return;
    }

    const printDocument = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>${reportTitle} - ${reportRef}</title>
            <meta charset="utf-8">
            <style>
                @page { size: portrait; margin: 12mm 15mm; }
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
                    color: #1e293b;
                    background: #fff;
                    margin: 0;
                    padding: 20px;
                    font-size: 12px;
                    line-height: 1.4;
                }
                .header-container {
                    display: flex;
                    justify-content: space-between;
                    align-items: flex-start;
                    border-bottom: 2px solid #0f172a;
                    padding-bottom: 12px;
                    margin-bottom: 16px;
                }
                .hotel-title {
                    font-size: 22px;
                    font-weight: 800;
                    color: #0f172a;
                    letter-spacing: -0.5px;
                }
                .hotel-subtitle {
                    font-size: 11px;
                    color: #64748b;
                    margin-top: 2px;
                }
                .report-meta {
                    text-align: right;
                    font-size: 11px;
                }
                .report-badge {
                    display: inline-block;
                    background: #4338ca;
                    color: #fff;
                    font-weight: bold;
                    font-size: 11px;
                    padding: 3px 8px;
                    border-radius: 4px;
                    margin-bottom: 4px;
                }
                .report-title-strip {
                    background: #f1f5f9;
                    padding: 10px 14px;
                    border-radius: 6px;
                    font-weight: bold;
                    font-size: 14px;
                    color: #0f172a;
                    margin-bottom: 16px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    border-left: 4px solid #4338ca;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }
                tr { page-break-inside: avoid; }
                .sign-off-section {
                    margin-top: 35px;
                    display: grid;
                    grid-template-columns: repeat(2, 1fr);
                    gap: 40px;
                    page-break-inside: avoid;
                }
                .signature-box {
                    border-top: 1px solid #94a3b8;
                    padding-top: 6px;
                    font-size: 11px;
                }
                .no-print {
                    margin-bottom: 20px;
                    padding: 12px;
                    background: #f8fafc;
                    border: 1px solid #e2e8f0;
                    border-radius: 8px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .print-btn {
                    background: #2563eb;
                    color: #fff;
                    border: none;
                    padding: 8px 16px;
                    border-radius: 6px;
                    font-weight: bold;
                    cursor: pointer;
                }
                @media print {
                    .no-print { display: none !important; }
                    body { padding: 0; }
                }
            </style>
        </head>
        <body>
            <div class="no-print">
                <div>
                    <strong>Print Preview</strong> - Ready for official hotel record archiving or PDF export.
                </div>
                <button class="print-btn" onclick="window.print()">Print Document</button>
            </div>

            <!-- Hotel Branding Header -->
            <div class="header-container">
                <div>
                    <div class="hotel-title">${hotelName}</div>
                    <div class="hotel-subtitle">${hotelAddress}</div>
                </div>
                <div class="report-meta">
                    <div class="report-badge">OFFICIAL REPORT</div>
                    <div><strong>Ref #:</strong> ${reportRef}</div>
                    <div><strong>Generated:</strong> ${generatedDate}</div>
                    <div><strong>Prepared By:</strong> Super Admin</div>
                </div>
            </div>

            <!-- Title Strip -->
            <div class="report-title-strip">
                <span>${reportTitle}</span>
                <span style="font-size: 12px; color: #475569; font-weight: normal;">Period: <strong>${datePeriod}</strong></span>
            </div>

            <!-- Summary KPI Grid -->
            ${kpiPrintHtml}

            <!-- Data Table -->
            <table>
                <thead>
                    ${tableHeadersHtml}
                </thead>
                <tbody>
                    ${tableRowsHtml}
                </tbody>
                <tfoot>
                    ${tableFootHtml}
                </tfoot>
            </table>

            <!-- Sign-Off & Verification Block -->
            <div class="sign-off-section">
                <div>
                    <div style="height: 40px;"></div>
                    <div class="signature-box">
                        <strong>Prepared By:</strong> Admin Staff / General Reception<br>
                        <span>W Island Hub Backpackers Inn Operations</span>
                    </div>
                </div>
                <div>
                    <div style="height: 40px;"></div>
                    <div class="signature-box">
                        <strong>Verified & Approved By:</strong> Hotel Manager / Owner<br>
                        <span>Date & Official Stamp</span>
                    </div>
                </div>
            </div>

            <div style="text-align: center; font-size: 10px; color: #94a3b8; margin-top: 25px; border-top: 1px dashed #e2e8f0; padding-top: 8px;">
                CONFIDENTIAL • Generated for internal administration of W Island Hub Backpackers Inn.
            </div>

            <script>
                // Auto trigger print dialog on load
                window.onload = function() {
                    setTimeout(function() { window.print(); }, 500);
                };
            <\/script>
        </body>
        </html>
    `;

    printWindow.document.open();
    printWindow.document.write(printDocument);
    printWindow.document.close();
}

/**
 * Export Current Report Data to CSV format
 */
function exportReportCSV() {
    if (!currentReportData) {
        showToast('Please generate report data first', 'warning');
        return;
    }

    const d = currentReportData;
    const type = d.report_type || 'financial';
    let csvRows = [];

    // Header Meta
    csvRows.push([`"W Island Hub Backpackers Inn - Official Report"`]);
    csvRows.push([`"Report Type"`, `"${type.toUpperCase()}"`]);
    csvRows.push([`"Reference #"`, `"${d.report_ref || ''}"`]);
    csvRows.push([`"Period"`, `"${d.date_range_label || ''}"`]);
    csvRows.push([`"Generated At"`, `"${d.generated_at || ''}"`]);
    csvRows.push([]); // Empty row

    if (type === 'daily_manifest') {
        csvRows.push([`"Activity"`, `"Ref ID"`, `"Guest Name"`, `"Email"`, `"Room"`, `"Type"`, `"Check-In"`, `"Check-Out"`, `"Nights"`, `"Payment"`, `"Total Price"`, `"Status"`]);
        
        const allManifest = [
            ...(d.arrivals || []).map(x => ({ ...x, _act: 'Arrival' })),
            ...(d.departures || []).map(x => ({ ...x, _act: 'Departure' })),
            ...(d.in_house || []).map(x => ({ ...x, _act: 'In-House' }))
        ];

        allManifest.forEach(b => {
            csvRows.push([
                `"${b._act}"`,
                `"${b.ref_id}"`,
                `"${b.final_guest_name}"`,
                `"${b.guest_email || ''}"`,
                `"${b.room_name}"`,
                `"${b.room_type}"`,
                `"${b.check_in}"`,
                `"${b.check_out}"`,
                b.nights,
                `"${b.payment_method || 'Desk'}"`,
                parseFloat(b.total_price).toFixed(2),
                `"${b.status}"`
            ]);
        });
    } else if (type === 'room_performance') {
        csvRows.push([`"Room Name"`, `"Type"`, `"Base Price"`, `"Capacity"`, `"Total Bookings"`, `"Nights Sold"`, `"Occupancy Rate %"`, `"Revenue Generated"`, `"Status"`]);
        (d.rooms_performance || []).forEach(r => {
            csvRows.push([
                `"${r.name}"`,
                `"${r.type}"`,
                parseFloat(r.price).toFixed(2),
                r.capacity,
                r.total_bookings,
                r.nights_sold,
                `${r.occupancy_rate}%`,
                parseFloat(r.revenue_generated).toFixed(2),
                `"${r.status}"`
            ]);
        });
    } else {
        csvRows.push([`"Ref ID"`, `"Guest Name"`, `"Email"`, `"Room Name"`, `"Room Type"`, `"Check-In"`, `"Check-Out"`, `"Nights"`, `"Payment Method"`, `"Total Price"`, `"Status"`, `"Booked Date"`]);
        (d.rows || []).forEach(b => {
            csvRows.push([
                `"${b.ref_id}"`,
                `"${b.final_guest_name}"`,
                `"${b.guest_email || ''}"`,
                `"${b.room_name || ''}"`,
                `"${b.room_type || ''}"`,
                `"${b.check_in}"`,
                `"${b.check_out}"`,
                b.nights || 1,
                `"${b.payment_method || 'Desk'}"`,
                parseFloat(b.total_price).toFixed(2),
                `"${b.status}"`,
                `"${b.created_at || ''}"`
            ]);
        });
    }

    const csvContent = "\uFEFF" + csvRows.map(e => e.join(",")).join("\n");
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.setAttribute("href", url);
    link.setAttribute("download", `hotel_report_${type}_${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    showToast('CSV report downloaded successfully', 'success');
}

/**
 * Guest Feedback Tab Methods
 */
function loadGuestFeedback() {
    fetch('../actions/reports_action.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('reports-list');
            const badge = document.getElementById('feedback-badge');
            
            if (data.success && data.reports && data.reports.length > 0) {
                const pendingCount = data.reports.filter(r => r.status === 'Pending').length;
                if (pendingCount > 0) {
                    badge.innerText = pendingCount;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }

                container.innerHTML = data.reports.map(report => `
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row gap-4 items-start md:items-center">
                        <div class="w-12 h-12 rounded-2xl ${getFeedbackTypeColor(report.type)} flex items-center justify-center shrink-0 text-lg">
                            <i class="${getFeedbackTypeIcon(report.type)}"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start mb-1">
                                <h4 class="font-bold text-gray-800 text-base">${report.subject || 'Guest Message'} 
                                    <span class="text-xs font-normal text-gray-400 ml-2">
                                        ${report.room_name ? 'regarding ' + report.room_name : ''}
                                    </span>
                                </h4>
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold ${getFeedbackStatusColor(report.status)}">
                                    ${report.status}
                                </span>
                            </div>
                            <p class="text-gray-600 text-sm mt-1">${report.message}</p>
                            <p class="text-xs text-gray-400 mt-2 flex items-center gap-2">
                                <span><i class="fas fa-user mr-1"></i> ${report.user_name || 'Guest'}</span>
                                <span>•</span>
                                <span><i class="fas fa-clock mr-1"></i> ${report.created_at || 'Recent'}</span>
                            </p>
                            ${report.admin_response ? `
                                <div class="mt-3 p-3 bg-blue-50 border-l-4 border-brand-purple rounded-r-xl text-sm">
                                    <strong class="text-brand-purple block mb-1">Management Response:</strong>
                                    <span class="text-gray-700">${report.admin_response}</span>
                                </div>
                            ` : ''}
                        </div>
                        <div class="flex gap-2">
                            <button onclick="openReplyModal('${report.id}')" class="bg-purple-50 text-brand-purple hover:bg-purple-100 p-2.5 rounded-xl transition" title="Reply to Guest"><i class="fas fa-reply"></i></button>
                            ${report.status !== 'Resolved' ? `<button onclick="updateFeedbackStatus('${report.id}', 'Resolved')" class="bg-green-50 text-green-600 hover:bg-green-100 p-2.5 rounded-xl transition" title="Mark as Resolved"><i class="fas fa-check"></i></button>` : ''}
                            ${report.status === 'Resolved' ? `<button onclick="updateFeedbackStatus('${report.id}', 'Pending')" class="bg-yellow-50 text-yellow-600 hover:bg-yellow-100 p-2.5 rounded-xl transition" title="Mark as Pending"><i class="fas fa-undo"></i></button>` : ''}
                            <button onclick="deleteFeedbackItem('${report.id}')" class="bg-gray-50 text-gray-400 hover:text-red-600 p-2.5 rounded-xl transition" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<div class="bg-white p-12 text-center text-gray-400 rounded-2xl border border-gray-100">No guest inquiries or feedback received yet.</div>';
                badge.classList.add('hidden');
            }
        })
        .catch(error => {
            console.error('Error loading feedback:', error);
            document.getElementById('reports-list').innerHTML = '<p class="text-center text-red-400 py-8">Error loading feedback items.</p>';
        });
}

function getFeedbackTypeColor(type) {
    const colors = {
        'feedback': 'bg-yellow-100 text-yellow-700',
        'complaint': 'bg-red-100 text-red-700',
        'suggestion': 'bg-blue-100 text-blue-700'
    };
    return colors[type] || 'bg-gray-100 text-gray-700';
}

function getFeedbackTypeIcon(type) {
    const icons = {
        'feedback': 'fas fa-star',
        'complaint': 'fas fa-exclamation-triangle',
        'suggestion': 'fas fa-lightbulb'
    };
    return icons[type] || 'fas fa-comment-dots';
}

function getFeedbackStatusColor(status) {
    return status === 'Resolved' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700';
}

function updateFeedbackStatus(reportId, status) {
    showConfirm(`Mark this feedback as ${status}?`, () => {
        fetch('../actions/reports_action.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: reportId, status: status })
        })
        .then(response => response.json())
        .then(data => {
            showToast(data.message || 'Status updated!', data.success ? 'success' : 'error');
            if (data.success) loadGuestFeedback();
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error updating status', 'error');
        });
    });
}

function deleteFeedbackItem(reportId) {
    showConfirm('Delete this feedback entry?', () => {
        fetch('../actions/reports_action.php?id=' + reportId, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            showToast(data.message || 'Feedback deleted!', data.success ? 'success' : 'error');
            if (data.success) loadGuestFeedback();
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error deleting feedback', 'error');
        });
    });
}

function openReplyModal(reportId) {
    document.getElementById('reply-report-id').value = reportId;
    document.getElementById('reply-message').value = '';
    document.getElementById('reply-modal').classList.remove('hidden');
    document.getElementById('reply-modal').classList.add('flex');
}

function closeReplyModal() {
    document.getElementById('reply-modal').classList.add('hidden');
    document.getElementById('reply-modal').classList.remove('flex');
}

function submitReply() {
    const reportId = document.getElementById('reply-report-id').value;
    const message = document.getElementById('reply-message').value;
    
    if (!message.trim()) {
        showToast('Please enter a reply message', 'warning');
        return;
    }
    
    fetch('../actions/save_report_reply.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ report_id: reportId, message: message })
    })
    .then(res => res.json())
    .then(data => {
        showToast(data.message || 'Reply sent successfully!', data.success ? 'success' : 'error');
        if (data.success) {
            closeReplyModal();
            loadGuestFeedback();
        }
    });
}

// Initialize reports on page load
document.addEventListener('DOMContentLoaded', () => {
    generateHotelReportData();
    loadGuestFeedback();
});
</script>

