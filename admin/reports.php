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
                            <option value="daily_summary" selected>📋 Daily Reservation Summary (Night Audit)</option>
                            <option value="bookings">📑 Main Reservation Ledger</option>
                            <option value="financial">💰 Financial & Revenue Summary</option>
                            <option value="daily_manifest">🛎️ Front Desk Daily Manifest</option>
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

            <!-- ================= REPORT STRUCTURE CONTAINER ================= -->
            <div id="official-report-container" class="space-y-6">

                <!-- 1. DOCUMENT HEADER -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 border-b border-gray-100 pb-6">
                        <!-- Brand Identity -->
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-brand-purple to-indigo-600 text-white flex items-center justify-center text-2xl font-serif font-black shadow-md shrink-0">
                                W
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h2 class="text-xl font-extrabold text-gray-900 tracking-tight">W Island Hub Backpackers Inn</h2>
                                    <span class="text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">Official PMS</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5 flex items-center gap-1.5">
                                    <i class="fas fa-map-marker-alt text-brand-purple"></i> Sitio Duli, Barangay Bucana, El Nido, Palawan 5313, Philippines
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5 flex items-center gap-2">
                                    <span><i class="fas fa-phone-alt"></i> +63 912 345 6789 / +63 998 765 4321</span>
                                    <span>•</span>
                                    <span><i class="fas fa-envelope"></i> info@wislandhub.com</span>
                                </p>
                            </div>
                        </div>

                        <!-- Document Classification -->
                        <div class="flex flex-col items-start lg:items-end">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-[11px] font-extrabold bg-red-50 text-red-700 border border-red-200 tracking-wider uppercase mb-1.5">
                                <i class="fas fa-shield-alt"></i> Confidential // Official Property Audit Report
                            </span>
                            <h3 class="text-base font-bold text-gray-800" id="report-doc-title">Daily Reservation Summary & Front Office Night Audit</h3>
                            <div class="text-xs text-gray-500 flex flex-wrap gap-x-3 gap-y-1 mt-1 font-mono">
                                <span><strong>Cycle:</strong> <span id="report-doc-cycle">Current Period</span></span>
                                <span>•</span>
                                <span><strong>Audit Ref:</strong> <span id="report-doc-ref" class="text-brand-purple font-bold">RES-AUDIT</span></span>
                            </div>
                            <div class="text-[11px] text-gray-400 mt-0.5">
                                Generated: <span id="report-doc-timestamp">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. OPERATIONAL SNAPSHOT (EXECUTIVE KPI BAR) -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5" id="operational-snapshot-section">
                    <!-- 2A. Occupancy Percentage -->
                    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="fas fa-chart-pie text-brand-purple"></i> Occupancy Percentage
                                </span>
                                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-purple-50 text-brand-purple" id="kpi-inventory-ratio">
                                    0 / 0 Rooms
                                </span>
                            </div>
                            <div class="mt-3 flex items-baseline gap-3">
                                <h4 class="text-3xl font-extrabold text-gray-900" id="kpi-occupancy-rate">0.0%</h4>
                                <span class="text-xs text-gray-500 font-medium">rentable rooms occupied</span>
                            </div>
                            <!-- Visual Progress Bar -->
                            <div class="w-full bg-gray-100 rounded-full h-2.5 mt-3 overflow-hidden">
                                <div id="kpi-occupancy-bar" class="bg-gradient-to-r from-brand-purple to-indigo-500 h-2.5 rounded-full transition-all duration-500" style="width: 0%"></div>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-gray-50 flex items-center justify-between text-xs text-gray-500">
                            <span>Total Inventory: <strong id="kpi-total-inventory" class="text-gray-700">0</strong></span>
                            <span>Rentable: <strong id="kpi-rentable-inventory" class="text-gray-700">0</strong></span>
                            <span>Maintenance: <strong id="kpi-maintenance-inventory" class="text-amber-600">0</strong></span>
                        </div>
                    </div>

                    <!-- 2B. Movement Counters -->
                    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fas fa-arrows-alt text-brand-blue"></i> Movement Counters
                            </span>
                            <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">Operational Status</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2.5">
                            <div class="p-2.5 bg-blue-50/60 rounded-xl border border-blue-100/50">
                                <div class="text-[10px] font-bold uppercase text-blue-700">Expected Check-Ins</div>
                                <div class="text-xl font-extrabold text-blue-900 mt-0.5" id="kpi-arrivals-count">0</div>
                                <div class="text-[10px] text-blue-600">Arrivals in window</div>
                            </div>
                            <div class="p-2.5 bg-orange-50/60 rounded-xl border border-orange-100/50">
                                <div class="text-[10px] font-bold uppercase text-orange-700">Expected Check-Outs</div>
                                <div class="text-xl font-extrabold text-orange-900 mt-0.5" id="kpi-departures-count">0</div>
                                <div class="text-[10px] text-orange-600">Scheduled departures</div>
                            </div>
                            <div class="p-2.5 bg-emerald-50/60 rounded-xl border border-emerald-100/50">
                                <div class="text-[10px] font-bold uppercase text-emerald-700">Stay-Overs</div>
                                <div class="text-xl font-extrabold text-emerald-900 mt-0.5" id="kpi-stayovers-count">0</div>
                                <div class="text-[10px] text-emerald-600">In-house guests</div>
                            </div>
                            <div class="p-2.5 bg-gray-50 rounded-xl border border-gray-200/60">
                                <div class="text-[10px] font-bold uppercase text-gray-600">Vacant Clean / Dirty</div>
                                <div class="text-xl font-extrabold text-gray-800 mt-0.5" id="kpi-vacant-count">0 / 0</div>
                                <div class="text-[10px] text-gray-500">Clean / Maintenance</div>
                            </div>
                        </div>
                    </div>

                    <!-- 2C. Revenue Metrics -->
                    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="fas fa-coins text-emerald-500"></i> Revenue Metrics
                                </span>
                                <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">Reconciled Folio</span>
                            </div>
                            <div class="space-y-2.5 mt-2">
                                <div class="flex justify-between items-baseline">
                                    <span class="text-xs text-gray-500 font-medium">Room Sales (Gross Confirmed):</span>
                                    <span class="text-lg font-extrabold text-emerald-700 font-mono" id="kpi-room-sales">₱0.00</span>
                                </div>
                                <div class="flex justify-between items-baseline">
                                    <span class="text-xs text-gray-500 font-medium">Average Daily Rate (ADR):</span>
                                    <span class="text-sm font-bold text-gray-800 font-mono" id="kpi-adr-val">₱0.00 / nt</span>
                                </div>
                                <div class="flex justify-between items-baseline">
                                    <span class="text-xs text-gray-500 font-medium">Total Pending Balances:</span>
                                    <span class="text-sm font-bold text-amber-600 font-mono" id="kpi-pending-balance">₱0.00</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-gray-50 flex items-center justify-between text-xs text-gray-400">
                            <span>Deposits Collected: <strong id="kpi-deposits-collected" class="text-gray-600">₱0.00</strong></span>
                            <span>Taxes/Fees: <strong id="kpi-taxes-portion" class="text-gray-600">₱0.00</strong></span>
                        </div>
                    </div>
                </div>

                <!-- Category & Payment Distribution Strip -->
                <div id="breakdown-container" class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <h5 class="font-bold text-gray-800 text-sm">Room Category & Payment Channel Distribution</h5>
                            <p class="text-xs text-gray-400">Inventory proportion and revenue channels for this audit window</p>
                        </div>
                        <div id="breakdown-pills" class="flex flex-wrap gap-2">
                            <span class="px-3 py-1 bg-gray-50 text-gray-600 text-xs rounded-lg border border-gray-100">Loading distribution metrics...</span>
                        </div>
                    </div>
                </div>

                <!-- 3. MAIN RESERVATION LEDGER (TABULAR DATA) -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 bg-gray-50/50">
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-gray-900 text-base flex items-center gap-2" id="report-table-title">
                                    <i class="fas fa-book-open text-brand-purple"></i> Main Reservation Ledger
                                </h4>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-50 text-brand-purple border border-purple-100">
                                    Tabular Data
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1" id="report-table-subtitle">Transaction identifiers, guest credentials, accommodation data, operational status, and folio billing</p>
                        </div>
                        <div class="flex items-center gap-3 text-xs">
                            <span id="record-count-badge" class="bg-white px-3 py-1.5 rounded-xl border border-gray-200 font-bold text-gray-700 shadow-sm">0 Records</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[1050px]">
                            <thead class="bg-gray-50/80 text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200/80" id="report-table-head">
                                <tr>
                                    <th class="p-3.5 pl-5">Transaction ID</th>
                                    <th class="p-3.5">Guest Credentials</th>
                                    <th class="p-3.5">Accommodation Data</th>
                                    <th class="p-3.5">Stay Schedule</th>
                                    <th class="p-3.5 text-center">Operational Status</th>
                                    <th class="p-3.5 text-right">Billing Details</th>
                                    <th class="p-3.5 pr-5 text-right">Folio Balance</th>
                                </tr>
                            </thead>
                            <tbody id="report-table-body" class="text-sm divide-y divide-gray-100">
                                <tr>
                                    <td colspan="7" class="p-12 text-center text-gray-400">
                                        <i class="fas fa-spinner fa-spin text-2xl mb-3 block text-brand-purple"></i>
                                        Compiling hotel operational ledger...
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot id="report-table-foot" class="bg-gray-50/90 text-sm font-bold text-gray-800 border-t-2 border-gray-200">
                                <!-- Populated dynamically with grand totals -->
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- 4. VERIFICATION AND SIGN-OFF BLOCK -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-6" id="verification-signoff-section">
                    <div class="border-b border-gray-100 pb-3">
                        <h4 class="font-bold text-gray-800 text-base flex items-center gap-2">
                            <i class="fas fa-file-signature text-brand-purple"></i> Verification and Sign-Off Block
                        </h4>
                        <p class="text-xs text-gray-400 mt-0.5">Dual-authorization accountability signatures & front-office handover remarks</p>
                    </div>

                    <!-- Special Remarks & Handover Notes -->
                    <div class="bg-slate-50/70 rounded-xl p-4 border border-slate-200/70">
                        <div class="flex items-center justify-between mb-2">
                            <h5 class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fas fa-clipboard-list text-amber-500"></i> Special Remarks & Front-Desk Handover Notes
                            </h5>
                            <span class="text-[11px] text-gray-400">Flight Delays • Maintenance Holds • Guest Arrangements</span>
                        </div>
                        <div id="special-remarks-container" class="space-y-2 mb-3">
                            <p class="text-xs text-gray-400 italic">No special flight delays or maintenance holds recorded for this reporting cycle.</p>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Supervisor Duty Handover Remarks (Front-Desk Log):</label>
                            <textarea id="handover-notes-input" rows="2" class="w-full bg-white border border-gray-200 rounded-xl p-2.5 text-xs text-gray-800 outline-none focus:ring-2 focus:ring-brand-purple/20 focus:border-brand-purple" placeholder="Add duty handover notes, flight delay updates, maintenance check notes, or cash drawer handover details..."></textarea>
                        </div>
                    </div>

                    <!-- Accountability Signatures Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-2">
                        <!-- Signature 1: Front Office Supervisor -->
                        <div class="p-5 rounded-xl border border-gray-200 bg-gray-50/40 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[11px] font-bold text-brand-purple uppercase tracking-wider">Accountability Seal 1</span>
                                    <span class="text-[10px] bg-purple-50 text-brand-purple font-semibold px-2 py-0.5 rounded">Front Office Shift</span>
                                </div>
                                <p class="text-[11px] text-gray-500 leading-relaxed italic mb-6">
                                    "I hereby certify that all room inventory counts, arrivals/departures movements, and key handovers for this operational cycle have been personally verified and matched against physical room status."
                                </p>
                            </div>
                            <div>
                                <div class="border-b border-gray-300 mb-2 h-10 flex items-end">
                                    <span class="text-xs text-gray-400 italic">Signature over Printed Name</span>
                                </div>
                                <div class="flex justify-between items-baseline text-xs">
                                    <div>
                                        <div class="font-bold text-gray-800">Front-Office Supervisor on Duty</div>
                                        <div class="text-[11px] text-gray-400">Role Tag: Operations / Front Desk Lead</div>
                                    </div>
                                    <div class="text-right text-[11px] text-gray-500">
                                        Date & Duty Shift: ____________
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Signature 2: Night Auditor / General Manager -->
                        <div class="p-5 rounded-xl border border-gray-200 bg-gray-50/40 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[11px] font-bold text-indigo-700 uppercase tracking-wider">Accountability Seal 2</span>
                                    <span class="text-[10px] bg-indigo-50 text-indigo-700 font-semibold px-2 py-0.5 rounded">Night Audit & Management</span>
                                </div>
                                <p class="text-[11px] text-gray-500 leading-relaxed italic mb-6">
                                    "I have audited and balanced the reservation folio ledger, end-of-day revenue transactions, payment channel reconciliations, and confirmed outstanding folio balances for hotel administration."
                                </p>
                            </div>
                            <div>
                                <div class="border-b border-gray-300 mb-2 h-10 flex items-end">
                                    <span class="text-xs text-gray-400 italic">Signature over Printed Name</span>
                                </div>
                                <div class="flex justify-between items-baseline text-xs">
                                    <div>
                                        <div class="font-bold text-gray-800">Night Auditor / General Manager</div>
                                        <div class="text-[11px] text-gray-400">Role Tag: Executive / Audit Sign-Off</div>
                                    </div>
                                    <div class="text-right text-[11px] text-gray-500">
                                        Date & Official Stamp: ____________
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. SYSTEM FOOTER -->
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 text-xs text-gray-500 flex flex-col sm:flex-row justify-between items-center gap-3">
                    <div class="flex flex-col sm:flex-row items-center gap-2 text-center sm:text-left">
                        <strong class="text-gray-700 font-semibold flex items-center gap-1.5">
                            <i class="fas fa-server text-brand-purple"></i> W Island Hub PMS v2.4.0
                        </strong>
                        <span class="hidden sm:inline text-gray-300">•</span>
                        <span>DB Instance: <code class="bg-white px-1.5 py-0.5 rounded border border-gray-200 text-gray-700">w_island_hub</code></span>
                        <span class="hidden sm:inline text-gray-300">•</span>
                        <span id="footer-db-ref">Ref: RES-AUDIT</span>
                    </div>
                    <div class="flex items-center gap-4 text-[11px]">
                        <span>System Generation: <strong id="footer-timestamp" class="text-gray-700">-</strong></span>
                        <span class="bg-white px-2.5 py-1 rounded-lg border border-gray-200 font-bold text-gray-600 shadow-2xs">Page 1 of 1</span>
                    </div>
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

