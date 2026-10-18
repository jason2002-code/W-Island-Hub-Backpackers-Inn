<?php
// Try to connect to database - don't fail the whole page
$dbConnected = false;
$stats = [
    'totalUsers' => 0,
    'totalBookings' => 0,
    'totalRooms' => 0,
    'totalRevenue' => 0,
    'pendingBookings' => 0,
    'confirmedBookings' => 0,
    'cancelledBookings' => 0
];

try {
    include_once __DIR__ . '/../../config/db.php';
    
    if (function_exists('getDB')) {
        $pdo = getDB();
        if ($pdo) {
            $dbConnected = true;
            
            // Total users (archived accounts are excluded)
            $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE is_archived = 0");
            $stats['totalUsers'] = $stmt ? intval($stmt->fetchColumn()) : 0;
            
            // Total bookings
            $stmt = $pdo->query("SELECT COUNT(*) FROM bookings");
            $stats['totalBookings'] = $stmt ? intval($stmt->fetchColumn()) : 0;
            
            // Total rooms
            $stmt = $pdo->query("SELECT COUNT(*) FROM rooms");
            $stats['totalRooms'] = $stmt ? intval($stmt->fetchColumn()) : 0;
            
            // Total revenue (confirmed bookings)
            $stmt = $pdo->query("SELECT COALESCE(SUM(COALESCE(NULLIF(total_amount, 0), total_price)), 0) FROM bookings WHERE status = 'Confirmed'");
            $stats['totalRevenue'] = $stmt ? floatval($stmt->fetchColumn()) : 0;
            
            // Pending bookings
            $stmt = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Pending'");
            $stats['pendingBookings'] = $stmt ? intval($stmt->fetchColumn()) : 0;
            
            // Confirmed bookings
            $stmt = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Confirmed'");
            $stats['confirmedBookings'] = $stmt ? intval($stmt->fetchColumn()) : 0;
            
            // Cancelled bookings
            $stmt = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Cancelled'");
            $stats['cancelledBookings'] = $stmt ? intval($stmt->fetchColumn()) : 0;
        }
    }
} catch (Exception $e) {
    $dbConnected = false;
}
?>

<!-- 1. HERO WELCOME & OPERATIONAL PULSE BANNER -->
<div class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-5 sm:p-7 lg:p-8 mb-6 sm:mb-8 shadow-xl border border-slate-800/80">
    <!-- Ambient glow decorative blobs -->
    <div class="absolute -right-12 -bottom-12 w-56 h-56 bg-brand-purple/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute right-40 -top-12 w-44 h-44 bg-sky-500/15 rounded-full blur-2xl pointer-events-none"></div>
    
    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-5">
        <div>
            <div class="flex flex-wrap items-center gap-2 mb-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> PMS Active & Online
                </span>
                <span class="text-xs text-slate-400 hidden sm:inline">• Panglao Island, Bohol</span>
                <span class="text-xs text-slate-400 hidden lg:inline">• <?php echo date('l, F j, Y'); ?></span>
            </div>
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight">
                Welcome back, <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-300 via-indigo-200 to-sky-300"><?php echo htmlspecialchars($adminName); ?></span> 👋
            </h1>
            <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-xl leading-relaxed">
                Property overview and live activity stream for W Island Hub Backpackers Inn.
            </p>
        </div>
        
        <!-- Quick Action Buttons on Banner -->
        <div class="flex flex-wrap items-center gap-2 sm:gap-2.5 shrink-0">
            <button onclick="showAdminSection('manage-bookings')" class="px-3.5 py-2.5 rounded-xl bg-brand-purple hover:bg-purple-600 text-white text-xs sm:text-sm font-bold transition flex items-center gap-2 shadow-md shadow-brand-purple/30 active:scale-95">
                <i class="fas fa-calendar-check"></i>
                <span>Bookings</span>
                <?php if ($stats['pendingBookings'] > 0): ?>
                    <span class="bg-amber-400 text-slate-950 text-[10px] font-black px-1.5 py-0.5 rounded-full"><?php echo $stats['pendingBookings']; ?></span>
                <?php endif; ?>
            </button>
            <button onclick="showAdminSection('manage-reports')" class="px-3.5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs sm:text-sm font-semibold backdrop-blur transition flex items-center gap-2 border border-white/10 active:scale-95">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Reports</span>
            </button>
            <button onclick="loadDashboardAll()" class="w-10 h-10 rounded-xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition border border-white/10 active:scale-95" title="Refresh Dashboard Data">
                <i class="fas fa-sync-alt text-xs" id="dash-refresh-icon"></i>
            </button>
        </div>
    </div>
