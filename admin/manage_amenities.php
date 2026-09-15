<!-- ================= AMENITIES MANAGEMENT ================= -->
<div id="manage-amenities" class="page-section">
    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h3 class="text-2xl font-bold text-brand-dark">Amenities Management</h3>
            <p class="text-gray-500 text-sm">Create, edit, and organize signature inn amenities, icons, and descriptions</p>
        </div>
        <button onclick="openAmenityModal()" class="bg-brand-purple hover:bg-purple-700 text-white px-4 py-2.5 rounded-xl font-medium transition-all duration-200 flex items-center gap-2 shadow-sm hover:shadow-md cursor-pointer">
            <i class="fas fa-plus"></i>
            <span>Add New Amenity</span>
        </button>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4">
            <!-- Filter Tabs -->
            <div class="flex flex-wrap gap-2">
                <button onclick="setAmenityFilter('all')" id="amenity-filter-all" class="amenity-filter-tab px-4 py-2 rounded-xl text-sm font-semibold transition bg-brand-purple text-white shadow-sm">
                    All Amenities (<span id="amenity-count-all">0</span>)
                </button>
                <button onclick="setAmenityFilter('active')" id="amenity-filter-active" class="amenity-filter-tab px-4 py-2 rounded-xl text-sm font-semibold transition bg-gray-100 hover:bg-emerald-50 text-gray-600 hover:text-emerald-700 border border-transparent hover:border-emerald-200">
                    Active (<span id="amenity-count-active">0</span>)
                </button>
                <button onclick="setAmenityFilter('disabled')" id="amenity-filter-disabled" class="amenity-filter-tab px-4 py-2 rounded-xl text-sm font-semibold transition bg-gray-100 hover:bg-rose-50 text-gray-600 hover:text-rose-700 border border-transparent hover:border-rose-200">
                    Disabled (<span id="amenity-count-disabled">0</span>)
                </button>
            </div>

            <!-- Search Field -->
            <div class="relative w-full md:w-72">
                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" id="amenity-search-input" oninput="handleAmenitySearch()" placeholder="Search amenities..." 
                       class="w-full pl-10 pr-4 py-2 bg-gray-50 hover:bg-gray-100/80 focus:bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-purple/20 focus:border-brand-purple transition">
            </div>
        </div>
    </div>

    <!-- Amenities Card Grid -->
    <div id="amenities-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Rendered dynamically by loadAmenities() -->
        <div class="col-span-full py-16 text-center text-gray-400">
            <i class="fas fa-spinner fa-spin text-3xl mb-3 text-brand-purple"></i>
            <p>Loading amenities...</p>
        </div>
    </div>
</div>

