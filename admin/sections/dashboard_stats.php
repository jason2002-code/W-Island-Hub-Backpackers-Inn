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
    // Suppress errors and don't exit
    error_reporting(0);
    @include_once __DIR__ . '/../../config/db.php';
    
    if (function_exists('getDB')) {
        $pdo = getDB();
        if ($pdo) {
            $dbConnected = true;
            
            // Total users
            $stmt = $pdo->query("SELECT COUNT(*) FROM users");
            $stats['totalUsers'] = $stmt ? $stmt->fetchColumn() : 0;
            
            // Total bookings
            $stmt = $pdo->query("SELECT COUNT(*) FROM bookings");
            $stats['totalBookings'] = $stmt ? $stmt->fetchColumn() : 0;
            
            // Total rooms
            $stmt = $pdo->query("SELECT COUNT(*) FROM rooms");
            $stats['totalRooms'] = $stmt ? $stmt->fetchColumn() : 0;
            
            // Total revenue (confirmed bookings)
            $stmt = $pdo->query("SELECT COALESCE(SUM(total_price), 0) FROM bookings WHERE status = 'Confirmed'");
            $stats['totalRevenue'] = $stmt ? $stmt->fetchColumn() : 0;
            
            // Pending bookings
            $stmt = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Pending'");
            $stats['pendingBookings'] = $stmt ? $stmt->fetchColumn() : 0;
            
            // Confirmed bookings
            $stmt = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Confirmed'");
            $stats['confirmedBookings'] = $stmt ? $stmt->fetchColumn() : 0;
            
            // Cancelled bookings
            $stmt = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Cancelled'");
            $stats['cancelledBookings'] = $stmt ? $stmt->fetchColumn() : 0;
        }
    }
} catch (Exception $e) {
    $dbConnected = false;
}
?>

<!-- Top Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="dash-card bg-white p-6 rounded-2xl shadow-sm border border-gray-50 flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-400 font-medium">Total Revenue</p>
            <h3 class="text-2xl font-bold text-brand-dark mt-1">$<?php echo number_format($stats['totalRevenue'], 2); ?></h3>
        </div>
        <div class="w-12 h-12 rounded-full bg-blue-50 text-brand-blue flex items-center justify-center text-xl"><i class="fas fa-dollar-sign"></i></div>
    </div>
    <div class="dash-card bg-white p-6 rounded-2xl shadow-sm border border-gray-50 flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-400 font-medium">Total Bookings</p>
            <h3 class="text-2xl font-bold text-brand-dark mt-1"><?php echo $stats['totalBookings']; ?></h3>
        </div>
        <div class="w-12 h-12 rounded-full bg-purple-50 text-brand-purple flex items-center justify-center text-xl"><i class="fas fa-bookmark"></i></div>
    </div>
    <div class="dash-card bg-white p-6 rounded-2xl shadow-sm border border-gray-50 flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-400 font-medium">Total Users</p>
            <h3 class="text-2xl font-bold text-brand-dark mt-1"><?php echo $stats['totalUsers']; ?></h3>
        </div>
        <div class="w-12 h-12 rounded-full bg-green-50 text-green-500 flex items-center justify-center text-xl"><i class="fas fa-users"></i></div>
    </div>
    <div class="dash-card bg-white p-6 rounded-2xl shadow-sm border border-gray-50 flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-400 font-medium">Total Rooms</p>
            <h3 class="text-2xl font-bold text-brand-dark mt-1"><?php echo $stats['totalRooms']; ?></h3>
        </div>
        <div class="w-12 h-12 rounded-full bg-yellow-50 text-yellow-500 flex items-center justify-center text-xl"><i class="fas fa-door-open"></i></div>
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
        <h4 class="font-bold text-gray-700 mb-4">Booking Status</h4>
        <div class="h-52 flex justify-center">
            <canvas id="roomStatusChart"></canvas>
        </div>
    </div>
</div>

<!-- Recent Bookings Table -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-50 overflow-hidden">
    <div class="p-6 border-b border-gray-50 flex justify-between items-center">
        <h4 class="font-bold text-gray-700">Recent Bookings</h4>
        <button onclick="renderAdminBookings()" class="text-sm text-brand-purple hover:underline">View All</button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-gray-50/50 text-xs text-gray-400 uppercase tracking-wider">
                <tr>
                    <th class="p-4 font-medium">Ref ID</th>
                    <th class="p-4 font-medium">Guest</th>
                    <th class="p-4 font-medium">Room</th>
                    <th class="p-4 font-medium">Status</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-gray-50" id="recent-bookings-body">
                <tr>
                    <td colspan="4" class="p-8 text-center text-gray-400">Loading recent bookings...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
// Load recent bookings for dashboard
function loadRecentBookings() {
    const tbody = document.getElementById('recent-bookings-body');
    
    // Add timeout to prevent infinite loading
    const timeoutId = setTimeout(function() {
        tbody.innerHTML = '<tr><td colspan="4" class="p-8 text-center text-yellow-500">Database not configured. Please import schema.sql to phpMyAdmin.</td></tr>';
    }, 5000);
    
    fetch('../actions/bookings_action.php')
        .then(response => {
            clearTimeout(timeoutId);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.bookings && data.bookings.length > 0) {
                const recentBookings = data.bookings.slice(0, 5);
                tbody.innerHTML = recentBookings.map(b => `
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="p-4 font-mono text-gray-500">${b.ref_id}</td>
                        <td class="p-4 font-medium text-gray-700">${b.guestName}</td>
                        <td class="p-4 text-gray-500">${b.roomName}</td>
                        <td class="p-4"><span class="px-3 py-1 rounded-full text-xs font-medium ${b.status === 'Confirmed' ? 'bg-green-100 text-green-600' : (b.status === 'Cancelled' ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-600')}">${b.status}</span></td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="4" class="p-8 text-center text-gray-400">No bookings yet.</td></tr>';
            }
        })
        .catch(error => {
            clearTimeout(timeoutId);
            console.error('Error loading bookings:', error);
            tbody.innerHTML = '<tr><td colspan="4" class="p-8 text-center text-gray-400">Error loading bookings. Database may not be set up.</td></tr>';
        });
}

// Initialize dashboard charts
function initDashboardCharts() {
    // This will be handled by admin_chart.js
}

// Load data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadRecentBookings();
});
</script>