// Operational Status Badge Helper
function getOperationalBadge(status) {
    switch (status) {
        case 'Checked In':
            return '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200"><i class="fas fa-house-user text-[10px]"></i> Checked In</span>';
        case 'Checked Out':
            return '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200"><i class="fas fa-sign-out-alt text-[10px]"></i> Checked Out</span>';
        case 'No-Show':
            return '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200"><i class="fas fa-user-times text-[10px]"></i> No-Show</span>';
        case 'Cancelled':
            return '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200"><i class="fas fa-ban text-[10px]"></i> Cancelled</span>';
        case 'Confirmed':
        default:
            return '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-200"><i class="fas fa-check-circle text-[10px]"></i> Confirmed</span>';
    }
}

// Render report data into UI
function renderReportView(data) {
    if (document.getElementById('report-ref-badge')) {
        document.getElementById('report-ref-badge').innerText = `Ref: ${data.report_ref || 'RPT-READY'}`;
    }
    if (document.getElementById('active-filter-summary')) {
        document.getElementById('active-filter-summary').innerText = `Scope: ${data.date_range_label || 'Current Period'} • Ref: ${data.report_ref || 'RPT-OFFICIAL'}`;
    }

    // Always update Section 1 (Header) & Section 5 (Footer) metadata
    const reportTitleMap = {
        'daily_summary': 'Daily Reservation Summary & Front Office Night Audit',
        'bookings': 'Hotel Reservations & Main Reservation Ledger',
        'financial': 'Financial & Revenue Summary Report',
        'daily_manifest': 'Front Desk Daily Operations Manifest',
        'room_performance': 'Room Occupancy & Utilization Performance Report',
        'feedback': 'Guest Satisfaction & Reviews Audit Report'
    };
    const titleText = data.report_title || reportTitleMap[data.report_type] || 'Hotel Operations Audit Report';

    if (document.getElementById('report-doc-title')) document.getElementById('report-doc-title').innerText = titleText;
    if (document.getElementById('report-doc-cycle')) document.getElementById('report-doc-cycle').innerText = data.date_range_label || 'Current Period';
    if (document.getElementById('report-doc-ref')) document.getElementById('report-doc-ref').innerText = data.report_ref || 'RES-AUDIT';
    if (document.getElementById('report-doc-timestamp')) document.getElementById('report-doc-timestamp').innerText = data.generated_at_formatted || new Date().toLocaleString();
    if (document.getElementById('footer-db-ref')) document.getElementById('footer-db-ref').innerText = 'Audit Ref: ' + (data.report_ref || 'RES-AUDIT');
    if (document.getElementById('footer-timestamp')) document.getElementById('footer-timestamp').innerText = data.generated_at_formatted || new Date().toLocaleString();

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

// 1. Render Financial & Bookings Register (Main 5-Section Layout)
function renderFinancialOrBookingsView(data) {
    const s = data.summary || {};
    const ops = data.operational_snapshot || {};

    // SECTION 2: OPERATIONAL SNAPSHOT (EXECUTIVE KPI BAR)
    // 2A. Occupancy Percentage
    const occPct = ops.occupancy_percentage !== undefined ? ops.occupancy_percentage : 0;
    if (document.getElementById('kpi-occupancy-rate')) document.getElementById('kpi-occupancy-rate').innerText = occPct + '%';
    if (document.getElementById('kpi-occupancy-bar')) document.getElementById('kpi-occupancy-bar').style.width = Math.min(100, Math.max(0, occPct)) + '%';
    if (document.getElementById('kpi-inventory-ratio')) document.getElementById('kpi-inventory-ratio').innerText = `${ops.occupied_rooms || 0} / ${ops.rentable_rooms || 0} Rooms`;
    if (document.getElementById('kpi-total-inventory')) document.getElementById('kpi-total-inventory').innerText = ops.total_inventory || 0;
    if (document.getElementById('kpi-rentable-inventory')) document.getElementById('kpi-rentable-inventory').innerText = ops.rentable_rooms || 0;
    if (document.getElementById('kpi-maintenance-inventory')) document.getElementById('kpi-maintenance-inventory').innerText = ops.maintenance_rooms || 0;

    // 2B. Movement Counters
    if (document.getElementById('kpi-arrivals-count')) document.getElementById('kpi-arrivals-count').innerText = ops.expected_check_ins !== undefined ? ops.expected_check_ins : (s.expected_arrivals || 0);
    if (document.getElementById('kpi-departures-count')) document.getElementById('kpi-departures-count').innerText = ops.expected_check_outs !== undefined ? ops.expected_check_outs : (s.expected_departures || 0);
    if (document.getElementById('kpi-stayovers-count')) document.getElementById('kpi-stayovers-count').innerText = ops.stay_overs !== undefined ? ops.stay_overs : (s.in_house_guests || 0);
    if (document.getElementById('kpi-vacant-count')) document.getElementById('kpi-vacant-count').innerText = `${ops.vacant_clean !== undefined ? ops.vacant_clean : 0} Clean / ${ops.vacant_dirty !== undefined ? ops.vacant_dirty : 0} Hold`;

    // 2C. Revenue Metrics
    if (document.getElementById('kpi-room-sales')) document.getElementById('kpi-room-sales').innerText = formatCurrency(ops.room_sales !== undefined ? ops.room_sales : (s.confirmed_revenue || 0));
    if (document.getElementById('kpi-adr-val')) document.getElementById('kpi-adr-val').innerText = formatCurrency(ops.average_daily_rate !== undefined ? ops.average_daily_rate : (s.average_daily_rate || 0)) + ' / nt';
    if (document.getElementById('kpi-pending-balance')) document.getElementById('kpi-pending-balance').innerText = formatCurrency(ops.total_pending_balance !== undefined ? ops.total_pending_balance : (s.pending_revenue || 0));
    if (document.getElementById('kpi-deposits-collected')) document.getElementById('kpi-deposits-collected').innerText = formatCurrency(ops.total_deposits_collected !== undefined ? ops.total_deposits_collected : (s.total_deposits_collected || 0));
    if (document.getElementById('kpi-taxes-portion')) document.getElementById('kpi-taxes-portion').innerText = formatCurrency(ops.taxes_and_fees !== undefined ? ops.taxes_and_fees : (s.tax_and_fees || 0));

    // Distribution Pills
    const pillsContainer = document.getElementById('breakdown-pills');
    if (pillsContainer) {
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
    }

    // SECTION 3: MAIN RESERVATION LEDGER (TABULAR DATA)
    const tableTitle = document.getElementById('report-table-title');
    if (tableTitle) {
        tableTitle.innerHTML = '<i class="fas fa-book-open text-brand-purple mr-1.5"></i> ' + (data.report_type === 'financial' ? 'Financial & Revenue Ledger' : 'Main Reservation Ledger');
    }
    if (document.getElementById('report-table-subtitle')) {
        document.getElementById('report-table-subtitle').innerText = `Period: ${data.date_range_label} • Showing ${data.rows ? data.rows.length : 0} reservation records`;
    }
    if (document.getElementById('record-count-badge')) {
        document.getElementById('record-count-badge').innerText = `${data.rows ? data.rows.length : 0} Records`;
    }

    // Table Header with 7 Grouped Pillars
    document.getElementById('report-table-head').innerHTML = `
        <tr>
            <th class="p-3.5 pl-5">Transaction ID</th>
            <th class="p-3.5">Guest Credentials</th>
            <th class="p-3.5">Accommodation Data</th>
            <th class="p-3.5">Stay Schedule</th>
            <th class="p-3.5 text-center">Operational Status</th>
            <th class="p-3.5 text-right">Billing Details</th>
            <th class="p-3.5 pr-5 text-right">Folio Balance</th>
        </tr>
    `;

    // Table Body
    const tbody = document.getElementById('report-table-body');
    const rows = data.rows || [];

    if (rows.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="p-12 text-center text-gray-400">No reservations found matching the selected parameters.</td></tr>`;
    } else {
        tbody.innerHTML = rows.map(b => {
            const checkInFormatted = b.check_in_formatted || new Date(b.check_in).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            const checkOutFormatted = b.check_out_formatted || new Date(b.check_out).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            const createdFormatted = b.created_at_formatted || (b.created_at ? new Date(b.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '-');
            const opStatus = b.operational_status || b.status;
            const charges = parseFloat(b.total_charges !== undefined ? b.total_charges : (b.total_price || 0));
            const paid = parseFloat(b.paid_amount || 0);
            const balance = parseFloat(b.balance_due !== undefined ? b.balance_due : Math.max(0, charges - paid));
            const paymentChannel = b.payment_channel || b.payment_method || 'Over-the-counter Cash / Pay at Hotel';
            const occupants = b.occupants_summary || `${b.room_capacity || 2} Pax (${Math.min(b.room_capacity || 2, 2)} Adults, ${Math.max(0, (b.room_capacity || 2) - 2)} Children)`;

            return `
                <tr class="hover:bg-gray-50/80 transition">
                    <td class="p-3.5 pl-5 align-top">
                        <div class="font-mono font-bold text-gray-800 text-xs">${b.ref_id}</div>
                        <div class="text-[11px] text-gray-400 font-mono mt-0.5">${createdFormatted}</div>
                    </td>
                    <td class="p-3.5 align-top">
                        <div class="font-bold text-gray-900">${b.final_guest_name}</div>
                        <div class="text-[11px] text-gray-500">${b.guest_email || 'N/A'} • ${b.guest_phone || 'N/A'}</div>
                        <div class="text-[11px] text-brand-purple font-semibold mt-0.5 flex items-center gap-1">
                            <i class="fas fa-user-friends text-[10px]"></i> ${occupants}
                        </div>
                    </td>
                    <td class="p-3.5 align-top">
                        <div class="font-bold text-gray-800">${b.room_name || ('Room #' + b.room_id)}</div>
                        <div class="text-[11px] text-gray-500 font-medium">${b.room_type || 'Standard'} (Base: ${formatCurrency(b.room_base_price || 0)})</div>
                    </td>
                    <td class="p-3.5 align-top">
                        <div class="text-xs text-gray-800 font-semibold">${checkInFormatted} &rarr; ${checkOutFormatted}</div>
                        <div class="text-[11px] text-gray-500 font-medium">${b.nights || 1} Night${(b.nights || 1) > 1 ? 's' : ''}</div>
                    </td>
                    <td class="p-3.5 align-top text-center">
                        ${getOperationalBadge(opStatus)}
                    </td>
                    <td class="p-3.5 align-top text-right">
                        <div class="font-bold text-gray-900 font-mono text-sm">${formatCurrency(charges)}</div>
                        <div class="text-[11px] text-gray-500">${paymentChannel}</div>
                        <div class="text-[10px] ${paid > 0 ? 'text-emerald-600 font-bold' : 'text-gray-400'}">${b.deposit_status || (paid > 0 ? `Deposit Paid: ${formatCurrency(paid)}` : 'Unpaid')}</div>
                    </td>
                    <td class="p-3.5 pr-5 align-top text-right">
                        <div class="font-bold font-mono text-sm ${balance > 0 ? 'text-amber-600' : 'text-emerald-600'}">${balance > 0 ? formatCurrency(balance) : '₱0.00 Cleared'}</div>
                        <div class="text-[10px] ${balance > 0 ? 'text-amber-600 font-medium' : 'text-emerald-600 font-medium'}">${balance > 0 ? 'Outstanding Due' : 'Folio Settled'}</div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    // Table Footer Totals
    const totCharges = ops.room_sales !== undefined ? ops.room_sales : (s.confirmed_revenue || 0);
    const totDeposits = ops.total_deposits_collected !== undefined ? ops.total_deposits_collected : (s.total_deposits_collected || 0);
    const totBalance = ops.total_pending_balance !== undefined ? ops.total_pending_balance : (s.total_pending_balance || 0);
    const totNights = s.total_nights_sold || 0;

    document.getElementById('report-table-foot').innerHTML = `
        <tr>
            <td colspan="3" class="p-3.5 pl-5 text-right font-bold text-gray-700">RECONCILED AUDIT TOTALS:</td>
            <td class="p-3.5 font-bold text-gray-900">${totNights} Nights</td>
            <td class="p-3.5 text-center text-xs text-gray-500">${s.confirmed_bookings || 0} Confirmed / ${s.total_bookings || rows.length} Total</td>
            <td class="p-3.5 text-right font-mono font-bold text-emerald-700 text-base">
                <div>${formatCurrency(totCharges)}</div>
                <div class="text-[10px] text-gray-500 font-normal">Deposits: ${formatCurrency(totDeposits)}</div>
            </td>
            <td class="p-3.5 pr-5 text-right font-mono font-bold text-amber-600 text-base">
                <div>${formatCurrency(totBalance)}</div>
                <div class="text-[10px] text-gray-500 font-normal">Total Pending</div>
            </td>
        </tr>
    `;

    // SECTION 4: VERIFICATION & SIGN-OFF BLOCK REMARKS
    const remarksContainer = document.getElementById('special-remarks-container');
    if (remarksContainer) {
        const remarks = data.special_remarks || [];
        if (remarks.length === 0) {
            remarksContainer.innerHTML = '<p class="text-xs text-gray-400 italic">No special flight delays or maintenance holds recorded for this reporting cycle.</p>';
        } else {
            remarksContainer.innerHTML = remarks.map(r => {
                const catColor = r.category === 'Flight / Late Arrival' ? 'bg-amber-100 text-amber-800 border-amber-200' : (r.category === 'Maintenance Hold' ? 'bg-red-100 text-red-800 border-red-200' : 'bg-purple-100 text-purple-800 border-purple-200');
                return `
                    <div class="flex items-start gap-2.5 p-2 bg-white rounded-lg border border-gray-200 text-xs">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold border ${catColor} shrink-0">${r.category}</span>
                        <div class="flex-1">
                            <span class="font-bold text-gray-800 font-mono">${r.ref_id}</span>
                            <span class="text-gray-400 font-normal">(${r.room} • ${r.guest}):</span>
                            <span class="text-gray-700 ml-1">${r.note}</span>
                        </div>
                    </div>
                `;
            }).join('');
        }
    }
}

// 2. Render Daily Front Desk Manifest
function renderManifestView(data) {
    const s = data.summary || {};
    
    // KPI Cards
    if (document.getElementById('kpi-arrivals-count')) document.getElementById('kpi-arrivals-count').innerText = s.expected_arrivals || 0;
    if (document.getElementById('kpi-departures-count')) document.getElementById('kpi-departures-count').innerText = s.expected_departures || 0;
    if (document.getElementById('kpi-stayovers-count')) document.getElementById('kpi-stayovers-count').innerText = s.in_house_guests || 0;
    if (document.getElementById('kpi-occupancy-rate')) document.getElementById('kpi-occupancy-rate').innerText = `${s.estimated_occupancy_rate || 0}%`;
    if (document.getElementById('kpi-occupancy-bar')) document.getElementById('kpi-occupancy-bar').style.width = `${Math.min(100, s.estimated_occupancy_rate || 0)}%`;
    if (document.getElementById('kpi-inventory-ratio')) document.getElementById('kpi-inventory-ratio').innerText = `${(s.in_house_guests || 0) + (s.expected_arrivals || 0)} / ${s.total_active_rooms || 0} Rooms`;
    if (document.getElementById('kpi-total-inventory')) document.getElementById('kpi-total-inventory').innerText = s.total_active_rooms || 0;
    if (document.getElementById('kpi-rentable-inventory')) document.getElementById('kpi-rentable-inventory').innerText = s.total_active_rooms || 0;
    if (document.getElementById('kpi-maintenance-inventory')) document.getElementById('kpi-maintenance-inventory').innerText = 0;

    document.getElementById('report-table-title').innerText = `Daily Front Desk Operations Manifest`;
    document.getElementById('report-table-subtitle').innerText = `Date: ${data.target_date_formatted || data.target_date} • Operational Room & Guest Schedule`;

    const arrivals = data.arrivals || [];
    const departures = data.departures || [];
    const inHouse = data.in_house || [];

    document.getElementById('record-count-badge').innerText = `${arrivals.length + departures.length + inHouse.length} Total Movements`;

    document.getElementById('report-table-head').innerHTML = `
        <tr>
            <th class="p-3.5 pl-5">Activity</th>
            <th class="p-3.5">Transaction ID</th>
            <th class="p-3.5">Guest Credentials</th>
            <th class="p-3.5">Room & Type</th>
            <th class="p-3.5">Stay Schedule</th>
            <th class="p-3.5">Payment Method</th>
            <th class="p-3.5 pr-5 text-right">Total Charges</th>
        </tr>
    `;

    let rowsHtml = '';

    if (arrivals.length === 0 && departures.length === 0 && inHouse.length === 0) {
        rowsHtml = `<tr><td colspan="7" class="p-12 text-center text-gray-400">No scheduled arrivals, departures, or staying guests on this date.</td></tr>`;
    } else {
        if (arrivals.length > 0) {
            rowsHtml += `<tr class="bg-blue-50/70 font-bold text-brand-blue text-xs uppercase"><td colspan="7" class="p-3 pl-5"><i class="fas fa-plane-arrival mr-2"></i> Expected Arrivals / Check-Ins (${arrivals.length})</td></tr>`;
            arrivals.forEach(b => {
                rowsHtml += renderManifestRow(b, 'Arrival (Check-in)', 'text-blue-600 bg-blue-50');
            });
        }
        if (departures.length > 0) {
            rowsHtml += `<tr class="bg-orange-50/70 font-bold text-orange-700 text-xs uppercase"><td colspan="7" class="p-3 pl-5"><i class="fas fa-plane-departure mr-2"></i> Expected Departures / Check-Outs (${departures.length})</td></tr>`;
            departures.forEach(b => {
                rowsHtml += renderManifestRow(b, 'Departure (Check-out)', 'text-orange-600 bg-orange-50');
            });
        }
        if (inHouse.length > 0) {
            rowsHtml += `<tr class="bg-emerald-50/70 font-bold text-emerald-700 text-xs uppercase"><td colspan="7" class="p-3 pl-5"><i class="fas fa-house-user mr-2"></i> Current In-House Guests (${inHouse.length})</td></tr>`;
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
            <td class="p-3.5 pl-5"><span class="px-2.5 py-1 rounded-lg text-xs font-bold ${badgeClass}">${typeLabel}</span></td>
            <td class="p-3.5 font-mono font-bold text-gray-600 text-xs">${b.ref_id}</td>
            <td class="p-3.5">
                <div class="font-bold text-gray-800">${b.final_guest_name}</div>
                <div class="text-xs text-gray-400">${b.guest_email || ''}</div>
            </td>
            <td class="p-3.5 font-medium text-gray-800">${b.room_name} <span class="text-xs text-gray-400 font-normal">(${b.room_type})</span></td>
            <td class="p-3.5 text-xs text-gray-600">${b.check_in} &rarr; ${b.check_out} (${b.nights} nts)</td>
            <td class="p-3.5 text-xs text-gray-600 font-medium">${b.payment_method || 'Cash / Desk'}</td>
            <td class="p-3.5 pr-5 text-right font-bold text-brand-dark">${formatCurrency(b.total_price)}</td>
        </tr>
    `;
}

// 3. Render Room Performance & Occupancy
function renderPerformanceView(data) {
    const s = data.summary || {};
    
    if (document.getElementById('kpi-room-sales')) document.getElementById('kpi-room-sales').innerText = formatCurrency(s.total_revenue);
    if (document.getElementById('kpi-adr-val')) document.getElementById('kpi-adr-val').innerText = formatCurrency(s.total_nights_sold > 0 ? (s.total_revenue / s.total_nights_sold) : 0) + ' / nt';
    if (document.getElementById('kpi-total-inventory')) document.getElementById('kpi-total-inventory').innerText = s.total_rooms || 0;
    if (document.getElementById('kpi-rentable-inventory')) document.getElementById('kpi-rentable-inventory').innerText = s.total_rooms || 0;

    document.getElementById('report-table-title').innerText = 'Room Occupancy & Utilization Performance';
    document.getElementById('report-table-subtitle').innerText = `Period: ${data.date_range_label} (${data.days_in_period} days)`;

    const rooms = data.rooms_performance || [];
    document.getElementById('record-count-badge').innerText = `${rooms.length} Rooms`;

    document.getElementById('report-table-head').innerHTML = `
        <tr>
            <th class="p-3.5 pl-5">Room Name</th>
            <th class="p-3.5">Type</th>
            <th class="p-3.5">Base Rate</th>
            <th class="p-3.5">Capacity</th>
            <th class="p-3.5">Total Bookings</th>
            <th class="p-3.5">Nights Sold</th>
            <th class="p-3.5">Est. Occupancy</th>
            <th class="p-3.5 text-right">Revenue Generated</th>
            <th class="p-3.5 pr-5 text-center">Status</th>
        </tr>
    `;

    const tbody = document.getElementById('report-table-body');
    if (rooms.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="p-12 text-center text-gray-400">No room inventory found.</td></tr>`;
    } else {
        tbody.innerHTML = rooms.map(r => `
            <tr class="hover:bg-gray-50/80 transition">
                <td class="p-3.5 pl-5 font-bold text-gray-800">${r.name}</td>
                <td class="p-3.5"><span class="px-2 py-0.5 bg-purple-50 text-brand-purple text-xs font-semibold rounded">${r.type}</span></td>
                <td class="p-3.5 font-medium text-gray-700">${formatCurrency(r.price)} / nt</td>
                <td class="p-3.5 text-gray-600 text-xs">${r.capacity} Guests</td>
                <td class="p-3.5 font-bold text-gray-800">${r.total_bookings}</td>
                <td class="p-3.5 font-bold text-brand-blue">${r.nights_sold} nights</td>
                <td class="p-3.5">
                    <div class="flex items-center gap-2">
                        <div class="w-16 bg-gray-200 rounded-full h-2">
                            <div class="bg-brand-purple h-2 rounded-full" style="width: ${Math.min(100, r.occupancy_rate)}%"></div>
                        </div>
                        <span class="text-xs font-bold text-gray-700">${r.occupancy_rate}%</span>
                    </div>
                </td>
                <td class="p-3.5 text-right font-bold text-emerald-700 text-base font-mono">${formatCurrency(r.revenue_generated)}</td>
                <td class="p-3.5 pr-5 text-center"><span class="px-2 py-0.5 rounded text-xs font-bold ${r.status === 'Maintenance' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'}">${r.status}</span></td>
            </tr>
        `).join('');
    }

    document.getElementById('report-table-foot').innerHTML = `
        <tr>
            <td colspan="4" class="p-3.5 pl-5 text-right font-bold">Total Performance:</td>
            <td class="p-3.5 font-bold">${s.total_confirmed_bookings || 0}</td>
            <td class="p-3.5 font-bold text-brand-blue">${s.total_nights_sold || 0} nts</td>
            <td class="p-3.5"></td>
            <td class="p-3.5 text-right font-bold text-emerald-700 text-base font-mono">${formatCurrency(s.total_revenue)}</td>
            <td class="p-3.5 pr-5"></td>
        </tr>
    `;
}

// 4. Render Guest Feedback & Reviews Report
function renderFeedbackReportView(data) {
    const s = data.summary || {};
    
    document.getElementById('report-table-title').innerText = 'Guest Satisfaction & Reviews Register';
    document.getElementById('report-table-subtitle').innerText = 'Recent guest ratings, feedback submissions, and management replies';

    const reviews = data.reviews || [];
    document.getElementById('record-count-badge').innerText = `${reviews.length} Reviews`;

    document.getElementById('report-table-head').innerHTML = `
        <tr>
            <th class="p-3.5 pl-5">Guest</th>
            <th class="p-3.5">Room</th>
            <th class="p-3.5">Rating</th>
            <th class="p-3.5">Feedback / Comment</th>
            <th class="p-3.5">Admin Reply</th>
            <th class="p-3.5 pr-5">Date</th>
        </tr>
    `;

    const tbody = document.getElementById('report-table-body');
    if (reviews.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="p-12 text-center text-gray-400">No guest reviews recorded yet.</td></tr>`;
    } else {
        tbody.innerHTML = reviews.map(r => `
            <tr class="hover:bg-gray-50/80 transition">
                <td class="p-3.5 pl-5">
                    <div class="font-bold text-gray-800">${r.user_name || 'Guest'}</div>
                    <div class="text-xs text-gray-400">${r.user_email || ''}</div>
                </td>
                <td class="p-3.5 font-medium text-gray-700">${r.room_name || 'General'}</td>
                <td class="p-3.5">
                    <span class="text-yellow-500 font-bold">${'★'.repeat(Math.round(r.rating || 5))}</span>
                    <span class="text-xs text-gray-400 font-semibold ml-1">(${r.rating})</span>
                </td>
                <td class="p-3.5 text-sm text-gray-700 max-w-xs">${r.comment || 'No comment provided'}</td>
                <td class="p-3.5 text-xs text-gray-600">${r.admin_reply ? `<span class="bg-blue-50 text-blue-800 p-2 rounded block border border-blue-100">${r.admin_reply}</span>` : '<span class="text-gray-400 italic">No reply yet</span>'}</td>
                <td class="p-3.5 pr-5 text-xs text-gray-500">${r.created_at ? new Date(r.created_at).toLocaleDateString() : '-'}</td>
            </tr>
        `).join('');
    }

    document.getElementById('report-table-foot').innerHTML = '';
}

/**
 * Professional Hotel Printable Report Generator
 * Opens a clean, branded, landscape multi-page print document adhering to the 5 Key Structural Sections
 */
function printHotelReport() {
    if (!currentReportData) {
        showToast('Please generate report data first', 'warning');
        return;
    }

    const d = currentReportData;
    const type = d.report_type || 'daily_summary';
    const s = d.summary || {};
    const ops = d.operational_snapshot || {};
    const hotelName = "W Island Hub Backpackers Inn";
    const hotelAddress = "Sitio Duli, Barangay Bucana, El Nido, Palawan 5313, Philippines • Contact: +63 912 345 6789 / +63 998 765 4321 • info@wislandhub.com";
    const reportRef = d.report_ref || 'RES-AUDIT-OFFICIAL';
    const generatedDate = d.generated_at_formatted || new Date().toLocaleString();
    const datePeriod = d.date_range_label || 'Current Audit Cycle';
    const handoverText = (document.getElementById('handover-notes-input') ? document.getElementById('handover-notes-input').value.trim() : '') || 'Normal front-office shift turnover. All key slots accounted for and physical room states reconciled with system register.';

    let reportTitle = "DAILY RESERVATION SUMMARY & FRONT OFFICE NIGHT AUDIT";
    if (type === 'bookings') reportTitle = "HOTEL RESERVATIONS & MAIN RESERVATION LEDGER";
    else if (type === 'financial') reportTitle = "HOTEL FINANCIAL & REVENUE SUMMARY REPORT";
    else if (type === 'daily_manifest') reportTitle = "FRONT DESK DAILY OPERATIONS MANIFEST";
    else if (type === 'room_performance') reportTitle = "ROOM OCCUPANCY & UTILIZATION PERFORMANCE REPORT";
    else if (type === 'feedback') reportTitle = "GUEST SATISFACTION & REVIEWS AUDIT REPORT";

    // 1. Executive KPI Bar for Print
    const occPct = ops.occupancy_percentage !== undefined ? ops.occupancy_percentage : 0;
    const roomSales = ops.room_sales !== undefined ? ops.room_sales : (s.confirmed_revenue || 0);
    const adr = ops.average_daily_rate !== undefined ? ops.average_daily_rate : (s.average_daily_rate || 0);
    const pendingBal = ops.total_pending_balance !== undefined ? ops.total_pending_balance : (s.pending_revenue || 0);
    const deposits = ops.total_deposits_collected !== undefined ? ops.total_deposits_collected : 0;

    const kpiPrintHtml = `
        <div style="display: grid; grid-template-columns: 1fr 1.3fr 1.2fr; gap: 12px; margin-bottom: 16px;">
            <!-- Occupancy Box -->
            <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px 12px;">
                <div style="font-size: 10px; text-transform: uppercase; color: #475569; font-weight: bold; letter-spacing: 0.5px;">1. Occupancy Percentage</div>
                <div style="display: flex; align-items: baseline; gap: 8px; margin-top: 3px;">
                    <span style="font-size: 20px; font-weight: 800; color: #0f172a;">${occPct}%</span>
                    <span style="font-size: 10px; color: #64748b; font-weight: 600;">(${ops.occupied_rooms || 0} / ${ops.rentable_rooms || 0} Rentable Rooms)</span>
                </div>
                <div style="font-size: 9.5px; color: #64748b; margin-top: 3px; border-top: 1px solid #e2e8f0; padding-top: 3px;">
                    Total Property Inventory: <strong>${ops.total_inventory || 0}</strong> • Rentable: <strong>${ops.rentable_rooms || 0}</strong> • Maintenance: <strong>${ops.maintenance_rooms || 0}</strong>
                </div>
            </div>

            <!-- Movement Counters Box -->
            <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px 12px;">
                <div style="font-size: 10px; text-transform: uppercase; color: #475569; font-weight: bold; letter-spacing: 0.5px;">2. Movement Counters</div>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px; margin-top: 5px; text-align: center;">
                    <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 5px; padding: 4px 2px;">
                        <div style="font-size: 8.5px; font-weight: bold; color: #1e40af; text-transform: uppercase;">Check-Ins</div>
                        <div style="font-size: 15px; font-weight: 800; color: #1e3a8a;">${ops.expected_check_ins || 0}</div>
                    </div>
                    <div style="background: #fff7ed; border: 1px solid #fed7aa; border-radius: 5px; padding: 4px 2px;">
                        <div style="font-size: 8.5px; font-weight: bold; color: #9a3412; text-transform: uppercase;">Check-Outs</div>
                        <div style="font-size: 15px; font-weight: 800; color: #7c2d12;">${ops.expected_check_outs || 0}</div>
                    </div>
                    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 5px; padding: 4px 2px;">
                        <div style="font-size: 8.5px; font-weight: bold; color: #065f46; text-transform: uppercase;">Stay-Overs</div>
                        <div style="font-size: 15px; font-weight: 800; color: #064e3b;">${ops.stay_overs || 0}</div>
                    </div>
                    <div style="background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 5px; padding: 4px 2px;">
                        <div style="font-size: 8.5px; font-weight: bold; color: #475569; text-transform: uppercase;">Clean/Hold</div>
                        <div style="font-size: 13px; font-weight: 800; color: #334155;">${ops.vacant_clean || 0}/${ops.vacant_dirty || 0}</div>
                    </div>
                </div>
            </div>

            <!-- Revenue Metrics Box -->
            <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px 12px;">
                <div style="font-size: 10px; text-transform: uppercase; color: #475569; font-weight: bold; letter-spacing: 0.5px;">3. Revenue Metrics</div>
                <div style="display: flex; justify-content: space-between; font-size: 10px; margin-top: 3px;">
                    <span style="color: #64748b;">Confirmed Room Sales:</span>
                    <strong style="color: #15803d; font-family: monospace;">${formatCurrency(roomSales)}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 10px; margin-top: 1px;">
                    <span style="color: #64748b;">Average Daily Rate (ADR):</span>
                    <strong style="color: #0f172a; font-family: monospace;">${formatCurrency(adr)} / nt</strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 10px; margin-top: 1px; border-top: 1px solid #e2e8f0; padding-top: 2px;">
                    <span style="color: #b45309; font-weight: 600;">Total Pending Folio:</span>
                    <strong style="color: #b45309; font-family: monospace;">${formatCurrency(pendingBal)}</strong>
                </div>
            </div>
        </div>
    `;

    // 2. Main Reservation Ledger Table for Print
    let tableHeadersHtml = `
        <tr style="background: #f1f5f9;">
            <th style="padding: 6px; border: 1px solid #cbd5e1; text-align: left; font-size: 9.5px; text-transform: uppercase;">Transaction ID</th>
            <th style="padding: 6px; border: 1px solid #cbd5e1; text-align: left; font-size: 9.5px; text-transform: uppercase;">Guest Credentials</th>
            <th style="padding: 6px; border: 1px solid #cbd5e1; text-align: left; font-size: 9.5px; text-transform: uppercase;">Accommodation Data</th>
            <th style="padding: 6px; border: 1px solid #cbd5e1; text-align: left; font-size: 9.5px; text-transform: uppercase;">Stay Schedule</th>
            <th style="padding: 6px; border: 1px solid #cbd5e1; text-align: center; font-size: 9.5px; text-transform: uppercase;">Operational Status</th>
            <th style="padding: 6px; border: 1px solid #cbd5e1; text-align: right; font-size: 9.5px; text-transform: uppercase;">Total Charges</th>
            <th style="padding: 6px; border: 1px solid #cbd5e1; text-align: left; font-size: 9.5px; text-transform: uppercase;">Payment Channel</th>
            <th style="padding: 6px; border: 1px solid #cbd5e1; text-align: right; font-size: 9.5px; text-transform: uppercase;">Deposit Status</th>
            <th style="padding: 6px; border: 1px solid #cbd5e1; text-align: right; font-size: 9.5px; text-transform: uppercase;">Folio Balance</th>
        </tr>
    `;

    const rows = d.rows || [];
    let tableRowsHtml = '';

    if (rows.length === 0) {
        tableRowsHtml = `<tr><td colspan="9" style="padding: 16px; text-align: center; border: 1px solid #cbd5e1; color: #64748b;">No reservations found matching the specified parameters.</td></tr>`;
    } else {
        tableRowsHtml = rows.map((b, idx) => {
            const checkInFormatted = b.check_in_formatted || b.check_in;
            const checkOutFormatted = b.check_out_formatted || b.check_out;
            const createdFormatted = b.created_at_formatted || (b.created_at ? b.created_at.substring(0, 16) : '-');
            const opStatus = b.operational_status || b.status;
            const charges = parseFloat(b.total_charges !== undefined ? b.total_charges : (b.total_price || 0));
            const paid = parseFloat(b.paid_amount || 0);
            const balance = parseFloat(b.balance_due !== undefined ? b.balance_due : Math.max(0, charges - paid));
            const paymentChannel = b.payment_channel || b.payment_method || 'Cash / Desk';
            const occupants = b.occupants_summary || `${b.room_capacity || 2} Pax`;
            const bgRow = idx % 2 === 1 ? 'background: #fbfcfe;' : 'background: #fff;';

            let statusBadge = `<span style="display: inline-block; padding: 2px 6px; border-radius: 4px; font-weight: bold; font-size: 9px; background: #dcfce7; color: #166534; border: 1px solid #bbf7d0;">${opStatus}</span>`;
            if (opStatus === 'Checked In') statusBadge = `<span style="display: inline-block; padding: 2px 6px; border-radius: 4px; font-weight: bold; font-size: 9px; background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe;">Checked In</span>`;
            else if (opStatus === 'Checked Out') statusBadge = `<span style="display: inline-block; padding: 2px 6px; border-radius: 4px; font-weight: bold; font-size: 9px; background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1;">Checked Out</span>`;
            else if (opStatus === 'No-Show') statusBadge = `<span style="display: inline-block; padding: 2px 6px; border-radius: 4px; font-weight: bold; font-size: 9px; background: #ffedd5; color: #9a3412; border: 1px solid #fed7aa;">No-Show</span>`;
            else if (opStatus === 'Cancelled') statusBadge = `<span style="display: inline-block; padding: 2px 6px; border-radius: 4px; font-weight: bold; font-size: 9px; background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;">Cancelled</span>`;

            return `
                <tr style="${bgRow} page-break-inside: avoid;">
                    <td style="padding: 5px 6px; border: 1px solid #cbd5e1; font-family: monospace; font-size: 9.5px; font-weight: bold;">
                        ${b.ref_id}<br>
                        <span style="font-size: 8.5px; color: #64748b; font-family: sans-serif; font-weight: normal;">${createdFormatted}</span>
                    </td>
                    <td style="padding: 5px 6px; border: 1px solid #cbd5e1;">
                        <strong style="color: #0f172a; font-size: 10.5px;">${b.final_guest_name}</strong><br>
                        <span style="font-size: 9px; color: #64748b;">${b.guest_email || 'N/A'} • ${b.guest_phone || 'N/A'}</span><br>
                        <span style="font-size: 8.5px; color: #4338ca; font-weight: bold;">${occupants}</span>
                    </td>
                    <td style="padding: 5px 6px; border: 1px solid #cbd5e1;">
                        <strong style="font-size: 10px;">${b.room_name || ('Room #' + b.room_id)}</strong><br>
                        <span style="font-size: 9px; color: #64748b;">${b.room_type || 'Standard'}</span>
                    </td>
                    <td style="padding: 5px 6px; border: 1px solid #cbd5e1; font-size: 9.5px;">
                        ${checkInFormatted} &rarr; ${checkOutFormatted}<br>
                        <span style="color: #64748b; font-weight: bold;">${b.nights || 1} Night${(b.nights || 1) > 1 ? 's' : ''}</span>
                    </td>
                    <td style="padding: 5px 6px; border: 1px solid #cbd5e1; text-align: center;">
                        ${statusBadge}
                    </td>
                    <td style="padding: 5px 6px; border: 1px solid #cbd5e1; text-align: right; font-weight: bold; font-family: monospace; font-size: 10px;">
                        ${formatCurrency(charges)}
                    </td>
                    <td style="padding: 5px 6px; border: 1px solid #cbd5e1; font-size: 9.5px; color: #334155;">
                        ${paymentChannel}
                    </td>
                    <td style="padding: 5px 6px; border: 1px solid #cbd5e1; text-align: right; font-size: 9.5px; ${paid > 0 ? 'color: #15803d; font-weight: bold;' : 'color: #64748b;'}">
                        ${paid > 0 ? formatCurrency(paid) : 'Unpaid'}
                    </td>
                    <td style="padding: 5px 6px; border: 1px solid #cbd5e1; text-align: right; font-family: monospace; font-weight: bold; font-size: 10px; ${balance > 0 ? 'color: #b45309;' : 'color: #15803d;'}">
                        ${balance > 0 ? formatCurrency(balance) : '₱0.00'}
                    </td>
                </tr>
            `;
        }).join('');
    }

    const tableFootHtml = `
        <tr style="background: #f1f5f9; font-weight: bold; page-break-inside: avoid;">
            <td colspan="3" style="padding: 6px; border: 1px solid #cbd5e1; text-align: right; font-size: 9.5px; text-transform: uppercase;">Reconciled Audit Totals:</td>
            <td style="padding: 6px; border: 1px solid #cbd5e1; font-size: 9.5px;">${s.total_nights_sold || 0} Nights</td>
            <td style="padding: 6px; border: 1px solid #cbd5e1; text-align: center; font-size: 8.5px; color: #475569;">${s.confirmed_bookings || 0} Confirmed / ${rows.length} Total</td>
            <td style="padding: 6px; border: 1px solid #cbd5e1; text-align: right; font-size: 11px; font-family: monospace; color: #15803d;">${formatCurrency(roomSales)}</td>
            <td style="padding: 6px; border: 1px solid #cbd5e1;"></td>
            <td style="padding: 6px; border: 1px solid #cbd5e1; text-align: right; font-size: 10px; font-family: monospace; color: #15803d;">${formatCurrency(deposits)}</td>
            <td style="padding: 6px; border: 1px solid #cbd5e1; text-align: right; font-size: 11px; font-family: monospace; color: #b45309;">${formatCurrency(pendingBal)}</td>
        </tr>
    `;

    // 3. Special Remarks & Handover Notes List for Print
    const remarks = d.special_remarks || [];
    let remarksListHtml = '';
    if (remarks.length === 0) {
        remarksListHtml = `<div style="font-size: 9.5px; color: #64748b; font-style: italic;">No exception holds or flight arrival delays logged for this cycle.</div>`;
    } else {
        remarksListHtml = remarks.map(r => `
            <div style="font-size: 9.5px; margin-bottom: 2px;">
                <strong style="color: #4338ca;">[${r.category}]</strong> 
                <strong>${r.ref_id}</strong> (${r.room} - ${r.guest}): 
                <span style="color: #334155;">${r.note}</span>
            </div>
        `).join('');
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
                @page { 
                    size: landscape; 
                    margin: 8mm 10mm; 
                    @bottom-right { content: "Page " counter(page) " of " counter(pages); }
                }
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
                    color: #0f172a;
                    background: #fff;
                    margin: 0;
                    padding: 12px;
                    font-size: 11px;
                    line-height: 1.35;
                }
                .header-container {
                    display: flex;
                    justify-content: space-between;
                    align-items: flex-start;
                    border-bottom: 2px solid #0f172a;
                    padding-bottom: 10px;
                    margin-bottom: 12px;
                }
                .hotel-brand {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
                .brand-emblem {
                    width: 44px;
                    height: 44px;
                    background: #4338ca;
                    color: #fff;
                    font-size: 24px;
                    font-weight: 800;
                    font-family: Georgia, serif;
                    border-radius: 8px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .hotel-title {
                    font-size: 19px;
                    font-weight: 800;
                    color: #0f172a;
                    letter-spacing: -0.3px;
                }
                .hotel-subtitle {
                    font-size: 10px;
                    color: #64748b;
                    margin-top: 1px;
                }
                .report-meta {
                    text-align: right;
                    font-size: 10px;
                }
                .classification-tag {
                    display: inline-block;
                    background: #dc2626;
                    color: #fff;
                    font-weight: 800;
                    font-size: 9.5px;
                    padding: 3px 8px;
                    border-radius: 4px;
                    margin-bottom: 3px;
                    letter-spacing: 0.5px;
                }
                .report-title-strip {
                    background: #f1f5f9;
                    padding: 8px 12px;
                    border-radius: 6px;
                    font-weight: bold;
                    font-size: 13px;
                    color: #0f172a;
                    margin-bottom: 12px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    border-left: 4px solid #4338ca;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 14px;
                }
                tr { page-break-inside: avoid; }
                .sign-off-container {
                    margin-top: 14px;
                    border: 1px solid #cbd5e1;
                    border-radius: 8px;
                    padding: 12px;
                    background: #f8fafc;
                    page-break-inside: avoid;
                }
                .sign-off-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 30px;
                    margin-top: 12px;
                }
                .signature-box {
                    border-top: 1px solid #475569;
                    padding-top: 5px;
                    font-size: 10px;
                }
                .system-footer {
                    margin-top: 14px;
                    border-top: 1px dashed #cbd5e1;
                    padding-top: 6px;
                    display: flex;
                    justify-content: space-between;
                    font-size: 9px;
                    color: #64748b;
                    page-break-inside: avoid;
                }
                .no-print {
                    margin-bottom: 14px;
                    padding: 10px 14px;
                    background: #f8fafc;
                    border: 1px solid #e2e8f0;
                    border-radius: 8px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .print-btn {
                    background: #4338ca;
                    color: #fff;
                    border: none;
                    padding: 7px 16px;
                    border-radius: 6px;
                    font-weight: bold;
                    cursor: pointer;
                    font-size: 11px;
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
                    <strong>Official Hotel Audit Preview</strong> &bull; Conforms to 5 Key Structural Reporting Standards.
                </div>
                <button class="print-btn" onclick="window.print()">Print / Save as PDF</button>
            </div>

            <!-- 1. DOCUMENT HEADER -->
            <div class="header-container">
                <div class="hotel-brand">
                    <div class="brand-emblem">W</div>
                    <div>
                        <div class="hotel-title">${hotelName}</div>
                        <div class="hotel-subtitle">${hotelAddress}</div>
                    </div>
                </div>
                <div class="report-meta">
                    <div class="classification-tag">CONFIDENTIAL // OFFICIAL PROPERTY AUDIT</div>
                    <div><strong>Audit Ref #:</strong> <span style="font-family: monospace;">${reportRef}</span></div>
                    <div><strong>Generated:</strong> ${generatedDate}</div>
                    <div><strong>System:</strong> W Island Hub PMS v2.4.0 (Enterprise)</div>
                </div>
            </div>

            <!-- Document Classification Title Strip -->
            <div class="report-title-strip">
                <span>${reportTitle}</span>
                <span style="font-size: 11px; color: #475569; font-weight: normal;">Reporting Cycle: <strong>${datePeriod}</strong></span>
            </div>

            <!-- 2. OPERATIONAL SNAPSHOT (EXECUTIVE KPI BAR) -->
            ${kpiPrintHtml}

            <!-- 3. MAIN RESERVATION LEDGER (TABULAR DATA) -->
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

            <!-- 4. VERIFICATION AND SIGN-OFF BLOCK -->
            <div class="sign-off-container">
                <div style="font-size: 11px; font-weight: bold; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">
                    4. Verification and Sign-Off Block &bull; Special Remarks & Accountability Signatures
                </div>

                <!-- Special Remarks -->
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; margin-bottom: 12px;">
                    <div style="font-size: 9.5px; font-weight: bold; text-transform: uppercase; color: #64748b; margin-bottom: 4px;">
                        Special Remarks & Operational Handover Notes:
                    </div>
                    ${remarksListHtml}
                    <div style="margin-top: 6px; font-size: 9.5px; color: #1e293b; background: #f8fafc; padding: 5px 8px; border-left: 3px solid #4338ca; border-radius: 2px;">
                        <strong>Front-Desk Handover Log:</strong> ${handoverText}
                    </div>
                </div>

                <!-- Accountability Signatures -->
                <div class="sign-off-grid">
                    <div>
                        <div style="font-size: 9.5px; color: #475569; font-style: italic; margin-bottom: 24px; min-height: 28px;">
                            "I hereby certify that all room inventory counts, arrivals/departures movements, and key handovers for this operational cycle have been personally verified and matched against physical room status."
                        </div>
                        <div class="signature-box">
                            <strong>Front-Office Supervisor on Duty</strong> &bull; Operations Lead<br>
                            <span>Printed Name & Signature &bull; Shift: [  ] AM [  ] PM [  ] Night &bull; Date: _________________</span>
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 9.5px; color: #475569; font-style: italic; margin-bottom: 24px; min-height: 28px;">
                            "I have audited and balanced the reservation folio ledger, end-of-day revenue transactions, payment channel reconciliations, and confirmed outstanding folio balances for hotel administration."
                        </div>
                        <div class="signature-box">
                            <strong>Night Auditor / General Manager</strong> &bull; Executive Sign-Off<br>
                            <span>Printed Name & Signature &bull; Official Property Seal &bull; Date: _________________</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. SYSTEM FOOTER -->
            <div class="system-footer">
                <div>
                    <strong>W Island Hub PMS v2.4.0 (Enterprise Reservation Engine)</strong> &bull; DB Instance: <code>w_island_hub</code> &bull; Ref: ${reportRef}
                </div>
                <div>
                    CONFIDENTIAL &bull; Official Hotel Audit Record &bull; Generated: ${generatedDate}
                </div>
            </div>

            <script>
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
 * Export Current Report Data to CSV format adhering to structural fields
 */
function exportReportCSV() {
    if (!currentReportData) {
        showToast('Please generate report data first', 'warning');
        return;
    }

    const d = currentReportData;
    const type = d.report_type || 'daily_summary';
    const ops = d.operational_snapshot || {};
    const s = d.summary || {};
    let csvRows = [];

    // 1. Header Meta & Classification
    csvRows.push([`"W Island Hub Backpackers Inn - Official Property Management System"`]);
    csvRows.push([`"Sitio Duli, Barangay Bucana, El Nido, Palawan 5313, Philippines"`]);
    csvRows.push([`"Report Title"`, `"${(d.report_title || 'DAILY RESERVATION SUMMARY & FRONT OFFICE NIGHT AUDIT').toUpperCase()}"`]);
    csvRows.push([`"Classification"`, `"CONFIDENTIAL // OFFICIAL PROPERTY AUDIT"`]);
    csvRows.push([`"Reference #"`, `"${d.report_ref || ''}"`]);
    csvRows.push([`"Reporting Cycle"`, `"${d.date_range_label || ''}"`]);
    csvRows.push([`"Generated At"`, `"${d.generated_at_formatted || d.generated_at || ''}"`]);
    csvRows.push([]); // Empty row

    // 2. Operational Snapshot Summary
    csvRows.push([`"--- OPERATIONAL SNAPSHOT (EXECUTIVE KPI BAR) ---"`]);
    csvRows.push([`"Occupancy Percentage"`, `"${ops.occupancy_percentage || 0}%"`]);
    csvRows.push([`"Rooms Occupied"`, `"${ops.occupied_rooms || 0} / ${ops.rentable_rooms || 0} Rentable Rooms"`]);
    csvRows.push([`"Total Property Inventory"`, `"${ops.total_inventory || 0} (Maintenance: ${ops.maintenance_rooms || 0})"`]);
    csvRows.push([`"Expected Check-Ins"`, `"${ops.expected_check_ins || s.expected_arrivals || 0}"`]);
    csvRows.push([`"Expected Check-Outs"`, `"${ops.expected_check_outs || s.expected_departures || 0}"`]);
    csvRows.push([`"Stay-Overs (In-House)"`, `"${ops.stay_overs || s.in_house_guests || 0}"`]);
    csvRows.push([`"Vacant Clean / Dirty"`, `"${ops.vacant_clean || 0} Clean / ${ops.vacant_dirty || 0} Hold"`]);
    csvRows.push([`"Confirmed Room Sales"`, parseFloat(ops.room_sales || s.confirmed_revenue || 0).toFixed(2)]);
    csvRows.push([`"Average Daily Rate (ADR)"`, parseFloat(ops.average_daily_rate || s.average_daily_rate || 0).toFixed(2)]);
    csvRows.push([`"Total Pending Folio Balance"`, parseFloat(ops.total_pending_balance || s.pending_revenue || 0).toFixed(2)]);
    csvRows.push([`"Total Deposits Collected"`, parseFloat(ops.total_deposits_collected || 0).toFixed(2)]);
    csvRows.push([]); // Empty row

    // 3. Main Reservation Ledger Tabular Data
    csvRows.push([`"--- MAIN RESERVATION LEDGER (TABULAR DATA) ---"`]);
    csvRows.push([
        `"Booking Ref ID"`,
        `"Reservation Date"`,
        `"Primary Guest Name"`,
        `"Guest Email"`,
        `"Guest Phone"`,
        `"Occupants Pax"`,
        `"Room Assigned"`,
        `"Room Category/Tier"`,
        `"Check-In Date"`,
        `"Check-Out Date"`,
        `"Total Nights"`,
        `"Operational Status"`,
        `"Total Room Charges (PHP)"`,
        `"Payment Channel"`,
        `"Deposit Paid (PHP)"`,
        `"Folio Balance Due (PHP)"`,
        `"Special Remarks"`
    ]);

    (d.rows || []).forEach(b => {
        csvRows.push([
            `"${b.ref_id}"`,
            `"${b.created_at_formatted || b.created_at || ''}"`,
            `"${b.final_guest_name}"`,
            `"${b.guest_email || 'N/A'}"`,
            `"${b.guest_phone || 'N/A'}"`,
            `"${b.occupants_summary || (b.room_capacity + ' Pax')}"`,
            `"${b.room_name || ('Room #' + b.room_id)}"`,
            `"${b.room_type || 'Standard'}"`,
            `"${b.check_in_formatted || b.check_in}"`,
            `"${b.check_out_formatted || b.check_out}"`,
            b.nights || 1,
            `"${b.operational_status || b.status}"`,
            parseFloat(b.total_charges !== undefined ? b.total_charges : (b.total_price || 0)).toFixed(2),
            `"${b.payment_channel || b.payment_method || 'Desk'}"`,
            parseFloat(b.paid_amount || 0).toFixed(2),
            parseFloat(b.balance_due !== undefined ? b.balance_due : 0).toFixed(2),
            `"${(b.special_requests || '').replace(/"/g, '""')}"`
        ]);
    });

    const csvContent = "\uFEFF" + csvRows.map(e => e.join(",")).join("\n");
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.setAttribute("href", url);
    link.setAttribute("download", `hotel_operational_audit_${type}_${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    showToast('CSV audit report downloaded successfully', 'success');
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

