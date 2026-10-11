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
    <div class="dash-card bg-white p-6 rounded-2xl shadow-sm border border-gray-50 flex items-center justify-between cursor-pointer hover:shadow-lg transition-all duration-300 hover:-translate-y-1" onclick="showRevenueDetailsModal()">
        <div>
            <p class="text-sm text-gray-400 font-medium">Total Revenue <i class="fas fa-info-circle text-brand-purple ml-1" title="Click to view details"></i></p>
<h3 class="text-2xl font-bold text-brand-dark mt-1">₱<?php echo number_format($stats['totalRevenue'], 2); ?></h3>
        </div>
        <div class="w-12 h-12 rounded-full bg-blue-50 text-brand-blue flex items-center justify-center text-xl"><i class="fas fa-peso-sign"></i></div>
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
        <div class="flex flex-wrap justify-between items-center gap-2 mb-4">
            <div>
                <h4 class="font-bold text-gray-700">Revenue Analytics</h4>
                <p class="text-xs text-gray-400">Monthly breakdown & trend analysis</p>
            </div>
            <button onclick="showRevenueDetailsModal()" type="button" class="text-xs font-semibold text-brand-purple hover:text-indigo-700 bg-purple-50 hover:bg-purple-100 border border-purple-100 px-3.5 py-1.5 rounded-xl transition flex items-center gap-1.5 shadow-2xs cursor-pointer">
                <i class="fas fa-calendar-alt text-brand-purple"></i> Custom Date & Trends
            </button>
        </div>
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

<!-- Rooms Overview Grid -->
<div class="mb-8">
    <div class="flex justify-between items-center mb-4">
        <h4 class="font-bold text-gray-700 text-lg">Room Overview</h4>
        <button onclick="loadRoomsOverview()" class="text-sm text-brand-purple hover:text-brand-blue transition hover:underline">Refresh</button>
    </div>
    <div id="rooms-overview-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="col-span-full text-center text-gray-400 py-8">Loading rooms...</div>
    </div>
</div>

<!-- Recent Bookings Table -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-50 overflow-hidden">
    <div class="p-6 border-b border-gray-50 flex justify-between items-center">
        <h4 class="font-bold text-gray-700">Recent Bookings</h4>
        <button onclick="renderAdminBookings()" class="text-sm text-brand-purple hover:text-brand-blue transition hover:underline">View All</button>
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

<!-- Room Details Modal -->
<div id="room-details-modal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-100 p-6 flex justify-between items-start">
            <div>
                <h3 id="room-details-title" class="text-2xl font-bold text-brand-dark mb-1"></h3>
                <p id="room-details-type" class="text-sm text-gray-500"></p>
            </div>
            <button onclick="closeRoomDetailsModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="p-6 space-y-6">
            <!-- Room Info -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-gray-500 font-semibold uppercase mb-1">Price/Night</p>
                    <p id="room-details-price" class="text-lg font-bold text-brand-purple">₱0</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-semibold uppercase mb-1">Capacity</p>
                    <p id="room-details-capacity" class="text-lg font-bold text-gray-700">0</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-semibold uppercase mb-1">Status</p>
                    <p id="room-details-status" class="text-lg font-bold"><span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-600">Available</span></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-semibold uppercase mb-1">Total Revenue</p>
                    <p id="room-details-revenue" class="text-lg font-bold text-green-600">₱0</p>
                </div>
            </div>

            <!-- Amenities -->
            <div>
                <h4 class="font-bold text-gray-700 mb-2">Amenities</h4>
                <p id="room-details-amenities" class="text-sm text-gray-600"></p>
            </div>

            <!-- Description -->
            <div>
                <h4 class="font-bold text-gray-700 mb-2">Description</h4>
                <p id="room-details-description" class="text-sm text-gray-600"></p>
            </div>

            <!-- Booking Stats -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-bold text-gray-700 mb-4">Booking Statistics</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white p-3 rounded-lg border border-gray-100">
                        <p class="text-xs text-gray-500 font-semibold uppercase mb-1">Confirmed</p>
                        <p id="room-stats-confirmed" class="text-2xl font-bold text-green-600">0</p>
                    </div>
                    <div class="bg-white p-3 rounded-lg border border-gray-100">
                        <p class="text-xs text-gray-500 font-semibold uppercase mb-1">Pending</p>
                        <p id="room-stats-pending" class="text-2xl font-bold text-yellow-600">0</p>
                    </div>
                    <div class="bg-white p-3 rounded-lg border border-gray-100">
                        <p class="text-xs text-gray-500 font-semibold uppercase mb-1">Cancelled</p>
                        <p id="room-stats-cancelled" class="text-2xl font-bold text-red-600">0</p>
                    </div>
                    <div class="bg-white p-3 rounded-lg border border-gray-100">
                        <p class="text-xs text-gray-500 font-semibold uppercase mb-1">Total Bookings</p>
                        <p id="room-stats-total" class="text-2xl font-bold text-brand-purple">0</p>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings for this Room -->
            <div>
                <h4 class="font-bold text-gray-700 mb-3">Recent Bookings</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-400 uppercase tracking-wider">
                            <tr>
                                <th class="p-3 font-medium">Guest</th>
                                <th class="p-3 font-medium">Check-in</th>
                                <th class="p-3 font-medium">Check-out</th>
                                <th class="p-3 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50" id="room-bookings-body">
                            <tr>
                                <td colspan="4" class="p-4 text-center text-gray-400">No bookings yet.</td>
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
    // This function is called from the "View All" button in the recent bookings section.
    // It switches the view to the main booking management page.
    
    // The showAdminSection function is defined in dashboard.php
    if (typeof showAdminSection === 'function') {
        showAdminSection('manage-bookings');
    }

    // The setActiveAdminLink function is defined in admin_chart.js
    // We find the corresponding link in the sidebar and activate it.
    const bookingsLink = document.getElementById('nav-bookings');
    if (typeof setActiveAdminLink === 'function' && bookingsLink) setActiveAdminLink(bookingsLink);
}