</div>

<!-- 2. TOP STATS CARDS (Responsive 2x2 on Mobile, 4x1 on Desktop) -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-6 mb-6 sm:mb-8">
    <!-- Card 1: Total Revenue -->
    <div class="dash-card bg-white p-4 sm:p-6 rounded-3xl shadow-sm border border-slate-200/70 flex flex-col justify-between cursor-pointer hover:shadow-xl hover:border-purple-200 transition-all duration-300 group active:scale-[0.98]" onclick="showRevenueDetailsModal()">
        <div class="flex items-start justify-between gap-2 mb-3">
            <span class="text-xs sm:text-sm text-slate-500 font-semibold flex items-center gap-1 truncate">
                Total Revenue
                <i class="fas fa-info-circle text-brand-purple text-xs opacity-70 group-hover:opacity-100 transition-opacity" title="Click for custom date breakdown"></i>
            </span>
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-gradient-to-tr from-blue-500 to-indigo-600 text-white flex items-center justify-center text-lg sm:text-xl shadow-md shadow-blue-500/20 shrink-0 group-hover:scale-110 transition-transform">
                <i class="fas fa-peso-sign"></i>
            </div>
        </div>
        <div>
            <h3 class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-slate-900 tracking-tight" id="dash-total-revenue">
                ₱<?php echo number_format($stats['totalRevenue'], 2); ?>
            </h3>
            <p class="text-[11px] sm:text-xs text-slate-400 mt-1 flex items-center gap-1 font-medium">
                <span class="text-emerald-600 font-bold"><i class="fas fa-check-circle"></i> Confirmed</span> earnings
            </p>
        </div>
    </div>

    <!-- Card 2: Total Bookings -->
    <div class="dash-card bg-white p-4 sm:p-6 rounded-3xl shadow-sm border border-slate-200/70 flex flex-col justify-between cursor-pointer hover:shadow-xl hover:border-purple-200 transition-all duration-300 group active:scale-[0.98]" onclick="showAdminSection('manage-bookings')">
        <div class="flex items-start justify-between gap-2 mb-3">
            <span class="text-xs sm:text-sm text-slate-500 font-semibold truncate">Total Bookings</span>
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-gradient-to-tr from-brand-purple to-purple-600 text-white flex items-center justify-center text-lg sm:text-xl shadow-md shadow-purple-500/20 shrink-0 group-hover:scale-110 transition-transform">
                <i class="fas fa-bookmark"></i>
            </div>
        </div>
        <div>
            <h3 class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-slate-900 tracking-tight" id="dash-total-bookings">
                <?php echo $stats['totalBookings']; ?>
            </h3>
            <p class="text-[11px] sm:text-xs text-slate-400 mt-1 font-medium truncate">
                <span class="text-slate-700 font-bold"><?php echo $stats['confirmedBookings']; ?></span> Confirmed • <span class="text-amber-600 font-bold"><?php echo $stats['pendingBookings']; ?></span> Pending
            </p>
        </div>
    </div>

    <!-- Card 3: Total Users -->
    <div class="dash-card bg-white p-4 sm:p-6 rounded-3xl shadow-sm border border-slate-200/70 flex flex-col justify-between cursor-pointer hover:shadow-xl hover:border-emerald-200 transition-all duration-300 group active:scale-[0.98]" onclick="showAdminSection('manage-users')">
        <div class="flex items-start justify-between gap-2 mb-3">
            <span class="text-xs sm:text-sm text-slate-500 font-semibold truncate">Total Users</span>
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-600 text-white flex items-center justify-center text-lg sm:text-xl shadow-md shadow-emerald-500/20 shrink-0 group-hover:scale-110 transition-transform">
                <i class="fas fa-users"></i>
            </div>
        </div>
        <div>
            <h3 class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-slate-900 tracking-tight" id="dash-total-users">
                <?php echo $stats['totalUsers']; ?>
            </h3>
            <p class="text-[11px] sm:text-xs text-slate-400 mt-1 font-medium">
                Active registered accounts
            </p>
        </div>
    </div>

    <!-- Card 4: Total Rooms -->
    <div class="dash-card bg-white p-4 sm:p-6 rounded-3xl shadow-sm border border-slate-200/70 flex flex-col justify-between cursor-pointer hover:shadow-xl hover:border-amber-200 transition-all duration-300 group active:scale-[0.98]" onclick="showAdminSection('manage-rooms')">
        <div class="flex items-start justify-between gap-2 mb-3">
            <span class="text-xs sm:text-sm text-slate-500 font-semibold truncate">Total Rooms</span>
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-gradient-to-tr from-amber-500 to-orange-500 text-white flex items-center justify-center text-lg sm:text-xl shadow-md shadow-amber-500/20 shrink-0 group-hover:scale-110 transition-transform">
                <i class="fas fa-door-open"></i>
            </div>
        </div>
        <div>
            <h3 class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-slate-900 tracking-tight" id="dash-total-rooms">
                <?php echo $stats['totalRooms']; ?>
            </h3>
            <p class="text-[11px] sm:text-xs text-slate-400 mt-1 font-medium">
                Standard & Dorm units
            </p>
        </div>
    </div>