<!-- ================= ADD / EDIT AMENITY MODAL ================= -->
<div id="amenity-modal" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-xl w-full max-h-[92vh] overflow-y-auto border border-gray-100 animate-fadeIn">
        
        <!-- Modal Header -->
        <div class="p-6 border-b border-gray-100 flex justify-between items-center sticky top-0 bg-white/95 backdrop-blur-md z-10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-purple-50 text-brand-purple flex items-center justify-center text-lg">
                    <i id="modal-header-icon" class="fas fa-concierge-bell"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-brand-dark" id="amenity-modal-title">Add New Amenity</h3>
                    <p class="text-xs text-gray-400">Configure amenity details, icon, and display order</p>
                </div>
            </div>
            <button type="button" onclick="closeAmenityModal()" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 hover:text-gray-800 flex items-center justify-center transition cursor-pointer">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <!-- Modal Form -->
        <form id="amenity-form" onsubmit="saveAmenity(event)" class="p-6 space-y-5">
            <input type="hidden" id="amenity-id" value="">

            <!-- Title -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1.5 flex items-center gap-1.5">
                    <i class="fas fa-tag text-brand-purple"></i> Amenity Title *
                </label>
                <input type="text" id="amenity-title" required placeholder="e.g. Nomad-Grade Starlink WiFi" 
                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-purple/20 focus:border-brand-purple transition font-medium">
            </div>

            <!-- Category & Sort Order Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1.5 flex items-center gap-1.5">
                        <i class="fas fa-folder text-brand-purple"></i> Category
                    </label>
                    <select id="amenity-category" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-purple/20 focus:border-brand-purple transition font-medium cursor-pointer">
                        <option value="Connectivity">Connectivity</option>
                        <option value="Location">Location</option>
                        <option value="Social">Social & Chill</option>
                        <option value="Adventures">Adventures & Tours</option>
                        <option value="Security">Security & Safety</option>
                        <option value="Dining">Dining & Drinks</option>
                        <option value="Comfort">Comfort & Rooms</option>
                        <option value="Leisure">Leisure & Wellness</option>
                        <option value="General">General</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1.5 flex items-center gap-1.5">
                        <i class="fas fa-sort-numeric-down text-brand-purple"></i> Display Order
                    </label>
                    <input type="number" id="amenity-sort-order" value="0" min="0" 
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-purple/20 focus:border-brand-purple transition font-medium">
                </div>
            </div>

            <!-- Icon Picker Section -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1.5 flex items-center justify-between">
                    <span class="flex items-center gap-1.5">
                        <i class="fas fa-icons text-brand-purple"></i> Select Icon *
                    </span>
                    <span class="text-[11px] text-gray-400 font-normal lowercase">Click a preset or enter class</span>
                </label>

                <!-- Presets Palette -->
                <div class="grid grid-cols-6 sm:grid-cols-9 gap-2 mb-3" id="icon-preset-palette">
                    <!-- Populated dynamically via JS -->
                </div>

                <!-- Custom Icon Class Input with Live Preview -->
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-purple-100 text-brand-purple flex items-center justify-center text-xl flex-shrink-0 shadow-inner" id="amenity-icon-preview">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="flex-1 relative">
                        <input type="text" id="amenity-icon" value="fas fa-star" oninput="updateAmenityIconPreview(this.value)" placeholder="e.g. fas fa-wifi" 
                               class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-purple/20 focus:border-brand-purple transition font-mono">
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1.5 flex items-center gap-1.5">
                    <i class="fas fa-align-left text-brand-purple"></i> Description *
                </label>
                <textarea id="amenity-description" rows="3" required placeholder="Describe what makes this amenity great for guests..." 
                          class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-purple/20 focus:border-brand-purple transition font-normal resize-none"></textarea>
            </div>

            <!-- Optional Image Upload / URL -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1.5 flex items-center justify-between">
                    <span class="flex items-center gap-1.5">
                        <i class="fas fa-image text-brand-purple"></i> Image (Optional)
                    </span>
                    <span class="text-[11px] text-gray-400 font-normal">Photo preview / card background</span>
                </label>
                <div class="flex gap-2">
                    <input type="text" id="amenity-image-url" placeholder="assets/image/your-photo.jpg" 
                           class="flex-1 px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-purple/20 focus:border-brand-purple transition">
                    <button type="button" onclick="document.getElementById('amenity-file-input').click()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-medium transition flex items-center gap-2 cursor-pointer">
                        <i class="fas fa-upload"></i>
                        <span class="hidden sm:inline">Upload</span>
                    </button>
                </div>
                <input type="file" id="amenity-file-input" accept="image/*" class="hidden" onchange="previewAmenityImage(event)">

                <div id="amenity-image-preview-box" class="mt-2 hidden rounded-xl overflow-hidden border border-gray-200 relative h-32 bg-gray-100">
                    <img id="amenity-preview-img" src="" alt="Amenity Preview" class="w-full h-full object-cover">
                    <button type="button" onclick="clearAmenityImage()" class="absolute top-2 right-2 bg-black/60 hover:bg-black/80 text-white rounded-full w-7 h-7 flex items-center justify-center text-xs transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Active Status Toggle Checkbox -->
            <div class="pt-2">
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input type="checkbox" id="amenity-is-active" checked class="w-4 h-4 rounded text-brand-purple focus:ring-brand-purple/20 border-gray-300">
                    <span class="text-sm font-semibold text-gray-700">Display this amenity on guest website (Active)</span>
                </label>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="closeAmenityModal()" class="flex-1 py-3 border border-gray-200 rounded-xl font-semibold text-gray-600 hover:bg-gray-50 transition cursor-pointer">
                    Cancel
                </button>
                <button type="submit" id="save-amenity-btn" class="flex-1 py-3 bg-brand-purple hover:bg-purple-700 text-white rounded-xl font-bold transition flex items-center justify-center gap-2 shadow-sm hover:shadow cursor-pointer">
                    <span id="save-amenity-text">Save Amenity</span>
                    <i class="fas fa-spinner fa-spin text-sm hidden" id="save-amenity-spinner"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// --- AMENITY MODULE STATE ---