// Load rooms overview for dashboard
function loadRoomsOverview() {
    const container = document.getElementById('rooms-overview-grid');

    fetch('../actions/rooms_action.php')
        .then(response => {
            console.log('Rooms API Response Status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Rooms Data:', data);

            if (data.success && data.rooms && data.rooms.length > 0) {
                console.log('Found ' + data.rooms.length + ' rooms');
                container.innerHTML = data.rooms.map(room => {
                    const isOccupied = room.status === 'Occupied' || room.is_occupied || (room.remaining_beds !== undefined && room.remaining_beds === 0);
                    const statusBadge = room.status === 'Maintenance' 
                        ? '<span class="px-2 py-1 bg-red-100 text-red-600 text-xs font-bold rounded">Maintenance</span>' 
                        : (isOccupied 
                            ? '<span class="px-2 py-1 bg-amber-100 text-amber-700 text-xs font-bold rounded">Occupied</span>' 
                            : '<span class="px-2 py-1 bg-green-100 text-green-600 text-xs font-bold rounded">Available</span>');
                    const imgSrc = room.image && room.image.startsWith('http') ? room.image : (room.image ? '../' + room.image : '../assets/image/rooms/default.jpg');

                    return `
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-50 hover:shadow-md transition cursor-pointer" onclick="viewRoomDetails('${room.id}')">
                            <img src="${imgSrc}" class="w-full h-24 object-cover rounded mb-3 bg-gray-200" alt="${room.name}">
                            <div class="mb-2">
                                <h5 class="font-bold text-gray-800 text-sm">${room.name}</h5>
                                <p class="text-xs text-gray-500">${room.type || 'N/A'}</p>
                            </div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-brand-purple font-bold">₱${room.price || '0'}</span>
                                ${statusBadge}
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                console.log('No rooms found or API error:', data);
                container.innerHTML = '<div class="col-span-full text-center text-gray-400 py-8">No rooms found. Create rooms in the Rooms section.</div>';
            }
        })
        .catch(error => {
            console.error('Error loading rooms:', error);
            container.innerHTML = '<div class="col-span-full text-center text-red-400 py-8">Error loading rooms: ' + error.message + '</div>';
        });
}

// View detailed room report
function viewRoomDetails(roomId) {
    // First fetch all rooms data
    fetch('../actions/rooms_action.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const room = data.rooms.find(r => r.id == roomId);
                if (room) {
                    // Display room info
                    document.getElementById('room-details-title').innerText = room.name;
                    document.getElementById('room-details-type').innerText = room.type + ' • Capacity: ' + room.capacity + ' guests';
                    document.getElementById('room-details-price').innerText = '₱' + room.price;
                    document.getElementById('room-details-capacity').innerText = room.capacity + ' Guest(s)';
                    const modalIsOccupied = room.status === 'Occupied' || room.is_occupied || (room.remaining_beds !== undefined && room.remaining_beds === 0);
                    const modalStatusHtml = room.status === 'Maintenance' 
                        ? '<span class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-600">Maintenance</span>' 
                        : (modalIsOccupied 
                            ? '<span class="px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Occupied</span>' 
                            : '<span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-600">Available</span>');
                    document.getElementById('room-details-status').innerHTML = modalStatusHtml;

                    // Parse amenities
                    let amenitiesText = 'No amenities';
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
                        amenitiesText = room.amenities || 'No amenities';
                    }
                    document.getElementById('room-details-amenities').innerText = amenitiesText;
                    document.getElementById('room-details-description').innerText = room.description || 'No description available.';

                    // Load bookings for this room
                    loadRoomBookings(roomId);

                    // Show modal
                    document.getElementById('room-details-modal').classList.remove('hidden');
                }
            }
        })
        .catch(error => console.error('Error fetching room details:', error));
}

// Load bookings for a specific room
function loadRoomBookings(roomId) {
    // First get room info to know the room name
    fetch('../actions/rooms_action.php')
        .then(r => r.json())
        .then(roomData => {
            if (roomData.success) {
                const room = roomData.rooms.find(rm => rm.id == roomId);
                if (!room) {
                    console.error('Room not found with ID:', roomId);
                    return;
                }

                const roomName = room.name;
                console.log('Looking for bookings for room:', roomName, 'ID:', roomId);

                // Now fetch bookings and filter by room name
                fetch('../actions/bookings_action.php')
                    .then(response => response.json())
                    .then(data => {
                        console.log('All bookings:', data);

                        if (data.success && data.bookings) {
                            const filteredBookings = data.bookings.filter(b => b.roomName === roomName);
                            console.log('Filtered bookings for ' + roomName + ':', filteredBookings);

                            // Calculate stats
                            const confirmed = filteredBookings.filter(b => b.status === 'Confirmed').length;
                            const pending = filteredBookings.filter(b => b.status === 'Pending').length;
                            const cancelled = filteredBookings.filter(b => b.status === 'Cancelled').length;
                            const total = filteredBookings.length;

                            // Calculate total revenue
                            const revenue = filteredBookings
                                .filter(b => b.status === 'Confirmed')
                                .reduce((sum, b) => sum + parseFloat(b.total || 0), 0);

                            document.getElementById('room-stats-confirmed').innerText = confirmed;
                            document.getElementById('room-stats-pending').innerText = pending;
                            document.getElementById('room-stats-cancelled').innerText = cancelled;
                            document.getElementById('room-stats-total').innerText = total;
                            document.getElementById('room-details-revenue').innerText = '₱' + revenue.toFixed(2);

                            // Display recent bookings (last 5)
                            const tbody = document.getElementById('room-bookings-body');
                            if (filteredBookings.length > 0) {
                                const recent = filteredBookings.slice(0, 5);
                                tbody.innerHTML = recent.map(b => `
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="p-3 font-medium text-gray-700">${b.guestName}</td>
                                        <td class="p-3 text-sm text-gray-600">${b.checkIn}</td>
                                        <td class="p-3 text-sm text-gray-600">${b.checkOut}</td>
                                        <td class="p-3"><span class="px-2 py-1 rounded-full text-xs font-medium ${b.status === 'Confirmed' ? 'bg-green-100 text-green-600' : (b.status === 'Cancelled' ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-600')}">${b.status}</span></td>
                                    </tr>
                                `).join('');
                            } else {
                                tbody.innerHTML = '<tr><td colspan="4" class="p-4 text-center text-gray-400">No bookings for this room yet.</td></tr>';
                            }
                        } else {
                            console.error('Error getting bookings:', data);
                        }
                    })
                    .catch(error => console.error('Error loading room bookings:', error));
            }
        })
        .catch(error => console.error('Error loading room data:', error));
}

// Close room details modal
function closeRoomDetailsModal() {
    document.getElementById('room-details-modal').classList.add('hidden');
}

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
    loadRoomsOverview();
});
</script>