</div>

<!-- 3. QUICK ACTIONS SHORTCUT HUB -->
<div class="bg-white rounded-3xl p-4 sm:p-6 shadow-sm border border-slate-200/70 mb-6 sm:mb-8">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h4 class="font-bold text-slate-800 text-sm sm:text-base">Quick Operations Hub</h4>
            <p class="text-xs text-slate-400">Direct shortcuts to frequent management tasks</p>
        </div>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-4">
        <button onclick="showAdminSection('manage-bookings')" class="p-3.5 rounded-2xl bg-slate-50 hover:bg-purple-50 text-slate-700 hover:text-brand-purple border border-slate-200/70 hover:border-purple-200 transition text-left flex flex-col gap-2 group active:scale-[0.98]">
            <div class="w-8 h-8 rounded-xl bg-purple-100 text-brand-purple flex items-center justify-center text-sm shadow-2xs group-hover:scale-110 transition-transform">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div>
                <span class="font-bold text-xs sm:text-sm block">Reservations</span>
                <span class="text-[10px] text-slate-400">View & Verify Bookings</span>
            </div>
        </button>

        <button onclick="showAdminSection('manage-prices')" class="p-3.5 rounded-2xl bg-slate-50 hover:bg-blue-50 text-slate-700 hover:text-sky-600 border border-slate-200/70 hover:border-sky-200 transition text-left flex flex-col gap-2 group active:scale-[0.98]">
            <div class="w-8 h-8 rounded-xl bg-blue-100 text-sky-600 flex items-center justify-center text-sm shadow-2xs group-hover:scale-110 transition-transform">
                <i class="fas fa-tags"></i>
            </div>
            <div>
                <span class="font-bold text-xs sm:text-sm block">Room Prices</span>
                <span class="text-[10px] text-slate-400">Adjust Nightly Rates</span>
            </div>
        </button>

        <button onclick="showAdminSection('manage-reports')" class="p-3.5 rounded-2xl bg-slate-50 hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 border border-slate-200/70 hover:border-emerald-200 transition text-left flex flex-col gap-2 group active:scale-[0.98]">
            <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm shadow-2xs group-hover:scale-110 transition-transform">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div>
                <span class="font-bold text-xs sm:text-sm block">Night Audit</span>
                <span class="text-[10px] text-slate-400">Audit & Revenue Ledger</span>
            </div>
        </button>

        <button onclick="showAdminSection('manage-reports'); if (typeof switchReportTab === 'function') switchReportTab('feedback');" class="p-3.5 rounded-2xl bg-slate-50 hover:bg-amber-50 text-slate-700 hover:text-amber-700 border border-slate-200/70 hover:border-amber-200 transition text-left flex flex-col gap-2 group active:scale-[0.98]">
            <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-sm shadow-2xs group-hover:scale-110 transition-transform">
                <i class="fas fa-comments"></i>
            </div>
            <div>
                <span class="font-bold text-xs sm:text-sm block">Guest Inquiries</span>
                <span class="text-[10px] text-slate-400">Messages & Support</span>
            </div>
        </button>
    </div>
</div>