let allAmenities = [];
let currentAmenityFilter = 'all';
let amenitySearchQuery = '';

// Preset FontAwesome travel & hospitality icons
const PRESET_ICONS = [
    { icon: 'fas fa-umbrella-beach', label: 'Beach' },
    { icon: 'fas fa-wifi', label: 'WiFi' },
    { icon: 'fas fa-cocktail', label: 'Bar' },
    { icon: 'fas fa-motorcycle', label: 'Scooter' },
    { icon: 'fas fa-key', label: 'Lockers' },
    { icon: 'fas fa-mug-hot', label: 'Coffee' },
    { icon: 'fas fa-swimming-pool', label: 'Pool' },
    { icon: 'fas fa-snowflake', label: 'A/C' },
    { icon: 'fas fa-shower', label: 'Shower' },
    { icon: 'fas fa-bed', label: 'Bed' },
    { icon: 'fas fa-tv', label: 'TV' },
    { icon: 'fas fa-utensils', label: 'Kitchen' },
    { icon: 'fas fa-dumbbell', label: 'Fitness' },
    { icon: 'fas fa-shield-alt', label: 'Security' },
    { icon: 'fas fa-water', label: 'Water' },
    { icon: 'fas fa-concierge-bell', label: 'Desk' },
    { icon: 'fas fa-gamepad', label: 'Lounge' },
    { icon: 'fas fa-bicycle', label: 'Bike' }
];

// Initialize icon presets palette
function renderIconPresets(selectedClass = '') {
    const container = document.getElementById('icon-preset-palette');
    if (!container) return;

    container.innerHTML = PRESET_ICONS.map(item => {
        const isSelected = selectedClass === item.icon;
        return `
            <button type="button" 
                    title="${item.label}"
                    onclick="selectPresetIcon('${item.icon}')"
                    class="w-9 h-9 rounded-xl border flex items-center justify-center text-sm transition cursor-pointer ${
                        isSelected 
                            ? 'bg-brand-purple text-white border-brand-purple shadow-sm scale-105' 
                            : 'bg-gray-50 hover:bg-purple-50 text-gray-600 hover:text-brand-purple border-gray-200'
                    }">
                <i class="${item.icon}"></i>
            </button>
        `;
    }).join('');
}

function selectPresetIcon(iconClass) {
    document.getElementById('amenity-icon').value = iconClass;
    updateAmenityIconPreview(iconClass);
    renderIconPresets(iconClass);
}

function updateAmenityIconPreview(iconClass) {
    const previewEl = document.getElementById('amenity-icon-preview');
    if (previewEl) {
        previewEl.innerHTML = `<i class="${iconClass || 'fas fa-star'}"></i>`;
    }
}

// Image upload preview for Amenity Modal
function previewAmenityImage(event) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        const previewBox = document.getElementById('amenity-image-preview-box');
        const img = document.getElementById('amenity-preview-img');
        if (previewBox && img) {
            img.src = e.target.result;
            previewBox.classList.remove('hidden');
        }
        document.getElementById('amenity-image-url').value = 'assets/image/' + file.name;
    };
    reader.readAsDataURL(file);
}

function clearAmenityImage() {
    document.getElementById('amenity-file-input').value = '';
    document.getElementById('amenity-image-url').value = '';
    const previewBox = document.getElementById('amenity-image-preview-box');
    if (previewBox) previewBox.classList.add('hidden');
}

// Fetch and load amenities from API
async function loadAmenities() {
    const grid = document.getElementById('amenities-grid');
    if (!grid) return;

    try {
        const res = await fetch('../actions/amenities_action.php?action=get_all');
        const data = await res.json();

        if (data.success && Array.isArray(data.amenities)) {
            allAmenities = data.amenities;
            updateAmenityCounters();
            renderAmenities();
        } else {
            grid.innerHTML = `
                <div class="col-span-full py-12 text-center text-gray-500">
                    <i class="fas fa-exclamation-circle text-2xl text-amber-500 mb-2"></i>
                    <p>${data.message || 'No amenities found'}</p>
                </div>
            `;
        }
    } catch (err) {
        console.error('Failed to load amenities:', err);
        grid.innerHTML = `
            <div class="col-span-full py-12 text-center text-rose-500">
                <i class="fas fa-times-circle text-2xl mb-2"></i>
                <p>Failed to connect to amenities service.</p>
            </div>
        `;
    }
}

