<!-- ================= Manage Room Prices Page ================= -->
<div id="manage-prices" class="page-section">
    <div class="p-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <p class="text-sm text-gray-500">Manage and update nightly rates across all room types</p>
            </div>
            <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                <div class="relative flex-1 sm:flex-none">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="price-search-input" placeholder="Search rooms..." 
                        class="pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-purple/20 w-full sm:w-48"
                        oninput="filterPriceTable()">
                </div>
                <select id="price-type-filter" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-purple/20" onchange="filterPriceTable()">
                    <option value="">All Types</option>
                    <option value="Dorm">Dorm</option>
                    <option value="Standard">Standard</option>
                    <option value="Deluxe">Deluxe</option>
                    <option value="Family">Family</option>
                </select>
                <button onclick="loadPriceRooms()" class="bg-brand-blue text-white px-4 py-2 rounded-lg shadow hover:bg-sky-600 transition flex items-center gap-2">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>

        <!-- Price Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Rooms</div>
                <div class="text-2xl font-bold text-brand-dark mt-1" id="price-stat-total">0</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Average Rate</div>
                <div class="text-2xl font-bold text-brand-purple mt-1" id="price-stat-avg">₱0.00</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Lowest Rate</div>
                <div class="text-2xl font-bold text-emerald-600 mt-1" id="price-stat-min">₱0.00</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Highest Rate</div>
                <div class="text-2xl font-bold text-blue-600 mt-1" id="price-stat-max">₱0.00</div>
            </div>
        </div>

        <!-- Room Prices Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[850px]">
                    <thead>
                        <tr class="bg-gray-50/80 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                            <th class="p-4">Room</th>
                            <th class="p-4">Type</th>
                            <th class="p-4">Capacity</th>
                            <th class="p-4">Current Price / Night</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="price-table-body" class="text-sm divide-y divide-gray-100">
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-400">Loading room pricing data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Dedicated Change Price Modal (Global for Admin Panel) -->
<div id="dedicated-price-modal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full overflow-hidden">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-tags text-emerald-600"></i> Change Room Price
                </h3>
                <button onclick="closeDedicatedPriceModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="dedicated-price-form" onsubmit="saveDedicatedRoomPrice(event)">
                <input type="hidden" id="dedicated-price-room-id">
                
                <div class="space-y-4">
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 flex items-center gap-4">
                        <img id="dedicated-price-room-img" src="" alt="Room" class="w-16 h-16 rounded-lg object-cover bg-gray-200">
                        <div class="flex-1">
                            <h4 id="dedicated-price-room-name" class="font-bold text-gray-800 text-base"></h4>
                            <div class="flex items-center gap-2 mt-1">
                                <span id="dedicated-price-room-type" class="text-xs bg-purple-100 text-brand-purple px-2 py-0.5 rounded font-medium"></span>
                                <span class="text-xs text-gray-500">Current: <strong id="dedicated-price-room-current" class="text-gray-800"></strong></span>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">New Price per Night (₱)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-500 font-bold text-base">₱</span>
                            <input type="number" id="dedicated-new-room-price" required min="0" step="0.01" placeholder="0.00" class="w-full pl-9 pr-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent font-semibold text-lg text-gray-800">
                        </div>
                        <p class="text-xs text-gray-400 mt-1">This will update the standard booking price per night.</p>
                    </div>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeDedicatedPriceModal()" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium text-gray-700">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-medium shadow">
                        Update Price
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let allPriceRooms = [];

// Loads all room data for price management
function loadPriceRooms() {
    fetch('../actions/rooms_action.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.rooms) {
                allPriceRooms = data.rooms;
                renderPriceTable(allPriceRooms);
                updatePriceStats(allPriceRooms);
            } else {
                document.getElementById('price-table-body').innerHTML = 
                    '<tr><td colspan="6" class="p-8 text-center text-gray-400">No rooms found.</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error loading price rooms:', error);
            document.getElementById('price-table-body').innerHTML = 
                '<tr><td colspan="6" class="p-8 text-center text-red-400">Error loading rooms for price management.</td></tr>';
        });
}