<!-- 4. CHARTS SECTION (Responsive Layout) -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6 sm:mb-8">
    <!-- Revenue Analytics Chart -->
    <div class="lg:col-span-2 bg-white p-5 sm:p-6 rounded-3xl shadow-sm border border-slate-200/70">
        <div class="flex flex-wrap justify-between items-center gap-2 mb-4">
            <div>
                <h4 class="font-bold text-slate-900 text-sm sm:text-base">Revenue Analytics</h4>
                <p class="text-xs text-slate-400">6-Month confirmed revenue trajectory</p>
            </div>
            <button onclick="showRevenueDetailsModal()" type="button" class="text-xs font-bold text-brand-purple hover:text-indigo-700 bg-purple-50 hover:bg-purple-100 border border-purple-100 px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 shadow-2xs cursor-pointer active:scale-95">
                <i class="fas fa-calendar-alt text-brand-purple"></i> Custom Date & Trends
            </button>
        </div>
        <div class="h-60 sm:h-72 w-full">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Booking Status Doughnut Chart -->
    <div class="bg-white p-5 sm:p-6 rounded-3xl shadow-sm border border-slate-200/70 flex flex-col justify-between">
        <div>
            <h4 class="font-bold text-slate-900 text-sm sm:text-base">Booking Status Distribution</h4>
            <p class="text-xs text-slate-400 mb-4">Confirmed vs Pending vs Cancelled</p>
        </div>
        <div class="h-56 sm:h-64 flex justify-center items-center">
            <canvas id="roomStatusChart"></canvas>
        </div>
        <div class="grid grid-cols-3 gap-2 text-center pt-3 border-t border-slate-100 text-xs">
            <div>
                <span class="block font-bold text-emerald-600"><?php echo $stats['confirmedBookings']; ?></span>
                <span class="text-[10px] text-slate-400">Confirmed</span>
            </div>
            <div>
                <span class="block font-bold text-amber-600"><?php echo $stats['pendingBookings']; ?></span>
                <span class="text-[10px] text-slate-400">Pending</span>
            </div>
            <div>
                <span class="block font-bold text-rose-600"><?php echo $stats['cancelledBookings']; ?></span>
                <span class="text-[10px] text-slate-400">Cancelled</span>
            </div>
        </div>
    </div>
</div>

<!-- 5. ROOMS OVERVIEW GRID -->
<div class="mb-6 sm:mb-8">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h4 class="font-bold text-slate-900 text-base sm:text-lg">Room Inventory Overview</h4>
            <p class="text-xs text-slate-400">Live property status & occupancy glance</p>
        </div>
        <button onclick="loadRoomsOverview()" class="text-xs sm:text-sm font-semibold text-brand-purple hover:text-brand-blue transition flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-purple-50 hover:bg-purple-100 border border-purple-100">
            <i class="fas fa-sync-alt text-xs"></i> Refresh Rooms
        </button>
    </div>
    <div id="rooms-overview-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3.5 sm:gap-4">
        <div class="col-span-full text-center text-slate-400 py-10 bg-white rounded-3xl border border-slate-200/70">
            <i class="fas fa-spinner fa-spin text-brand-purple text-xl mb-2"></i>
            <p class="text-xs">Loading rooms inventory...</p>
        </div>
    </div>
</div>

<!-- 6. RECENT BOOKINGS (Dual-View: Responsive Table on Tablet/Desktop, Touch Cards on Mobile) -->
<div class="bg-white rounded-3xl shadow-sm border border-slate-200/70 overflow-hidden mb-8">
    <div class="p-4 sm:p-6 border-b border-slate-100 flex justify-between items-center">
        <div>
            <h4 class="font-bold text-slate-900 text-base sm:text-lg">Recent Reservations</h4>
            <p class="text-xs text-slate-400">Latest guest bookings stream</p>
        </div>
        <button onclick="renderAdminBookings()" class="text-xs sm:text-sm font-semibold text-brand-purple hover:text-brand-blue transition flex items-center gap-1 hover:underline">
            View All <i class="fas fa-arrow-right text-[10px]"></i>
        </button>
    </div>

    <!-- Desktop Table View (Hidden on Mobile Screens) -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-slate-50/80 text-[11px] text-slate-400 uppercase tracking-wider border-b border-slate-100">
                <tr>
                    <th class="p-4 font-semibold">Ref ID</th>
                    <th class="p-4 font-semibold">Guest</th>
                    <th class="p-4 font-semibold">Room Accommodation</th>
                    <th class="p-4 font-semibold">Status</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-slate-100" id="recent-bookings-body">
                <tr>
                    <td colspan="4" class="p-8 text-center text-slate-400">
                        <i class="fas fa-spinner fa-spin text-brand-purple text-lg mb-2 block"></i>
                        Loading recent reservations...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Mobile Touch Cards Stack (Visible on Phones Only) -->
    <div class="md:hidden p-3.5 space-y-3" id="recent-bookings-cards">
        <div class="p-8 text-center text-slate-400">
            <i class="fas fa-spinner fa-spin text-brand-purple text-lg mb-2 block"></i>
            Loading reservations...
        </div>
    </div>