// Update filter counters
function updateAmenityCounters() {
    const total = allAmenities.length;
    const active = allAmenities.filter(a => parseInt(a.is_active) === 1).length;
    const disabled = total - active;

    const elTotal = document.getElementById('amenity-count-all');
    const elActive = document.getElementById('amenity-count-active');
    const elDisabled = document.getElementById('amenity-count-disabled');

    if (elTotal) elTotal.textContent = total;
    if (elActive) elActive.textContent = active;
    if (elDisabled) elDisabled.textContent = disabled;
}

// Filter tab handler
function setAmenityFilter(filter) {
    currentAmenityFilter = filter;
    
    // Update button styling
    ['all', 'active', 'disabled'].forEach(f => {
        const btn = document.getElementById(`amenity-filter-${f}`);
        if (!btn) return;
        if (f === filter) {
            btn.className = 'amenity-filter-tab px-4 py-2 rounded-xl text-sm font-semibold transition bg-brand-purple text-white shadow-sm';
        } else {
            btn.className = 'amenity-filter-tab px-4 py-2 rounded-xl text-sm font-semibold transition bg-gray-100 hover:bg-gray-200 text-gray-600 border border-transparent';
        }
    });

    renderAmenities();
}

function handleAmenitySearch() {
    const searchInput = document.getElementById('amenity-search-input');
    amenitySearchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    renderAmenities();
}

// Render cards into amenities grid
function renderAmenities() {
    const grid = document.getElementById('amenities-grid');
    if (!grid) return;

    let filtered = [...allAmenities];

    if (currentAmenityFilter === 'active') {
        filtered = filtered.filter(a => parseInt(a.is_active) === 1);
    } else if (currentAmenityFilter === 'disabled') {
        filtered = filtered.filter(a => parseInt(a.is_active) === 0);
    }

    if (amenitySearchQuery) {
        filtered = filtered.filter(a => 
            (a.title && a.title.toLowerCase().includes(amenitySearchQuery)) ||
            (a.description && a.description.toLowerCase().includes(amenitySearchQuery)) ||
            (a.category && a.category.toLowerCase().includes(amenitySearchQuery))
        );
    }

    if (filtered.length === 0) {
        grid.innerHTML = `
            <div class="col-span-full py-16 text-center text-gray-400 bg-white rounded-3xl border border-gray-100 p-8">
                <div class="w-16 h-16 rounded-full bg-purple-50 text-brand-purple flex items-center justify-center text-2xl mx-auto mb-3">
                    <i class="fas fa-concierge-bell"></i>
                </div>
                <h4 class="text-base font-bold text-gray-700 mb-1">No amenities found</h4>
                <p class="text-xs text-gray-400 max-w-sm mx-auto mb-4">No amenities match your current filter or search criteria.</p>
                <button onclick="openAmenityModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-brand-purple text-white rounded-xl text-xs font-semibold hover:bg-purple-700 transition">
                    <i class="fas fa-plus"></i> Add Amenity
                </button>
            </div>
        `;
        return;
    }

    grid.innerHTML = filtered.map(item => {
        const isActive = parseInt(item.is_active) === 1;
        const iconClass = item.icon || 'fas fa-star';
        const category = item.category || 'General';
        const sortOrder = item.sort_order ?? 0;

        return `
            <div class="bg-white rounded-3xl p-6 border ${isActive ? 'border-gray-100 hover:border-brand-purple/40' : 'border-rose-100 bg-rose-50/20 opacity-85'} shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                
                <!-- Card Top: Icon, Category, Status -->
                <div>
                    <div class="flex items-start justify-between mb-4">
                        <div class="w-13 h-13 rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 text-white flex items-center justify-center text-2xl shadow-md group-hover:scale-105 transition-transform">
                            <i class="${iconClass}"></i>
                        </div>
                        <div class="flex flex-col items-end gap-1.5">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold tracking-wide uppercase ${
                                isActive ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-rose-50 text-rose-700 border border-rose-200/60'
                            }">
                                ${isActive ? '● Active' : '○ Disabled'}
                            </span>
                            <span class="text-[10px] font-semibold text-gray-400 bg-gray-100 px-2 py-0.5 rounded-md">
                                #${sortOrder}
                            </span>
                        </div>
                    </div>

                    <!-- Category Pill & Title -->
                    <span class="text-[11px] font-bold uppercase tracking-wider text-brand-purple mb-1 block">
                        ${category}
                    </span>
                    <h4 class="text-lg font-bold text-gray-900 group-hover:text-brand-purple transition-colors line-clamp-1 mb-2">
                        ${escapeHtml(item.title)}
                    </h4>

                    <!-- Description -->
                    <p class="text-gray-500 text-xs sm:text-sm leading-relaxed line-clamp-3 mb-4">
                        ${escapeHtml(item.description)}
                    </p>

                    ${item.image_url ? `
                        <div class="mb-4 h-36 rounded-2xl overflow-hidden border border-gray-100 bg-gray-50 relative group/img">
                            <img src="${(item.image_url.startsWith('http://') || item.image_url.startsWith('https://')) ? item.image_url : '../' + item.image_url}" alt="${escapeHtml(item.title)}" class="w-full h-full object-cover group-hover/img:scale-105 transition-transform duration-300">
                        </div>
                    ` : ''}
                </div>

                <!-- Card Bottom: Actions -->
                <div class="pt-4 border-t border-gray-100 flex items-center justify-between gap-2 mt-2">
                    
                    <!-- Quick Toggle Switch -->
                    <label class="inline-flex items-center gap-2 cursor-pointer select-none" title="Toggle visibility">
                        <input type="checkbox" class="sr-only peer" ${isActive ? 'checked' : ''} onchange="toggleAmenityStatus(${item.id})">
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-purple"></div>
                        <span class="text-xs text-gray-400 font-medium">Visible</span>
                    </label>

                    <div class="flex items-center gap-1.5">
                        <button onclick="editAmenity(${item.id})" class="p-2 text-gray-500 hover:text-brand-purple hover:bg-purple-50 rounded-xl transition cursor-pointer" title="Edit Amenity">
                            <i class="fas fa-edit text-sm"></i>
                        </button>
                        <button onclick="deleteAmenity(${item.id})" class="p-2 text-gray-500 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition cursor-pointer" title="Delete Amenity">
                            <i class="fas fa-trash-alt text-sm"></i>
                        </button>
                    </div>
                </div>

            </div>
        `;
    }).join('');
}

