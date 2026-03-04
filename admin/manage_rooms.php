<!-- ================= Manage Rooms Page ================= -->
<div id="manage-rooms" class="page-section">
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-bold">Room Management</h3>
            <div class="flex gap-2">
                <button onclick="loadRooms()" class="bg-brand-blue text-white px-4 py-2 rounded shadow hover:bg-sky-600">
                    ↻ Refresh
                </button>
                <button onclick="showAddRoomModal()" class="bg-brand-purple text-white px-4 py-2 rounded shadow hover:bg-purple-700">
                    <i class="fas fa-plus mr-2"></i> Add Room
                </button>
            </div>
        </div>
        
        <div id="rooms-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="col-span-full text-center text-gray-400 py-8">Loading rooms...</div>
        </div>
    </div>
</div>

<!-- Add/Edit Room Modal -->
<div id="room-modal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 id="room-modal-title" class="text-xl font-bold">Add New Room</h3>
                <button onclick="closeRoomModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="room-form" onsubmit="saveRoom(event)">
                <input type="hidden" id="room-id">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Room Name</label>
                        <input type="text" id="room-name" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-purple focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Room Type</label>
                        <select id="room-type" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-purple focus:border-transparent">
                            <option value="">Select Type</option>
                            <option value="Dorm">Dorm</option>
                            <option value="Standard">Standard</option>
                            <option value="Deluxe">Deluxe</option>
                            <option value="Family">Family</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price per Night ($)</label>
                        <input type="number" id="room-price" required min="0" step="0.01" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-purple focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Capacity</label>
                        <input type="number" id="room-capacity" required min="1" value="2" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-purple focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
                        <input type="url" id="room-image" placeholder="https://..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-purple focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="room-description" rows="3" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-purple focus:border-transparent"></textarea>
                    </div>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeRoomModal()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-brand-purple text-white rounded-lg hover:bg-purple-700">
                        Save Room
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function loadRooms() {
    fetch('../actions/rooms_action.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('rooms-grid');
            
            if (data.success && data.rooms.length > 0) {
                container.innerHTML = data.rooms.map(room => `
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-50 p-4 flex gap-4 transition hover:shadow-md">
                        <img src="${room.image}" class="w-24 h-24 object-cover rounded-xl bg-gray-200" alt="${room.name}">
                        <div class="flex-1 flex flex-col justify-center">
                            <h4 class="font-bold text-gray-800">${room.name}</h4>
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">${room.type}</p>
                            <p class="text-brand-purple font-bold text-lg">$${room.price} <span class="text-xs text-gray-400 font-normal">/night</span></p>
                            <div class="mt-3 flex gap-3 text-xs">
                                <button onclick="editRoom(${room.id})" class="text-blue-500 hover:underline">Edit</button>
                                <button onclick="deleteRoom(${room.id})" class="text-red-400 hover:underline">Remove</button>
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<div class="col-span-full text-center text-gray-400 py-8">No rooms found. Add your first room!</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('rooms-grid').innerHTML = '<div class="col-span-full text-center text-red-400 py-8">Error loading rooms.</div>';
        });
}

function showAddRoomModal() {
    document.getElementById('room-modal-title').innerText = 'Add New Room';
    document.getElementById('room-form').reset();
    document.getElementById('room-id').value = '';
    document.getElementById('room-modal').classList.remove('hidden');
}

function editRoom(roomId) {
    fetch('../actions/rooms_action.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const room = data.rooms.find(r => r.id === roomId);
                if (room) {
                    document.getElementById('room-modal-title').innerText = 'Edit Room';
                    document.getElementById('room-id').value = room.id;
                    document.getElementById('room-name').value = room.name;
                    document.getElementById('room-type').value = room.type;
                    document.getElementById('room-price').value = room.price;
                    document.getElementById('room-capacity').value = room.capacity;
                    document.getElementById('room-image').value = room.image;
                    document.getElementById('room-description').value = room.description || '';
                    document.getElementById('room-modal').classList.remove('hidden');
                }
            }
        });
}

function closeRoomModal() {
    document.getElementById('room-modal').classList.add('hidden');
}

function saveRoom(event) {
    event.preventDefault();
    
    const roomId = document.getElementById('room-id').value;
    const roomData = {
        name: document.getElementById('room-name').value,
        type: document.getElementById('room-type').value,
        price: parseFloat(document.getElementById('room-price').value),
        capacity: parseInt(document.getElementById('room-capacity').value),
        image: document.getElementById('room-image').value || 'https://images.unsplash.com/photo-1555854877-bab0e564b8d5?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
        description: document.getElementById('room-description').value
    };
    
    const method = roomId ? 'PUT' : 'POST';
    const url = roomId ? '../actions/rooms_action.php' : '../actions/rooms_action.php';
    
    if (roomId) {
        roomData.id = parseInt(roomId);
    }
    
    console.log("Sending data:", roomData);
    
    fetch(url, {
        method: method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(roomData)
    })
    .then(response => {
        console.log("Response status:", response.status);
        return response.text();
    })
    .then(text => {
        console.log("Raw response:", text);
        try {
            const data = JSON.parse(text);
            if (data.success) {
                alert(data.message);
                closeRoomModal();
                loadRooms();
            } else {
                alert('Error: ' + data.message);
            }
        } catch(e) {
            alert('Parse error: ' + text);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving room: ' + error.message);
    });
}

function deleteRoom(roomId) {
    if (!confirm('Are you sure you want to delete this room?')) {
        return;
    }
    
    fetch('../actions/rooms_action.php?id=' + roomId, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            loadRooms();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting room');
    });
}

// Load rooms when page loads
document.addEventListener('DOMContentLoaded', loadRooms);
</script>