</div>

<!-- 7. ROOM DETAILS MODAL (Mobile-Optimized Responsive Bottom-Sheet / Dialog) -->
<div id="room-details-modal" class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full max-h-[92vh] flex flex-col overflow-hidden animate-in fade-in duration-200">
        <!-- Sticky Header -->
        <div class="sticky top-0 bg-white border-b border-slate-100 p-5 sm:p-6 flex justify-between items-start z-10">
            <div>
                <h3 id="room-details-title" class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight"></h3>
                <p id="room-details-type" class="text-xs sm:text-sm text-slate-500 font-medium"></p>
            </div>
            <button onclick="closeRoomDetailsModal()" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition active:scale-95">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <div class="p-5 sm:p-6 space-y-5 overflow-y-auto">
            <!-- Room Info Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50/70 p-4 rounded-2xl border border-slate-200/60">
                <div>
                    <p class="text-[11px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Price / Night</p>
                    <p id="room-details-price" class="text-base sm:text-lg font-extrabold text-brand-purple">₱0</p>
                </div>
                <div>
                    <p class="text-[11px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Capacity</p>
                    <p id="room-details-capacity" class="text-base sm:text-lg font-bold text-slate-800">0</p>
                </div>
                <div>
                    <p class="text-[11px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Status</p>
                    <div id="room-details-status" class="mt-0.5"><span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Available</span></div>
                </div>
                <div>
                    <p class="text-[11px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Total Revenue</p>
                    <p id="room-details-revenue" class="text-base sm:text-lg font-extrabold text-emerald-600">₱0</p>
                </div>
            </div>

            <!-- Amenities -->
            <div>
                <h4 class="font-bold text-slate-800 text-sm mb-1.5 flex items-center gap-1.5">
                    <i class="fas fa-concierge-bell text-xs text-brand-purple"></i> Room Amenities
                </h4>
                <p id="room-details-amenities" class="text-xs sm:text-sm text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-200/50"></p>
            </div>

            <!-- Description -->
            <div>
                <h4 class="font-bold text-slate-800 text-sm mb-1.5 flex items-center gap-1.5">
                    <i class="fas fa-align-left text-xs text-brand-purple"></i> Room Description
                </h4>
                <p id="room-details-description" class="text-xs sm:text-sm text-slate-600 leading-relaxed bg-slate-50 p-3 rounded-xl border border-slate-200/50"></p>
            </div>

            <!-- Booking Stats -->
            <div class="bg-slate-50/70 p-4 rounded-2xl border border-slate-200/60">
                <h4 class="font-bold text-slate-800 text-sm mb-3">Historical Reservation Performance</h4>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="bg-white p-3 rounded-xl border border-slate-200/70 shadow-2xs">
                        <p class="text-[10px] text-slate-400 font-bold uppercase mb-0.5">Confirmed</p>
                        <p id="room-stats-confirmed" class="text-xl font-extrabold text-emerald-600">0</p>
                    </div>
                    <div class="bg-white p-3 rounded-xl border border-slate-200/70 shadow-2xs">
                        <p class="text-[10px] text-slate-400 font-bold uppercase mb-0.5">Pending</p>
                        <p id="room-stats-pending" class="text-xl font-extrabold text-amber-600">0</p>
                    </div>
                    <div class="bg-white p-3 rounded-xl border border-slate-200/70 shadow-2xs">
                        <p class="text-[10px] text-slate-400 font-bold uppercase mb-0.5">Cancelled</p>
                        <p id="room-stats-cancelled" class="text-xl font-extrabold text-rose-600">0</p>
                    </div>
                    <div class="bg-white p-3 rounded-xl border border-slate-200/70 shadow-2xs">
                        <p class="text-[10px] text-slate-400 font-bold uppercase mb-0.5">Total</p>
                        <p id="room-stats-total" class="text-xl font-extrabold text-brand-purple">0</p>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings for this Room -->
            <div>
                <h4 class="font-bold text-slate-800 text-sm mb-2">Recent Bookings for this Room</h4>
                <div class="overflow-x-auto rounded-xl border border-slate-200/70">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-slate-50 text-[11px] text-slate-400 uppercase tracking-wider">
                            <tr>
                                <th class="p-3 font-semibold">Guest</th>
                                <th class="p-3 font-semibold">Check-in</th>
                                <th class="p-3 font-semibold">Check-out</th>
                                <th class="p-3 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100" id="room-bookings-body">
                            <tr>
                                <td colspan="4" class="p-4 text-center text-slate-400">No bookings for this room yet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Function for the "View All" button in recent bookings
