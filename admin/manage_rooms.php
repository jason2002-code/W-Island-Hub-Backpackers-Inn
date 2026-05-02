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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price per Night (₱)</label>
                        <input type="number" id="room-price" required min="0" step="0.01" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-purple focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Capacity</label>
                        <input type="number" id="room-capacity" required min="1" value="2" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-purple focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Amenities (comma separated)</label>
                        <input type="text" id="room-amenities" placeholder="WiFi, A/C, Breakfast, Pool Access" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-purple focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
                        <input type="text" id="room-image" placeholder="assets/image/your-image.jpg" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-purple focus:border-transparent">
                        <input type="file" id="room-image-file" accept="image/*" class="hidden" onchange="previewRoomImage(event)">
                        <p class="text-xs text-gray-500 mt-1">Select an image below to upload it automatically on save.</p>
                        <div id="room-image-preview-container" class="mt-2 text-center p-4 border-2 border-dashed border-gray-200 rounded-lg bg-gray-50">
                            <img id="room-preview-img" src="" alt="Click to select image" class="w-full h-32 object-cover rounded-lg cursor-pointer hover:opacity-90 transition hidden" onclick="document.getElementById('room-image-file').click();">
                            <div id="room-preview-placeholder" class="cursor-pointer text-gray-400 hover:text-brand-purple transition" onclick="document.getElementById('room-image-file').click();">
                                <i class="fas fa-image text-3xl mb-2"></i>
                                <p class="text-sm">Click to browse</p>
                            </div>
                        </div>
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
// Loads all rooms from the backend API (actions/rooms_action.php) and dynamically renders them as cards in the grid
function loadRooms() {
    fetch('../actions/rooms_action.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('rooms-grid');
            
            if (data.success && data.rooms.length > 0) {
                container.innerHTML = data.rooms.map(room => `
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-50 p-4 flex gap-4 transition hover:shadow-md">
                        <img src="${room.image.startsWith('http') ? room.image : '../' + room.image}" class="w-24 h-24 object-cover rounded-xl bg-gray-200" alt="${room.name}">
                        <div class="flex-1 flex flex-col justify-center">
                            <h4 class="font-bold text-gray-800">${room.name}</h4>
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">${room.type}</p>
                            <p class="text-brand-purple font-bold text-lg">₱${room.price} <span class="text-xs text-gray-400 font-normal">/night</span></p>
                            <div class="mt-3 flex gap-3 text-xs">
                                <button onclick="editRoom('${room.id}')" class="text-blue-500 hover:underline">Edit</button>
                                <button onclick="deleteRoom('${room.id}')" class="text-red-400 hover:underline">Remove</button>
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

// Handle image preview when a file is selected
function previewRoomImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('room-preview-img').src = e.target.result;
            document.getElementById('room-preview-img').classList.remove('hidden');
            document.getElementById('room-preview-placeholder').classList.add('hidden');
            document.getElementById('room-image').value = 'assets/image/' + file.name;
        };
        reader.readAsDataURL(file);
    }
}

// Opens the room management modal in 'Add New Room' mode and resets the form fields
function showAddRoomModal() {
    document.getElementById('room-modal-title').innerText = 'Add New Room';
    document.getElementById('room-form').reset();
    document.getElementById('room-id').value = '';
    document.getElementById('room-preview-img').src = '';
    document.getElementById('room-preview-img').classList.add('hidden');
    document.getElementById('room-preview-placeholder').classList.remove('hidden');
    document.getElementById('room-image-file').value = '';
    document.getElementById('room-modal').classList.remove('hidden');
}

// Fetches details of specific room by ID and populates the modal form for editing
function editRoom(roomId) {
    fetch('../actions/rooms_action.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const room = data.rooms.find(r => r.id == roomId);
                if (room) {
                    document.getElementById('room-modal-title').innerText = 'Edit Room';
                    document.getElementById('room-id').value = room.id;
                    document.getElementById('room-name').value = room.name;
                    document.getElementById('room-type').value = room.type;
                    document.getElementById('room-price').value = room.price;
                    document.getElementById('room-capacity').value = room.capacity;
                    document.getElementById('room-image').value = room.image;
                    document.getElementById('room-description').value = room.description || '';
                    
                    document.getElementById('room-image-file').value = '';
                    if (room.image) {
                        const imgUrl = room.image.startsWith('http') ? room.image : '../' + room.image;
                        document.getElementById('room-preview-img').src = imgUrl;
                        document.getElementById('room-preview-img').classList.remove('hidden');
                        document.getElementById('room-preview-placeholder').classList.add('hidden');
                    } else {
                        document.getElementById('room-preview-img').classList.add('hidden');
                        document.getElementById('room-preview-placeholder').classList.remove('hidden');
                    }

                    // Handle amenities parsing (JSON string to comma-separated)
                    let amenitiesVal = '';
                    try {
                        // If it's a string looking like JSON array
                        if (room.amenities && typeof room.amenities === 'string' && room.amenities.trim().startsWith('[')) {
                            const parsed = JSON.parse(room.amenities);
                            if (Array.isArray(parsed)) amenitiesVal = parsed.join(', ');
                        } else if (room.amenities) {
                            // Fallback for plain text
                            amenitiesVal = room.amenities;
                        }
                    } catch (e) {
                        amenitiesVal = room.amenities || '';
                    }
                    document.getElementById('room-amenities').value = amenitiesVal;
                    
                    document.getElementById('room-modal').classList.remove('hidden');
                }
            }
        });
}

// Hides and closes the room add/edit modal, optionally resetting form
function closeRoomModal() {
    document.getElementById('room-modal').classList.add('hidden');
}

// Handles room form submission: validates, formats amenities as JSON, sends POST/PUT to API, refreshes list on success
function saveRoom(event) {
    event.preventDefault();
    
    const roomId = document.getElementById('room-id').value;
    
    // Process amenities from comma-separated string to JSON string
    const amenitiesRaw = document.getElementById('room-amenities').value;
    const amenitiesArray = amenitiesRaw.split(',').map(item => item.trim()).filter(item => item !== '');
    const amenitiesJson = JSON.stringify(amenitiesArray);

    const formData = new FormData();
    formData.append('name', document.getElementById('room-name').value);
    formData.append('type', document.getElementById('room-type').value);
    formData.append('price', parseFloat(document.getElementById('room-price').value));
    formData.append('capacity', parseInt(document.getElementById('room-capacity').value));
    formData.append('image', document.getElementById('room-image').value || 'assets/image/background.jpg');
    formData.append('description', document.getElementById('room-description').value);
    formData.append('amenities', amenitiesJson);

    const fileInput = document.getElementById('room-image-file');
    if (fileInput.files.length > 0) {
        formData.append('image_file', fileInput.files[0]);
    }

    let url = '../actions/rooms_action.php';
    let method = 'POST'; // We always use POST for FormData

    if (roomId) {
        formData.append('id', parseInt(roomId));
        formData.append('_method', 'PUT'); // Tell the backend it's an update
        url = `../actions/rooms_action.php?id=${roomId}`; // Keep query param just in case
    }
    
    console.log("Sending data");
    
    fetch(url, {
        method: method,
        body: formData
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
                showToast(data.message, 'success');
                closeRoomModal();
                loadRooms();
            } else {
                showToast('Error: ' + data.message, 'error');
            }
        } catch(e) {
            showToast('Parse error: ' + text, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error saving room: ' + error.message, 'error');
    });
}

// Prompts confirmation then sends DELETE request to API to remove room, refreshes grid on success
function deleteRoom(roomId) {
    showConfirm('Are you sure you want to delete this room?', () => {
        fetch('../actions/rooms_action.php?id=' + roomId, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                loadRooms();
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error deleting room', 'error');
        });
    });
}

// Load rooms when page loads
document.addEventListener('DOMContentLoaded', loadRooms);
</script>