// Renders the room price rows in the table
function renderPriceTable(rooms) {
    const tbody = document.getElementById('price-table-body');
    if (!rooms || rooms.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="p-8 text-center text-gray-400">No matching rooms found.</td></tr>';
        return;
    }

    tbody.innerHTML = rooms.map(room => {
        const imgSrc = room.image.startsWith('http') ? room.image : '../' + room.image;
        const priceFormatted = parseFloat(room.price).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        return `
            <tr class="hover:bg-gray-50/80 transition">
                <td class="p-4">
                    <div class="flex items-center gap-3">
                        <img src="${imgSrc}" alt="${room.name}" class="w-12 h-12 rounded-lg object-cover bg-gray-200 border border-gray-100 flex-shrink-0">
                        <div>
                            <p class="font-bold text-gray-800">${room.name}</p>
                            <p class="text-xs text-gray-400 truncate max-w-xs">${room.description || 'No description'}</p>
                        </div>
                    </div>
                </td>
                <td class="p-4">
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-50 text-brand-purple">
                        ${room.type}
                    </span>
                </td>
                <td class="p-4 text-gray-600 font-medium">
                    <i class="fas fa-user-friends text-gray-400 mr-1.5 text-xs"></i>${room.capacity} Guests
                </td>
                <td class="p-4">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-bold text-base border border-emerald-100">
                        ₱${priceFormatted} <span class="text-xs font-normal text-emerald-600">/night</span>
                    </div>
                </td>
                <td class="p-4">
                    ${room.status === 'Maintenance' 
                        ? '<span class="px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">Maintenance</span>' 
                        : '<span class="px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Available</span>'}
                </td>
                <td class="p-4 text-right">
                    <button onclick="openDedicatedPriceModal('${room.id}')" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium rounded-lg shadow-sm hover:shadow transition flex items-center gap-1.5 ml-auto">
                        <i class="fas fa-tags"></i> Change Price
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

// Updates statistics cards on top of price management
function updatePriceStats(rooms) {
    if (!rooms || rooms.length === 0) {
        document.getElementById('price-stat-total').innerText = '0';
        document.getElementById('price-stat-avg').innerText = '₱0.00';
        document.getElementById('price-stat-min').innerText = '₱0.00';
        document.getElementById('price-stat-max').innerText = '₱0.00';
        return;
    }

    const prices = rooms.map(r => parseFloat(r.price) || 0);
    const total = rooms.length;
    const sum = prices.reduce((acc, p) => acc + p, 0);
    const avg = sum / total;
    const min = Math.min(...prices);
    const max = Math.max(...prices);

    const fmt = num => '₱' + num.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    document.getElementById('price-stat-total').innerText = total;
    document.getElementById('price-stat-avg').innerText = fmt(avg);
    document.getElementById('price-stat-min').innerText = fmt(min);
    document.getElementById('price-stat-max').innerText = fmt(max);
}

// Filter rooms in price management by search query and type
function filterPriceTable() {
    const searchVal = document.getElementById('price-search-input').value.toLowerCase().trim();
    const typeVal = document.getElementById('price-type-filter').value;

    const filtered = allPriceRooms.filter(room => {
        const matchesSearch = !searchVal || 
            room.name.toLowerCase().includes(searchVal) || 
            room.type.toLowerCase().includes(searchVal) ||
            (room.description && room.description.toLowerCase().includes(searchVal));
        const matchesType = !typeVal || room.type === typeVal;
        return matchesSearch && matchesType;
    });

    renderPriceTable(filtered);
}

// Opens the Change Price Modal for a specific room
function openDedicatedPriceModal(roomId) {
    const room = allPriceRooms.find(r => r.id == roomId);
    if (!room) {
        fetch('../actions/rooms_action.php')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    allPriceRooms = data.rooms;
                    const r = allPriceRooms.find(item => item.id == roomId);
                    if (r) fillAndShowPriceModal(r);
                }
            });
    } else {
        fillAndShowPriceModal(room);
    }
}

function fillAndShowPriceModal(room) {
    document.getElementById('dedicated-price-room-id').value = room.id;
    document.getElementById('dedicated-price-room-name').innerText = room.name;
    document.getElementById('dedicated-price-room-type').innerText = room.type;
    document.getElementById('dedicated-price-room-current').innerText = '₱' + parseFloat(room.price).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' /night';
    document.getElementById('dedicated-new-room-price').value = room.price;
    
    const imgSrc = room.image.startsWith('http') ? room.image : '../' + room.image;
    document.getElementById('dedicated-price-room-img').src = imgSrc;

    document.getElementById('dedicated-price-modal').classList.remove('hidden');
    document.getElementById('dedicated-new-room-price').focus();
}

function closeDedicatedPriceModal() {
    document.getElementById('dedicated-price-modal').classList.add('hidden');
    document.getElementById('dedicated-price-form').reset();
}

// Submits the price update
function saveDedicatedRoomPrice(event) {
    event.preventDefault();
    const roomId = document.getElementById('dedicated-price-room-id').value;
    const newPrice = parseFloat(document.getElementById('dedicated-new-room-price').value);

    if (!roomId || isNaN(newPrice) || newPrice < 0) {
        showToast('Please enter a valid price', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('id', parseInt(roomId));
    formData.append('price', newPrice);
    formData.append('_method', 'PUT');

    fetch(`../actions/rooms_action.php?id=${roomId}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Room price updated successfully!', 'success');
            closeDedicatedPriceModal();
            loadPriceRooms();
            if (typeof loadRooms === 'function') loadRooms();
        } else {
            showToast('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error updating price:', error);
        showToast('Error updating price: ' + error.message, 'error');
    });
}

// Load price data when DOM is ready
document.addEventListener('DOMContentLoaded', loadPriceRooms);
</script>

