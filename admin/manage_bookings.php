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
                        ${booking.status !== 'Cancelled' ? `
                            <button onclick="updateBookingStatus('${booking.id}', 'Cancelled')" class="text-red-600 hover:bg-red-50 p-1 rounded transition" title="Cancel">
                                <i class="fas fa-times"></i>
                            </button>
                        ` : ''}
                        <button onclick="printAdminInvoice('${booking.id}')" class="text-blue-600 hover:bg-blue-50 p-1 rounded transition" title="Print Invoice">
                            <i class="fas fa-file-invoice"></i>
                        </button>
                        <button onclick="deleteBooking('${booking.id}')" class="text-gray-400 hover:text-red-600 p-1 rounded transition" title="Delete">
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

function printAdminInvoice(bookingId) {
    const booking = allBookings.find(b => b.id == bookingId);
    if (!booking) return;

    const dateNow = new Date().toLocaleDateString();
    const guestName = booking.guestName || 'Guest';
    const guestEmail = booking.guestEmail || 'N/A';
    const paymentMethod = booking.payment_method || 'Full Payment';
    // Default back to calculated price if room_price isn't available for some reason
    const pricePerNight = booking.room_price || ((booking.total - 15) / Math.max(1, booking.nights));
    const total = parseFloat(booking.total);

    const invoiceHTML = `
        <html>
            <head>
                <title>Invoice - ${booking.ref_id}</title>
                <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
                <style>
                    body { font-family: sans-serif; padding: 20px; }
                    @media print {
                        .no-print { display: none; }
                        body { padding: 0; }
                    }
                </style>
            </head>
            <body>
                <div class="max-w-2xl mx-auto bg-white p-8">
                    <div class="flex justify-between items-start mb-8 border-b pb-6">
                        <div>
                            <h2 class="text-3xl font-serif font-bold text-gray-800 mb-2">W Island Hub</h2>
                            <p class="text-sm text-gray-500">Backpackers Inn</p>
                            <p class="text-sm text-gray-500">Paradise Island, PH</p>
                        </div>
                        <div class="text-right flex items-center gap-4">
                            <div class="text-right">
                                <div class="text-xl font-bold text-blue-600 mb-1">INVOICE</div>
                                <div class="text-sm font-semibold text-gray-600">Ref: ${booking.ref_id}</div>
                                <div class="text-sm text-gray-500">Date: ${dateNow}</div>
                            </div>
                            <div class="bg-white p-1 border border-gray-100 rounded-lg shadow-sm">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=${encodeURIComponent(booking.ref_id)}" alt="QR Code" class="w-20 h-20">
                            </div>
                        </div>
                    </div>
    
                    <div class="grid grid-cols-2 gap-8 mb-8">
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Billed To:</h4>
                            <div class="font-bold text-gray-800">${guestName}</div>
                            <div class="text-sm text-gray-600">${guestEmail}</div>
                        </div>
                        <div class="text-right">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Payment Method:</h4>
                            <div class="font-bold text-gray-800">${paymentMethod}</div>
                            <div class="text-sm text-gray-600">Status: ${booking.status}</div>
                        </div>
                    </div>
    
                    <div class="mb-8">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Reservation Details:</h4>
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500 block mb-1">Room</span>
                                <strong class="text-gray-800 text-base">${booking.roomName}</strong>
                            </div>
                            <div>
                                <span class="text-gray-500 block mb-1">Nights</span>
                                <strong class="text-gray-800">${booking.nights}</strong>
                            </div>
                            <div>
                                <span class="text-gray-500 block mb-1">Check-in</span>
                                <strong class="text-gray-800">${formatDate(booking.checkIn)}</strong>
                            </div>
                            <div>
                                <span class="text-gray-500 block mb-1">Check-out</span>
                                <strong class="text-gray-800">${formatDate(booking.checkOut)}</strong>
                            </div>
                        </div>
                    </div>
    
                    <table class="w-full text-left border-collapse mb-8">
                        <thead>
                            <tr class="border-b-2 border-gray-200">
                                <th class="py-3 font-bold text-gray-800">Description</th>
                                <th class="py-3 font-bold text-gray-800 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-gray-100">
                                <td class="py-4">
                                    <div class="font-bold text-gray-800">Room Rate</div>
                                    <div class="text-xs text-gray-500">₱${parseFloat(pricePerNight).toFixed(2)} x ${booking.nights} nights</div>
                                </td>
                                <td class="py-4 text-right font-medium">₱${(parseFloat(pricePerNight) * parseInt(booking.nights)).toFixed(2)}</td>
                            </tr>
                            <tr class="border-b border-gray-100">
                                <td class="py-4">
                                    <div class="font-bold text-gray-800">Taxes & Fees</div>
                                </td>
                                <td class="py-4 text-right font-medium">₱15.00</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="py-4 font-bold text-xl text-gray-800 text-right pr-4">Total:</td>
                                <td class="py-4 font-bold text-2xl text-blue-600 text-right">₱${total.toFixed(2)}</td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <div class="text-center text-sm text-gray-500 mt-8 pt-8 border-t border-gray-100">
                        <p>Thank you for choosing W Island Hub Backpackers Inn!</p>
                        <p>We look forward to welcoming you.</p>
                    </div>

                    <div class="mt-8 text-center no-print">
                        <button onclick="window.print();" class="px-6 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700">
                            Print Invoice
                        </button>
                    </div>
                </div>
            </body>
        </html>
    `;

    const printWindow = window.open('', '_blank');
    printWindow.document.write(invoiceHTML);
    printWindow.document.close();
}

// Load bookings when page loads
document.addEventListener('DOMContentLoaded', loadBookings);
</script>
