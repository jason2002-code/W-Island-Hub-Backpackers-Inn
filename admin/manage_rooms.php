<!-- ================= Manage Rooms Page ================= -->
<div id="manage-rooms" class="page-section">
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
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
                    
                    <div id="room-price-container">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price per Night (₱)</label>
                        <input type="number" id="room-price" required min="0" step="0.01" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-purple focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Capacity</label>
                        <input type="number" id="room-capacity" required min="1" value="2" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-purple focus:border-transparent">
                    </div>
                    
                     <div class="col-span-1 sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Amenities</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3" id="room-amenities-container">
                            <label class="inline-flex items-center text-sm text-gray-700 cursor-pointer"><input type="checkbox" value="Free WiFi" class="form-checkbox h-4 w-4 text-brand-purple rounded border-gray-300 mr-2 amenity-cb">Free WiFi</label>
                            <label class="inline-flex items-center text-sm text-gray-700 cursor-pointer"><input type="checkbox" value="Air Conditioning" class="form-checkbox h-4 w-4 text-brand-purple rounded border-gray-300 mr-2 amenity-cb">Air Conditioning</label>
                            <label class="inline-flex items-center text-sm text-gray-700 cursor-pointer"><input type="checkbox" value="Ceiling Fan" class="form-checkbox h-4 w-4 text-brand-purple rounded border-gray-300 mr-2 amenity-cb">Ceiling Fan</label>
                            <label class="inline-flex items-center text-sm text-gray-700 cursor-pointer"><input type="checkbox" value="Breakfast Included" class="form-checkbox h-4 w-4 text-brand-purple rounded border-gray-300 mr-2 amenity-cb">Breakfast</label>
                            <label class="inline-flex items-center text-sm text-gray-700 cursor-pointer"><input type="checkbox" value="Hot Shower" class="form-checkbox h-4 w-4 text-brand-purple rounded border-gray-300 mr-2 amenity-cb">Hot Shower</label>
                            <label class="inline-flex items-center text-sm text-gray-700 cursor-pointer"><input type="checkbox" value="Fresh Towels" class="form-checkbox h-4 w-4 text-brand-purple rounded border-gray-300 mr-2 amenity-cb">Fresh Towels</label>
                            <label class="inline-flex items-center text-sm text-gray-700 cursor-pointer"><input type="checkbox" value="Shared Kitchen" class="form-checkbox h-4 w-4 text-brand-purple rounded border-gray-300 mr-2 amenity-cb">Shared Kitchen</label>
                            <label class="inline-flex items-center text-sm text-gray-700 cursor-pointer"><input type="checkbox" value="Lockers" class="form-checkbox h-4 w-4 text-brand-purple rounded border-gray-300 mr-2 amenity-cb">Lockers</label>
                            <label class="inline-flex items-center text-sm text-gray-700 cursor-pointer"><input type="checkbox" value="Reading Light" class="form-checkbox h-4 w-4 text-brand-purple rounded border-gray-300 mr-2 amenity-cb">Reading Light</label>
                            <label class="inline-flex items-center text-sm text-gray-700 cursor-pointer"><input type="checkbox" value="Bidet" class="form-checkbox h-4 w-4 text-brand-purple rounded border-gray-300 mr-2 amenity-cb">Bidet</label>
                            <label class="inline-flex items-center text-sm text-gray-700 cursor-pointer"><input type="checkbox" value="Work Desk" class="form-checkbox h-4 w-4 text-brand-purple rounded border-gray-300 mr-2 amenity-cb">Work Desk</label>
                            <label class="inline-flex items-center text-sm text-gray-700 cursor-pointer"><input type="checkbox" value="Toiletries" class="form-checkbox h-4 w-4 text-brand-purple rounded border-gray-300 mr-2 amenity-cb">Toiletries</label>
                        </div>
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