function renderAdminBookings() {
    if (typeof showAdminSection === 'function') {
        showAdminSection('manage-bookings');
    }
}

// Refresh whole dashboard with animation feedback
function loadDashboardAll() {
    const icon = document.getElementById('dash-refresh-icon');
    if (icon) icon.classList.add('fa-spin');
    
    loadRecentBookings();
    loadRoomsOverview();
    if (typeof initAdmin === 'function') {
        initAdmin();
    }
    
    setTimeout(() => {
        if (icon) icon.classList.remove('fa-spin');
    }, 800);
}

// Load rooms overview for dashboard
function loadRoomsOverview() {
    const container = document.getElementById('rooms-overview-grid');
    if (!container) return;

    fetch('../actions/rooms_action.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.rooms && data.rooms.length > 0) {
                container.innerHTML = data.rooms.map(room => {
                    const isOccupied = room.status === 'Occupied' || room.is_occupied || (room.remaining_beds !== undefined && room.remaining_beds === 0);
                    const statusBadge = room.status === 'Maintenance' 
                        ? '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Maintenance</span>' 
                        : (isOccupied 
                            ? '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200"><span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Occupied</span>' 
                            : '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Available</span>');
                    
                    const imgSrc = room.image && room.image.startsWith('http') ? room.image : (room.image ? '../' + room.image : '../assets/image/rooms/default.jpg');
                    const cap = room.capacity || 2;

                    return `
                        <div class="bg-white rounded-3xl p-3.5 sm:p-4 shadow-sm border border-slate-200/70 hover:shadow-xl hover:border-purple-200 transition-all duration-300 cursor-pointer group flex flex-col justify-between active:scale-[0.98]" onclick="viewRoomDetails('${room.id}')">
                            <div>
                                <div class="relative overflow-hidden rounded-2xl mb-3 aspect-video bg-slate-100">
                                    <img src="${imgSrc}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="${room.name}" onerror="this.src='../assets/image/rooms/default.jpg'">
                                    <div class="absolute top-2 left-2 bg-slate-900/80 backdrop-blur-xs text-white text-[10px] font-bold px-2 py-0.5 rounded-lg">
                                        ${room.type || 'Standard'}
                                    </div>
                                    <div class="absolute bottom-2 right-2 bg-white/95 backdrop-blur-xs text-slate-800 text-[10px] font-bold px-2 py-0.5 rounded-lg shadow-xs flex items-center gap-1">
                                        <i class="fas fa-user-friends text-[9px] text-brand-purple"></i> ${cap} Pax
                                    </div>
                                </div>
                                <h5 class="font-bold text-slate-900 text-sm group-hover:text-brand-purple transition-colors truncate">${room.name}</h5>
                            </div>
                            <div class="flex justify-between items-center mt-3 pt-3 border-t border-slate-100">
                                <span class="text-brand-purple font-extrabold text-sm sm:text-base">₱${parseFloat(room.price || 0).toLocaleString()}<span class="text-[10px] font-normal text-slate-400">/nt</span></span>
                                ${statusBadge}
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                container.innerHTML = '<div class="col-span-full text-center text-slate-400 py-8 bg-white rounded-3xl border border-slate-200/70">No rooms found. Create rooms in the Rooms section.</div>';
            }
        })
        .catch(error => {
            console.error('Error loading rooms:', error);
            container.innerHTML = '<div class="col-span-full text-center text-rose-500 py-8 bg-white rounded-3xl border border-slate-200/70">Error loading rooms.</div>';
        });
}

// View detailed room modal
function viewRoomDetails(roomId) {
    fetch('../actions/rooms_action.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.rooms) {
                const room = data.rooms.find(r => r.id == roomId);
                if (room) {
                    document.getElementById('room-details-title').innerText = room.name;
                    document.getElementById('room-details-type').innerText = room.type;
                    document.getElementById('room-details-price').innerText = '₱' + parseFloat(room.price || 0).toLocaleString();
                    document.getElementById('room-details-capacity').innerText = (room.capacity || 2) + ' Guest(s)';
                    
                    const modalIsOccupied = room.status === 'Occupied' || room.is_occupied || (room.remaining_beds !== undefined && room.remaining_beds === 0);
                    const modalStatusHtml = room.status === 'Maintenance' 
                        ? '<span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-700">Maintenance</span>' 
                        : (modalIsOccupied 
                            ? '<span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">Occupied</span>' 
                            : '<span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">Available</span>');
                    document.getElementById('room-details-status').innerHTML = modalStatusHtml;

                    let amenitiesText = 'No amenities specified';
                    try {
                        if (room.amenities && typeof room.amenities === 'string' && room.amenities.trim().startsWith('[')) {
                            const amenities = JSON.parse(room.amenities);
                            if (Array.isArray(amenities)) {
                                amenitiesText = amenities.join(', ');
                            }
                        } else if (room.amenities) {
                            amenitiesText = room.amenities;
                        }
                    } catch (e) {
                        amenitiesText = room.amenities || 'No amenities specified';
                    }
                    document.getElementById('room-details-amenities').innerText = amenitiesText;
                    document.getElementById('room-details-description').innerText = room.description || 'No description provided for this room.';

                    loadRoomBookings(roomId);
                    document.getElementById('room-details-modal').classList.remove('hidden');
                }
            }
        })
        .catch(error => console.error('Error fetching room details:', error));
}

// Load bookings for a specific room
function loadRoomBookings(roomId) {
    fetch('../actions/rooms_action.php')
        .then(r => r.json())
        .then(roomData => {
            if (roomData.success) {
                const room = roomData.rooms.find(rm => rm.id == roomId);
                if (!room) return;
                const roomName = room.name;

                fetch('../actions/bookings_action.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.bookings) {
                            const filteredBookings = data.bookings.filter(b => b.roomName === roomName || b.room_id == roomId);

                            const confirmed = filteredBookings.filter(b => b.status === 'Confirmed').length;
                            const pending = filteredBookings.filter(b => b.status === 'Pending').length;
                            const cancelled = filteredBookings.filter(b => b.status === 'Cancelled').length;
                            const total = filteredBookings.length;

                            const revenue = filteredBookings
                                .filter(b => b.status === 'Confirmed')
                                .reduce((sum, b) => sum + parseFloat(b.total || 0), 0);

                            document.getElementById('room-stats-confirmed').innerText = confirmed;
                            document.getElementById('room-stats-pending').innerText = pending;
                            document.getElementById('room-stats-cancelled').innerText = cancelled;
                            document.getElementById('room-stats-total').innerText = total;
                            document.getElementById('room-details-revenue').innerText = '₱' + revenue.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

                            const tbody = document.getElementById('room-bookings-body');
                            if (filteredBookings.length > 0) {
                                const recent = filteredBookings.slice(0, 5);
                                tbody.innerHTML = recent.map(b => `
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="p-3 font-semibold text-slate-800">${b.guestName || 'Guest'}</td>
                                        <td class="p-3 text-xs text-slate-600">${b.checkIn || '-'}</td>
                                        <td class="p-3 text-xs text-slate-600">${b.checkOut || '-'}</td>
                                        <td class="p-3"><span class="px-2 py-0.5 rounded-full text-xs font-semibold ${b.status === 'Confirmed' ? 'bg-emerald-100 text-emerald-700' : (b.status === 'Cancelled' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700')}">${b.status}</span></td>
                                    </tr>
                                `).join('');
                            } else {
                                tbody.innerHTML = '<tr><td colspan="4" class="p-4 text-center text-slate-400">No bookings recorded for this room yet.</td></tr>';
                            }
                        }
                    })
                    .catch(error => console.error('Error loading room bookings:', error));
            }
        })
        .catch(error => console.error('Error loading room data:', error));
}

// Close room details modal
function closeRoomDetailsModal() {
    const modal = document.getElementById('room-details-modal');
    if (modal) modal.classList.add('hidden');
}

// Load recent bookings for dashboard (Both Desktop Table & Mobile Cards)
function loadRecentBookings() {
    const tbody = document.getElementById('recent-bookings-body');
    const cardsContainer = document.getElementById('recent-bookings-cards');

    fetch('../actions/bookings_action.php')
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.success && data.bookings && data.bookings.length > 0) {
                const recentBookings = data.bookings.slice(0, 5);
                
                // Desktop Table Rows
                if (tbody) {
                    tbody.innerHTML = recentBookings.map(b => {
                        const statusClass = b.status === 'Confirmed' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : (b.status === 'Cancelled' ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-amber-50 text-amber-700 border-amber-200');
                        const dotClass = b.status === 'Confirmed' ? 'bg-emerald-500' : (b.status === 'Cancelled' ? 'bg-rose-500' : 'bg-amber-500');
                        const guestInitial = (b.guestName || 'G').charAt(0).toUpperCase();
                        const checkIn = b.checkIn || b.check_in || '-';
                        const checkOut = b.checkOut || b.check_out || '-';

                        return `
                            <tr class="hover:bg-purple-50/30 transition group">
                                <td class="p-4">
                                    <span class="font-mono text-xs font-bold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-xl border border-slate-200/80">${b.ref_id}</span>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-purple-100 to-indigo-100 text-brand-purple flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                            ${guestInitial}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 text-sm group-hover:text-brand-purple transition-colors">${b.guestName || 'Guest'}</p>
                                            <p class="text-[11px] text-slate-400 font-mono">${checkIn} &rarr; ${checkOut}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <p class="font-medium text-slate-800 text-sm">${b.roomName || 'Room'}</p>
                                    <p class="text-[11px] text-brand-purple font-bold">₱${parseFloat(b.total || 0).toLocaleString()}</p>
                                </td>
                                <td class="p-4">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border ${statusClass}">
                                        <span class="w-1.5 h-1.5 rounded-full ${dotClass}"></span>
                                        ${b.status}
                                    </span>
                                </td>
                            </tr>
                        `;
                    }).join('');
                }

                // Mobile Card Stack
                if (cardsContainer) {
                    cardsContainer.innerHTML = recentBookings.map(b => {
                        const statusClass = b.status === 'Confirmed' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : (b.status === 'Cancelled' ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-amber-50 text-amber-700 border-amber-200');
                        const dotClass = b.status === 'Confirmed' ? 'bg-emerald-500' : (b.status === 'Cancelled' ? 'bg-rose-500' : 'bg-amber-500');
                        const guestInitial = (b.guestName || 'G').charAt(0).toUpperCase();
                        const checkIn = b.checkIn || b.check_in || '-';
                        const checkOut = b.checkOut || b.check_out || '-';

                        return `
                            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="font-mono text-xs font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-lg border border-slate-200">${b.ref_id}</span>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold border ${statusClass}">
                                        <span class="w-1.5 h-1.5 rounded-full ${dotClass}"></span> ${b.status}
                                    </span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-purple-100 to-indigo-100 text-brand-purple flex items-center justify-center font-bold text-sm shrink-0 shadow-2xs">
                                        ${guestInitial}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-bold text-slate-900 text-sm truncate">${b.guestName || 'Guest'}</p>
                                        <p class="text-xs text-slate-500 font-medium">${b.roomName || 'Room'}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-extrabold text-brand-purple block">₱${parseFloat(b.total || 0).toLocaleString()}</span>
                                        <span class="text-[10px] text-slate-400">Total</span>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between text-xs text-slate-500 pt-2 border-t border-slate-100">
                                    <span><i class="far fa-calendar-alt text-[11px] mr-1 text-slate-400"></i> ${checkIn} &rarr; ${checkOut}</span>
                                    <button onclick="renderAdminBookings()" class="text-brand-purple font-semibold text-[11px] hover:underline">Manage &rarr;</button>
                                </div>
                            </div>
                        `;
                    }).join('');
                }
            } else {
                if (tbody) tbody.innerHTML = '<tr><td colspan="4" class="p-8 text-center text-slate-400">No reservations recorded yet.</td></tr>';
                if (cardsContainer) cardsContainer.innerHTML = '<div class="p-6 text-center text-slate-400 text-sm">No reservations recorded yet.</div>';
            }
        })
        .catch(error => {
            console.error('Error loading bookings:', error);
            if (tbody) tbody.innerHTML = '<tr><td colspan="4" class="p-8 text-center text-rose-500">Error loading reservations.</td></tr>';
            if (cardsContainer) cardsContainer.innerHTML = '<div class="p-6 text-center text-rose-500 text-sm">Error loading reservations.</div>';
        });
}

// Load data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadRecentBookings();
    loadRoomsOverview();
});
</script>
