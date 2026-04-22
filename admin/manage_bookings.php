<!-- ================= Manage Bookings Page ================= -->
<div id="manage-bookings" class="page-section">
    <div class="p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <h3 class="text-2xl font-bold">Booking Management</h3>
            <div class="flex gap-2 w-full sm:w-auto">
                <div class="relative flex-1 sm:flex-none">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="booking-search" placeholder="Search bookings..." 
                        class="pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-purple/20 w-full sm:w-48"
                        oninput="filterBookings()">
                </div>
                <select id="status-filter" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-purple/20" onchange="filterBookings()">
                    <option value="">All Status</option>
                    <option value="Pending">Pending</option>
                    <option value="Confirmed">Confirmed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
                <button onclick="loadBookings()" class="bg-brand-blue text-white px-4 py-2 rounded shadow hover:bg-sky-600 transition">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-xs text-gray-500 uppercase">Total Bookings</div>
                <div class="text-2xl font-bold text-brand-dark" id="stat-total">0</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-xs text-gray-500 uppercase">Pending</div>
                <div class="text-2xl font-bold text-yellow-600" id="stat-pending">0</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-xs text-gray-500 uppercase">Confirmed</div>
                <div class="text-2xl font-bold text-green-600" id="stat-confirmed">0</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-xs text-gray-500 uppercase">Cancelled</div>
                <div class="text-2xl font-bold text-red-600" id="stat-cancelled">0</div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[1000px]">
                    <thead>
                        <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <th class="p-4">Ref ID</th>
                            <th class="p-4">Guest</th>
                            <th class="p-4">Email</th>
                            <th class="p-4">Room</th>
                            <th class="p-4">Check In</th>
                            <th class="p-4">Check Out</th>
                            <th class="p-4">Total</th>
                            <th class="p-4">Status</th>
                            <th class="p-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="bookings-table-body" class="text-sm">
                        <tr>
                            <td colspan="9" class="p-4 text-center text-gray-400">Loading bookings...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
let allBookings = [];

function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function loadBookings() {
    fetch('../actions/bookings_action.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allBookings = data.bookings;
                updateStats();
                filterBookings();
            } else {
                document.getElementById('bookings-table-body').innerHTML = 
                    '<tr><td colspan="9" class="p-4 text-center text-red-400">' + data.message + '</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('bookings-table-body').innerHTML = 
                '<tr><td colspan="9" class="p-4 text-center text-red-400">Error loading bookings. Make sure the database is set up.</td></tr>';
        });
}

function updateStats() {
    const stats = { total: allBookings.length, pending: 0, confirmed: 0, cancelled: 0 };
    allBookings.forEach(b => {
        if (b.status === 'Pending') stats.pending++;
        else if (b.status === 'Confirmed') stats.confirmed++;
        else if (b.status === 'Cancelled') stats.cancelled++;
    });
    document.getElementById('stat-total').textContent = stats.total;
    document.getElementById('stat-pending').textContent = stats.pending;
    document.getElementById('stat-confirmed').textContent = stats.confirmed;
    document.getElementById('stat-cancelled').textContent = stats.cancelled;
}

function filterBookings() {
    const search = document.getElementById('booking-search').value.toLowerCase();
    const statusFilter = document.getElementById('status-filter').value;
    
    const filtered = allBookings.filter(booking => {
        const matchesSearch = !search || 
            booking.ref_id.toLowerCase().includes(search) ||
            booking.guestName.toLowerCase().includes(search) ||
            (booking.guestEmail && booking.guestEmail.toLowerCase().includes(search)) ||
            (booking.roomName && booking.roomName.toLowerCase().includes(search));
        const matchesStatus = !statusFilter || booking.status === statusFilter;
        return matchesSearch && matchesStatus;
    });
    
    renderBookings(filtered);
}

function renderBookings(bookings) {
    const tbody = document.getElementById('bookings-table-body');
    
    if (bookings.length > 0) {
        tbody.innerHTML = bookings.map(booking => `
            <tr class="border-t hover:bg-gray-50">
                <td class="p-4 font-mono text-gray-500">${booking.ref_id}</td>
                <td class="p-4 font-bold">${booking.guestName}</td>
                <td class="p-4 text-gray-500 text-xs">${booking.guestEmail || '-'}</td>
                <td class="p-4">${booking.roomName || 'N/A'}</td>
                <td class="p-4 text-gray-600">${formatDate(booking.checkIn)}</td>
                <td class="p-4 text-gray-600">${formatDate(booking.checkOut)}</td>
                <td class="p-4 font-bold text-brand-purple">₱${parseFloat(booking.total).toFixed(2)}</td>
                <td class="p-4">
                    <span class="px-2 py-1 rounded text-xs font-bold ${
                        booking.status === 'Confirmed' ? 'bg-green-100 text-green-700' : 
                        booking.status === 'Cancelled' ? 'bg-red-100 text-red-700' : 
                        'bg-yellow-100 text-yellow-700'
                    }">
                        ${booking.status}
                    </span>
                </td>
                <td class="p-4">
                    <div class="flex gap-2">
                        ${booking.status === 'Pending' ? `
                            <button onclick="updateBookingStatus(${booking.id}, 'Confirmed')" class="text-green-600 hover:bg-green-50 p-1 rounded transition" title="Confirm">
                                <i class="fas fa-check"></i>
                            </button>
                        ` : ''}
                        ${booking.status !== 'Cancelled' ? `
                            <button onclick="updateBookingStatus(${booking.id}, 'Cancelled')" class="text-red-600 hover:bg-red-50 p-1 rounded transition" title="Cancel">
                                <i class="fas fa-times"></i>
                            </button>
                        ` : ''}
                        <button onclick="deleteBooking(${booking.id})" class="text-gray-400 hover:text-red-600 p-1 rounded transition" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    } else {
        tbody.innerHTML = '<tr><td colspan="9" class="p-4 text-center text-gray-400">No bookings found.</td></tr>';
    }
}

function updateBookingStatus(bookingId, status) {
    showConfirm(`Are you sure you want to ${status.toLowerCase()} this booking?`, () => {
        fetch('../actions/bookings_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: bookingId, status: status })
        })
        .then(response => response.json())
        .then(data => {
            showToast(data.message || 'Status updated successfully!', data.success ? 'success' : 'error');
            if (data.success) loadBookings();
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error updating booking status', 'error');
        });
    });
}

function deleteBooking(bookingId) {
    showConfirm('Are you sure you want to delete this booking?', () => {
        fetch('../actions/bookings_action.php?id=' + bookingId, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            showToast(data.message || 'Booking deleted successfully!', data.success ? 'success' : 'error');
            if (data.success) loadBookings();
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error deleting booking', 'error');
        });
    });
}

// Load bookings when page loads
document.addEventListener('DOMContentLoaded', loadBookings);
</script>