<!-- Room Features Modal -->
<div id="room-features-modal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4" onclick="closeFeaturesModal()">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto overflow-hidden" onclick="event.stopPropagation()">
        <img id="features-image" src="" alt="Room Image" class="w-full h-56 object-cover bg-gray-200 hidden">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 id="features-modal-title" class="text-xl font-bold text-brand-dark">Room Features</h3>
                <button onclick="closeFeaturesModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="space-y-4">
                <div>
                    <h4 class="font-bold text-gray-700 text-sm mb-1">Capacity</h4>
                    <p id="features-capacity" class="text-gray-600"></p>
                </div>
                <div>
                    <h4 class="font-bold text-gray-700 text-sm mb-1">Amenities</h4>
                    <p id="features-amenities" class="text-gray-600"></p>
                </div>
                <div>
                    <h4 class="font-bold text-gray-700 text-sm mb-1">Description</h4>
                    <p id="features-description" class="text-gray-600"></p>
                </div>
            </div>
            <div class="mt-6 flex justify-end">
                <button onclick="closeFeaturesModal()" class="px-4 py-2 bg-brand-blue text-white rounded-lg hover:bg-sky-600">Close</button>
            </div>
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
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-50 overflow-hidden flex flex-col transition hover:shadow-md cursor-pointer" onclick="viewRoomFeatures('${room.id}')">
                        <img src="${room.image.startsWith('http') ? room.image : '../' + room.image}" class="w-full h-48 object-cover bg-gray-200" alt="${room.name}">
                        <div class="p-4 flex-1 flex flex-col justify-center">
                            <h4 class="font-bold text-gray-800">${room.name} ${room.status === 'Maintenance' ? '<span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded ml-2">Maintenance</span>' : (room.status === 'Occupied' || room.is_occupied ? '<span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded ml-2">Occupied</span>' : '<span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded ml-2">Available</span>')}</h4>
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">${room.type}</p>
                            <p class="text-brand-purple font-bold text-lg">₱${parseFloat(room.price).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} <span class="text-xs text-gray-400 font-normal">/night</span></p>
                            <div class="mt-3 flex flex-wrap items-center gap-3 text-xs">
                                <button onclick="event.stopPropagation(); editRoom('${room.id}')" class="text-blue-600 hover:text-blue-800 hover:underline font-medium flex items-center gap-1">
                                    <i class="fas fa-edit"></i> Edit Details
                                </button>
                                <button onclick="event.stopPropagation(); toggleMaintenance('${room.id}', '${room.status}')" class="text-orange-500 hover:text-orange-700 hover:underline flex items-center gap-1">
                                    <i class="fas fa-wrench"></i> ${room.status === 'Maintenance' ? 'Enable' : 'Maintenance'}
                                </button>
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

    // Show price input for new room creation
    const priceContainer = document.getElementById('room-price-container');
    if (priceContainer) priceContainer.classList.remove('hidden');
    const priceInput = document.getElementById('room-price');
    if (priceInput) priceInput.required = true;

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
                    document.getElementById('room-modal-title').innerText = 'Edit Room Details';
                    document.getElementById('room-id').value = room.id;
                    document.getElementById('room-name').value = room.name;
                    document.getElementById('room-type').value = room.type;
                    document.getElementById('room-capacity').value = room.capacity;
                    document.getElementById('room-image').value = room.image;
                    document.getElementById('room-description').value = room.description || '';
                    
                    // Hide price field in general edit modal (prices are managed via dedicated "Change Price" button)
                    const priceContainer = document.getElementById('room-price-container');
                    if (priceContainer) priceContainer.classList.add('hidden');
                    const priceInput = document.getElementById('room-price');
                    if (priceInput) priceInput.required = false;

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

                      // Handle amenities parsing
                    let amenitiesList = [];
                    try {
                        if (room.amenities && typeof room.amenities === 'string' && room.amenities.trim().startsWith('[')) {
                            const parsed = JSON.parse(room.amenities);
                            if (Array.isArray(parsed)) amenitiesList = parsed;
                        } else if (room.amenities) {
                            amenitiesList = room.amenities.split(',').map(a => a.trim());
                        }
                    } catch (e) {
                        if (room.amenities) {
                            amenitiesList = room.amenities.split(',').map(a => a.trim());
                        }
                    }
                    
                    document.querySelectorAll('.amenity-cb').forEach(cb => {
                        cb.checked = amenitiesList.includes(cb.value);
                    });
                    
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
    
      // Process amenities from checkboxes to JSON string
    const checkedAmenities = Array.from(document.querySelectorAll('.amenity-cb:checked')).map(cb => cb.value);
    const amenitiesJson = JSON.stringify(checkedAmenities);

    const formData = new FormData();
    formData.append('name', document.getElementById('room-name').value);
    formData.append('type', document.getElementById('room-type').value);
    formData.append('capacity', parseInt(document.getElementById('room-capacity').value));
    formData.append('image', document.getElementById('room-image').value || 'assets/image/background.jpg');
    formData.append('description', document.getElementById('room-description').value);
    formData.append('amenities', amenitiesJson);

    // Only include price if creating a new room
    if (!roomId) {
        const priceVal = parseFloat(document.getElementById('room-price').value);
        if (isNaN(priceVal) || priceVal < 0) {
            showToast('Please enter a valid price for the room', 'error');
            return;
        }
        formData.append('price', priceVal);
    }

    const fileInput = document.getElementById('room-image-file');
    if (fileInput.files.length > 0) {
        formData.append('image_file', fileInput.files[0]);
    }

    let url = '../actions/rooms_action.php';
    let method = 'POST'; // We always use POST for FormData

    if (roomId) {
        formData.append('id', parseInt(roomId));
        formData.append('_method', 'PUT'); // Tell the backend it's an update
        url = `../actions/rooms_action.php?id=${roomId}`;
    }
    
    fetch(url, {
        method: method,
        body: formData
    })
    .then(response => response.text())
    .then(text => {
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

// Toggles the maintenance status of a room
function toggleMaintenance(roomId, currentStatus) {
    const newStatus = currentStatus === 'Maintenance' ? 'Available' : 'Maintenance';
    const actionText = currentStatus === 'Maintenance' ? 'enable this room' : 'put this room under maintenance';
    showConfirm(`Are you sure you want to ${actionText}?`, () => {
        const formData = new FormData();
        formData.append('id', roomId);
        formData.append('status', newStatus);
        formData.append('_method', 'PUT');

        fetch(`../actions/rooms_action.php?id=${roomId}`, {
            method: 'POST',
            body: formData
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
            showToast('Error updating room status', 'error');
        });
    });
}

// View Room Features
function viewRoomFeatures(roomId) {
    fetch('../actions/rooms_action.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const room = data.rooms.find(r => r.id == roomId);
                if (room) {
                    let amenitiesVal = '';
                    try {
                        if (room.amenities && typeof room.amenities === 'string' && room.amenities.trim().startsWith('[')) {
                            const parsed = JSON.parse(room.amenities);
                            if (Array.isArray(parsed)) amenitiesVal = parsed.join(', ');
                        } else if (room.amenities) {
                            amenitiesVal = room.amenities;
                        }
                    } catch (e) {
                        amenitiesVal = room.amenities || '';
                    }
                    
                    const imgElement = document.getElementById('features-image');
                    if (room.image) {
                        imgElement.src = room.image.startsWith('http') ? room.image : '../' + room.image;
                        imgElement.classList.remove('hidden');
                    } else {
                        imgElement.classList.add('hidden');
                    }
                    
                    document.getElementById('features-modal-title').innerText = room.name + ' Features';
                    document.getElementById('features-description').innerText = room.description || 'No description available.';
                    document.getElementById('features-amenities').innerText = amenitiesVal || 'No amenities available.';
                    document.getElementById('features-capacity').innerText = room.capacity + ' Guest(s)';
                    
                    document.getElementById('room-features-modal').classList.remove('hidden');
                }
            }
        });
}

function closeFeaturesModal() {
    document.getElementById('room-features-modal').classList.add('hidden');
}

// Load rooms when page loads
document.addEventListener('DOMContentLoaded', loadRooms);
</script>