// Modal controls
function openAmenityModal(id = null) {
    const modal = document.getElementById('amenity-modal');
    const form = document.getElementById('amenity-form');
    const title = document.getElementById('amenity-modal-title');
    const saveText = document.getElementById('save-amenity-text');
    const headerIcon = document.getElementById('modal-header-icon');

    form.reset();
    clearAmenityImage();

    if (id) {
        const item = allAmenities.find(a => parseInt(a.id) === parseInt(id));
        if (item) {
            title.textContent = 'Edit Amenity';
            saveText.textContent = 'Update Amenity';
            if (headerIcon) headerIcon.className = item.icon || 'fas fa-edit';

            document.getElementById('amenity-id').value = item.id;
            document.getElementById('amenity-title').value = item.title || '';
            document.getElementById('amenity-category').value = item.category || 'General';
            document.getElementById('amenity-sort-order').value = item.sort_order ?? 0;
            document.getElementById('amenity-icon').value = item.icon || 'fas fa-star';
            document.getElementById('amenity-description').value = item.description || '';
            document.getElementById('amenity-is-active').checked = parseInt(item.is_active) === 1;

            if (item.image_url) {
                document.getElementById('amenity-image-url').value = item.image_url;
                const previewBox = document.getElementById('amenity-image-preview-box');
                const img = document.getElementById('amenity-preview-img');
                if (previewBox && img) {
                    img.src = (item.image_url.startsWith('http://') || item.image_url.startsWith('https://')) ? item.image_url : '../' + item.image_url;
                    previewBox.classList.remove('hidden');
                }
            }

            updateAmenityIconPreview(item.icon);
            renderIconPresets(item.icon);
        }
    } else {
        title.textContent = 'Add New Amenity';
        saveText.textContent = 'Save Amenity';
        if (headerIcon) headerIcon.className = 'fas fa-concierge-bell';

        document.getElementById('amenity-id').value = '';
        document.getElementById('amenity-sort-order').value = allAmenities.length + 1;
        document.getElementById('amenity-icon').value = 'fas fa-star';
        document.getElementById('amenity-is-active').checked = true;

        updateAmenityIconPreview('fas fa-star');
        renderIconPresets('fas fa-star');
    }

    modal.classList.remove('hidden');
}

