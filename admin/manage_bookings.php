<!-- ================= Manage Bookings Page ================= -->
<div id="manage-bookings" class="page-section">
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-bold">Booking Management</h3>
            <button onclick="loadBookings()" class="bg-brand-blue text-white px-4 py-2 rounded shadow hover:bg-sky-600">
                ↻ Refresh
            </button>
        </div>
        
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[900px]">
                    <thead>
                        <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <th class="p-4">Ref ID</th>
                            <th class="p-4">Guest</th>
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
                            <td colspan="8" class="p-4 text-center text-gray-400">Loading bookings...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function loadBookings() {
    fetch('actions/bookings_action.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('bookings-table-body');
            
            if (data.success && data.bookings.length > 0) {
                tbody.innerHTML = data.bookings.map(booking => `
                    <tr class="border-t hover:bg-gray-50">
                        <td class="p-4 font-mono text-gray-500">${booking.ref_id}</td>
                        <td class="p-4 font-bold">${booking.guestName}</td>
                        <td class="p-4">${booking.roomName}</td>
                        <td class="p-4 text-gray-600">${booking.checkIn}</td>
                        <td class="p-4 text-gray-600">${booking.checkOut}</td>
                        <td class="p-4 font-bold text-brand-purple">$${booking.total}</td>
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
                tbody.innerHTML = '<tr><td colspan="8" class="p-4 text-center text-gray-400">No bookings found.</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('bookings-table-body').innerHTML = 
                '<tr><td colspan="8" class="p-4 text-center text-red-400">Error loading bookings. Make sure the database is set up.</td></tr>';
        });
}

function updateBookingStatus(bookingId, status) {
    if (!confirm(`Are you sure you want to ${status.toLowerCase()} this booking?`)) {
        return;
    }
    
    fetch('actions/bookings_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: bookingId, status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            loadBookings();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating booking status');
    });
}

function deleteBooking(bookingId) {
    if (!confirm('Are you sure you want to delete this booking?')) {
        return;
    }
    
    fetch('actions/bookings_action.php?id=' + bookingId, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            loadBookings();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting booking');
    });
}

// Load bookings when page loads
document.addEventListener('DOMContentLoaded', loadBookings);
</script>