function closeAmenityModal() {
    const modal = document.getElementById('amenity-modal');
    if (modal) modal.classList.add('hidden');
}

function editAmenity(id) {
    openAmenityModal(id);
}

// Save Amenity (Create or Update)
async function saveAmenity(e) {
    e.preventDefault();

    const saveBtn = document.getElementById('save-amenity-btn');
    const spinner = document.getElementById('save-amenity-spinner');
    const id = document.getElementById('amenity-id').value;
    const action = id ? 'update' : 'create';

    const formData = new FormData();
    formData.append('action', action);
    if (id) formData.append('id', id);
    formData.append('title', document.getElementById('amenity-title').value.trim());
    formData.append('category', document.getElementById('amenity-category').value);
    formData.append('sort_order', document.getElementById('amenity-sort-order').value);
    formData.append('icon', document.getElementById('amenity-icon').value.trim());
    formData.append('description', document.getElementById('amenity-description').value.trim());
    formData.append('image_url', document.getElementById('amenity-image-url').value.trim());
    formData.append('is_active', document.getElementById('amenity-is-active').checked ? 1 : 0);

    const fileInput = document.getElementById('amenity-file-input');
    if (fileInput && fileInput.files[0]) {
        formData.append('image_file', fileInput.files[0]);
    }

    try {
        if (saveBtn) saveBtn.disabled = true;
        if (spinner) spinner.classList.remove('hidden');

        const res = await fetch('../actions/amenities_action.php', {
            method: 'POST',
            body: formData
        });
        const result = await res.json();

        if (result.success) {
            if (typeof window.showToast === 'function') {
                window.showToast(result.message || 'Amenity saved successfully!', 'success');
            }
            closeAmenityModal();
            loadAmenities();
        } else {
            if (typeof window.showToast === 'function') {
                window.showToast(result.message || 'Failed to save amenity', 'error');
            }
        }
    } catch (err) {
        console.error('Error saving amenity:', err);
        if (typeof window.showToast === 'function') {
            window.showToast('Server error while saving amenity', 'error');
        }
    } finally {
        if (saveBtn) saveBtn.disabled = false;
        if (spinner) spinner.classList.add('hidden');
    }
}

// Toggle status via AJAX
async function toggleAmenityStatus(id) {
    try {
        const formData = new FormData();
        formData.append('action', 'toggle');
        formData.append('id', id);

        const res = await fetch('../actions/amenities_action.php', {
            method: 'POST',
            body: formData
        });
        const result = await res.json();

        if (result.success) {
            const item = allAmenities.find(a => parseInt(a.id) === parseInt(id));
            if (item) {
                item.is_active = result.is_active;
                updateAmenityCounters();
                renderAmenities();
            }
            if (typeof window.showToast === 'function') {
                window.showToast('Amenity status updated', 'success');
            }
        } else {
            if (typeof window.showToast === 'function') {
                window.showToast(result.message || 'Failed to update status', 'error');
            }
        }
    } catch (err) {
        console.error('Error toggling status:', err);
    }
}

// Delete amenity
function deleteAmenity(id) {
    const item = allAmenities.find(a => parseInt(a.id) === parseInt(id));
    const title = item ? item.title : 'this amenity';

    const confirmAction = async () => {
        try {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);

            const res = await fetch('../actions/amenities_action.php', {
                method: 'POST',
                body: formData
            });
            const result = await res.json();

            if (result.success) {
                if (typeof window.showToast === 'function') {
                    window.showToast('Amenity deleted successfully', 'success');
                }
                loadAmenities();
            } else {
                if (typeof window.showToast === 'function') {
                    window.showToast(result.message || 'Failed to delete amenity', 'error');
                }
            }
        } catch (err) {
            console.error('Error deleting amenity:', err);
            if (typeof window.showToast === 'function') {
                window.showToast('Network error while deleting amenity', 'error');
            }
        }
    };

    if (typeof window.showConfirm === 'function') {
        window.showConfirm(`Are you sure you want to permanently delete "${title}"?`, confirmAction, {
            confirmText: 'Delete',
            confirmColor: 'bg-rose-600 hover:bg-rose-700'
        });
    } else if (confirm(`Are you sure you want to delete "${title}"?`)) {
        confirmAction();
    }
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// Auto-load when section is shown or on ready
document.addEventListener('DOMContentLoaded', () => {
    loadAmenities();
});
</script>

