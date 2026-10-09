<!-- ================= CAROUSEL & SLIDER MANAGEMENT ================= -->
<div id="manage-sliders" class="page-section">
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <p class="text-gray-500 text-sm">Manage hero carousel and island gallery carousel images</p>
        </div>
        <button onclick="openSliderModal()" class="bg-brand-purple hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium transition flex items-center gap-2">
            <i class="fas fa-plus"></i> Add New Slider
        </button>
    </div>

    <!-- Filter Tabs & Bulk Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="flex flex-wrap gap-2 mb-4">
            <button onclick="setFilter('all')" id="filter-all" class="filter-tab px-4 py-2 bg-brand-purple text-white rounded-lg font-medium active-filter-tab">All Sliders</button>
            <button onclick="setFilter('active')" id="filter-active" class="filter-tab px-4 py-2 bg-green-500/20 text-green-700 hover:bg-green-500/40 border border-green-500/30 rounded-lg font-medium">Active (<span id="count-active">0</span>)</button>
            <button onclick="setFilter('disabled')" id="filter-disabled" class="filter-tab px-4 py-2 bg-red-500/20 text-red-700 hover:bg-red-500/40 border border-red-500/30 rounded-lg font-medium">Disabled (<span id="count-disabled">0</span>)</button>
        </div>
        <div id="bulk-actions" class="flex gap-2 flex-wrap items-center">
            <span id="selection-count" class="text-sm text-gray-500 hidden">0 selected</span>
            <button onclick="bulkEnableSelected()" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-medium text-sm transition hidden" id="bulk-enable-btn">
                <i class="fas fa-eye mr-1"></i>Enable Selected
            </button>
            <button onclick="bulkDisableSelected()" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-medium text-sm transition hidden" id="bulk-disable-btn">
                <i class="fas fa-eye-slash mr-1"></i>Disable Selected
            </button>
            <button onclick="enableAllDisabled()" class="px-4 py-2 bg-brand-purple hover:bg-purple-700 text-white rounded-lg font-medium text-sm transition">
                <i class="fas fa-magic mr-1"></i>Enable All Disabled
            </button>
        </div>
    </div>

    <!-- Hero Carousel Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <h4 class="text-lg font-bold text-brand-dark mb-4 flex items-center gap-2">
            <i class="fas fa-images text-brand-purple"></i> Hero Carousel
        </h4>
        <div id="hero-sliders-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Hero sliders will be loaded here -->
        </div>
    </div>

    <!-- Gallery Carousel Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h4 class="text-lg font-bold text-brand-dark mb-4 flex items-center gap-2">
            <i class="fas fa-photo-video text-brand-purple"></i> Island Gallery Carousel
        </h4>
        <div id="marquee-sliders-grid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <!-- Gallery carousel sliders will be loaded here -->
        </div>
    </div>
</div>

<!-- Slider Modal -->
<div id="slider-modal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-xl font-bold text-brand-dark" id="slider-modal-title">Add New Slider</h3>
            <button onclick="closeSliderModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form id="slider-form" class="p-6 space-y-4">
            <input type="hidden" id="slider-id">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" id="slider-title" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-purple/20" placeholder="Enter slider title">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
                <div class="flex gap-2">
                    <input type="text" id="slider-image-url" class="flex-1 px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-purple/20" placeholder="assets/image/your-image.jpg">
                    <button type="button" onclick="browseImage('slider-image-url')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                        <i class="fas fa-folder-open"></i>
                    </button>
                </div>
<input type="file" id="image-file-input" accept="image/*" style="display:none;">
<p class="text-xs text-gray-500 mt-1">🔥 Select image below - it uploads automatically to assets/image/ on Save! No manual copy needed.</p>
<div id="slider-image-preview" class="mt-2 text-center p-4 border-2 border-dashed border-gray-200 rounded-lg bg-gray-50">
<img id="preview-img" src="" alt="Click to select image" class="w-full h-32 object-cover rounded-lg cursor-pointer hover:opacity-90 transition" onclick="document.getElementById('image-file-input').click();">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select id="slider-type" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-purple/20">
                    <option value="hero">Hero Carousel</option>
                    <option value="marquee">Island Gallery Carousel</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                <input type="number" id="slider-sort-order" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-purple/20" value="0" min="0">
            </div>
            
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeSliderModal()" class="flex-1 px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition">Cancel</button>
<button type="submit" id="save-slider-btn" class="flex-1 px-4 py-2 bg-brand-purple hover:bg-purple-700 text-white rounded-lg transition">
    Save
    <i class="fas fa-spinner fa-spin ml-1 hidden" id="save-spinner"></i>
</button>
            </div>
        </form>
    </div>
</div>

<!-- Image Browser Modal -->
<!-- (Future implementation for browsing existing assets) -->

<script>
// Image file picker support
document.getElementById('image-file-input').addEventListener('change', function(e) {
  const file = e.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function(ev) {
      document.getElementById('preview-img').src = ev.target.result;
      document.getElementById('preview-img').alt = file.name;
      document.getElementById('slider-image-preview').classList.remove('text-center', 'p-4', 'border-2', 'border-dashed', 'border-gray-200', 'bg-gray-50');
      document.getElementById('slider-image-url').value = 'assets/image/' + file.name;
    };
    reader.readAsDataURL(file);
  }
});

// --- GLOBAL STATE ---
let allSliders = [];
let currentFilter = 'all';
let selectedIds = new Set();

// Bulk toggle helper
function bulkToggle(status) {
  if (selectedIds.size === 0) {
    showToast('No sliders selected', 'warning');
    return;
  }
  showConfirm(`Set ${selectedIds.size} slider(s) to ${status ? 'enabled' : 'disabled'}?`, () => {
    let completed = 0;
    selectedIds.forEach(id => {
      const formData = new FormData();
      formData.append('action', 'toggle');
      formData.append('id', id);
      fetch('../actions/sliders_action.php', {method: 'POST', body: formData})
        .then(res => res.json())
        .then(result => {
          if (result.success) completed++;
          if (completed === selectedIds.size) {
            selectedIds.clear();
            loadSliders();
          }
        })
        .catch(() => {});
    });
  });
}

// Public functions for HTML buttons
// Button handler - calls bulkToggle(true) to enable all selected sliders
function bulkEnableSelected() { bulkToggle(true); }
// Button handler for disabling selected sliders - calls bulkToggle(false)
function bulkDisableSelected() { bulkToggle(false); }

// Enable all currently disabled sliders
function enableAllDisabled() {
  const disabled = allSliders.filter(s => s.is_active != 1).map(s => s.id);
  if (disabled.length === 0) {
    showToast('No disabled sliders', 'warning');
    return;
  }
  showConfirm(`Enable ${disabled.length} disabled slider(s)?`, () => {
    disabled.forEach(id => {
      const formData = new FormData();
      formData.append('action', 'toggle');
      formData.append('id', id);
      fetch('../actions/sliders_action.php', {method: 'POST', body: formData}).catch(() => {});
    });
    loadSliders();
  });
}

// Handle individual checkbox selection
function toggleCheckbox(id) {
  const cb = document.querySelector(`.slider-checkbox[data-id="${id}"]`);
  if (cb) {
    cb.checked = !cb.checked;
    if (cb.checked) selectedIds.add(id);
    else selectedIds.delete(id);
    updateBulkActions();
  }
}

// Update filter tab counters
function updateCounts() {
  const activeCount = allSliders.filter(s => s.is_active == 1).length;
  const disabledCount = allSliders.length - activeCount;
  document.getElementById('count-active').textContent = activeCount;
  document.getElementById('count-disabled').textContent = disabledCount || 0;
}

// Toggle visibility of bulk action buttons
function updateBulkActions() {
  const count = selectedIds.size;
  document.getElementById('selection-count').textContent = `${count} selected`;
  document.getElementById('selection-count').classList.toggle('hidden', count === 0);
  document.getElementById('bulk-enable-btn').classList.toggle('hidden', count === 0);
  document.getElementById('bulk-disable-btn').classList.toggle('hidden', count === 0);
}

function updateSelectionUI() {
  // Rebind checkbox events after render
  document.querySelectorAll('.slider-checkbox').forEach(cb => {
    cb.removeEventListener('change', checkboxHandler);
    cb.addEventListener('change', checkboxHandler);
  });
}

function checkboxHandler(e) {
  const id = parseInt(e.target.dataset.id);
  if (e.target.checked) selectedIds.add(id);
  else selectedIds.delete(id);
  updateBulkActions();
}

// Replace static querySelectorAll with dynamic rebinding

// Load sliders on page load
document.addEventListener('DOMContentLoaded', function() {
    loadSliders();
    
    // Setup form submission
document.getElementById('slider-form').addEventListener('submit', function(e) {
    console.log('Form submit triggered');
    e.preventDefault();
    saveSlider();
});

// Fallback: direct button click
document.addEventListener('click', function(e) {
    if (e.target.id === 'save-slider-btn') {
        console.log('Save button clicked directly');
        e.preventDefault();
        saveSlider();
    }
});
    
    // Image URL preview
    document.getElementById('slider-image-url').addEventListener('input', function() {
        const previewDiv = document.getElementById('slider-image-preview');
        const img = document.getElementById('preview-img');
        if (this.value && img.src === '') {  // Only if no file preview
            img.src = this.value;
            previewDiv.classList.remove('text-center', 'p-4', 'border-2', 'border-dashed', 'border-gray-200', 'bg-gray-50');
        }
    });
});

// Fetch all slider data from API
function loadSliders() {
    fetch('../actions/sliders_action.php?action=get_all')
        .then(res => {
            console.log('Load sliders status:', res.status, res.url);
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            return res.json();
        })
        .then(data => {
            console.log('Load sliders data:', data);
            if (data.success) {
                allSliders = data.sliders || [];
                updateCounts();
                currentFilter = 'all';
                document.getElementById('filter-all').classList.add('active-filter-tab', 'bg-brand-purple', 'text-white');
                document.getElementById('filter-active').classList.remove('active-filter-tab', 'bg-brand-purple', 'text-white');
                document.getElementById('filter-disabled').classList.remove('active-filter-tab', 'bg-brand-purple', 'text-white');
                renderSliders();
                selectedIds.clear();
                updateBulkActions();
            } else {
                console.error('Load failed:', data.message);
            }
        })
        .catch(err => {
            console.error('Load sliders failed:', err);
        });
}

// Switch between All, Active, and Disabled views
function setFilter(filter) {
    currentFilter = filter;
    document.querySelectorAll('.filter-tab').forEach(tab => tab.classList.remove('active-filter-tab', 'bg-brand-purple', 'text-white'));
    document.getElementById(`filter-${filter}`).classList.add('active-filter-tab', 'bg-brand-purple', 'text-white');
    renderSliders();
    updateBulkActions();
}

// Render the grid of slider cards based on current filter
function renderSliders(slidersInput = null) {
    const sliders = slidersInput || allSliders.filter(s => {
        const active = s.is_active == 1;
        switch(currentFilter) {
            case 'active': return active;
            case 'disabled': return !active;
            default: return true;
        }
    });

    const heroGrid = document.getElementById('hero-sliders-grid');
    const marqueeGrid = document.getElementById('marquee-sliders-grid');
    
    if (heroGrid) heroGrid.innerHTML = '';
    if (marqueeGrid) marqueeGrid.innerHTML = '';
    
    if (sliders.length === 0) {
        const emptyMsg = '<p class="col-span-full text-center text-gray-500 italic py-8">No sliders found.</p>';
        if (heroGrid) heroGrid.innerHTML = emptyMsg;
        if (marqueeGrid) marqueeGrid.innerHTML = emptyMsg;
        return;
    }
    
    sliders.forEach(slider => {
        const isActive = slider.is_active == 1;
        const isSelected = selectedIds.has(slider.id);
        
        const card = `
<div class="relative group rounded-lg overflow-hidden border ${isActive ? 'border-gray-200' : 'border-red-200'}">
                <input type="checkbox" class="slider-checkbox absolute top-2 left-2 z-20 w-5 h-5 rounded border-2 border-gray-300 ${isActive ? 'bg-green-100 border-green-400' : 'bg-red-100 border-red-400'} cursor-pointer" data-id="${slider.id}" onchange="checkboxHandler(event)" ${isSelected ? 'checked' : ''}>
<img src="../${slider.image_url}" alt="${slider.title}" class="w-full h-32 object-cover">
                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2">
                    <button onclick="toggleCheckbox(${slider.id}); editSlider(${slider.id})" class="p-2 bg-white rounded-full text-brand-dark hover:text-brand-purple transition" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="toggleSliderStatus(${slider.id})" class="p-2 ${isActive ? 'bg-green-500' : 'bg-gray-500'} rounded-full text-white transition" title="${isActive ? 'Disable' : 'Enable'}">
                        <i class="fas fa-${isActive ? 'eye' : 'eye-slash'}"></i>
                    </button>
                    <button onclick="deleteSlider(${slider.id})" class="p-2 bg-red-500 rounded-full text-white hover:bg-red-600 transition" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-2">
                    <p class="text-white text-sm font-medium truncate">${slider.title || 'Untitled'}</p>
                    <p class="text-white/70 text-xs">Order: ${slider.sort_order}</p>
                </div>
                ${!isActive ? '<div class="absolute inset-0 bg-black/50 flex items-center justify-center"><span class="bg-red-500 text-white text-xs px-2 py-1 rounded">Disabled</span></div>' : ''}
            </div>
        `;
        
        if (slider.type === 'hero' && heroGrid) {
            heroGrid.innerHTML += card;
        } else if (marqueeGrid) {
            marqueeGrid.innerHTML += card;
        }
    });
    
    if (typeof updateSelectionUI === 'function') updateSelectionUI();
}

// --- MODAL FUNCTIONS ---
function openSliderModal() {
    document.getElementById('slider-modal-title').textContent = 'Add New Slider';
    document.getElementById('slider-form').reset();
    document.getElementById('image-file-input').value = '';
    document.getElementById('slider-id').value = '';
    document.getElementById('slider-image-preview').className = 'mt-2 text-center p-4 border-2 border-dashed border-gray-200 rounded-lg bg-gray-50';
    document.getElementById('slider-modal').classList.remove('hidden');
}

function closeSliderModal() {
    document.getElementById('slider-modal').classList.add('hidden');
}

// Populate modal with existing slider data
function editSlider(id) {
    fetch('../actions/sliders_action.php?action=get_all')
        .then(res => {
            if (!res.ok) throw new Error('Load failed');
            return res.json();
        })
        .then(data => {
            if (data.success) {
                const slider = data.sliders.find(s => s.id == id);  // loose == for safety
                if (slider) {
                    document.getElementById('slider-modal-title').textContent = 'Edit Slider';
                    document.getElementById('slider-id').value = slider.id;
                    document.getElementById('slider-title').value = slider.title || '';
                    document.getElementById('slider-image-url').value = slider.image_url || '';
                    document.getElementById('image-file-input').value = '';
                    document.getElementById('slider-type').value = slider.type || 'hero';
                    document.getElementById('slider-sort-order').value = slider.sort_order || 0;
                    
                    // Preview
                    const preview = document.getElementById('slider-image-preview');
                    const img = document.getElementById('preview-img');
                    img.src = slider.image_url ? '../' + slider.image_url : '';
                    img.alt = slider.image_url || '';
                    preview.classList.remove('text-center', 'p-4', 'border-2', 'border-dashed', 'border-gray-200', 'bg-gray-50');
                    
                    document.getElementById('slider-modal').classList.remove('hidden');
                }
            }
        })
        .catch(err => console.error('Edit slider load failed:', err));
}

// Create or Update slider (handles FormData)
function saveSlider() {
    console.log('=== SAVE SLIDER DEBUG START ===');
    
    const saveBtn = document.getElementById('save-slider-btn');
    const spinner = document.getElementById('save-spinner');
    
    // Loading state
    saveBtn.disabled = true;
    spinner.classList.remove('hidden');
    saveBtn.innerHTML = 'Saving... ' + spinner.outerHTML;
    
    const id = document.getElementById('slider-id').value;
    console.log('Slider ID:', id);
    
    const data = {
        title: document.getElementById('slider-title').value,
        image_url: document.getElementById('slider-image-url').value,
        type: document.getElementById('slider-type').value,
        sort_order: parseInt(document.getElementById('slider-sort-order').value) || 0
    };
    console.log('Form data:', data);
    
    if (!data.image_url) {
        showToast('Please enter an image URL', 'warning');
        resetSaveButton();
        return;
    }
    
    const action = id ? 'update' : 'create';
    const formData = new FormData();
    formData.append('action', action);
    if (id) formData.append('id', id);
    formData.append('title', data.title);
    const fileInput = document.getElementById('image-file-input');
    if (fileInput.files.length > 0) {
        formData.append('image_file', fileInput.files[0]);
    }
    formData.append('image_url', data.image_url);
    formData.append('type', data.type);
    formData.append('sort_order', data.sort_order);
    
    console.log('Sending POST to sliders_action.php...');
    
    fetch('../actions/sliders_action.php', {
        method: 'POST',
        body: formData
    })
    .then(res => {
        console.log('Response status:', res.status);
        console.log('Response URL:', res.url);
        if (!res.ok) throw new Error(`HTTP ${res.status}: ${res.statusText}`);
        return res.json();
    })
    .then(result => {
        console.log('Full response:', result);
        console.log('=== SAVE SLIDER DEBUG END ===');
        
        if (result.success) {
            closeSliderModal();
            loadSliders();
            showToast('✅ ' + (result.message || 'Saved successfully!'), 'success');
        } else {
            showToast('❌ Save failed: ' + (result.message || 'Unknown error'), 'error');
        }
    })
    .catch(err => {
        console.error('SAVE ERROR:', err);
        showToast('🌐 Network/DB Error: ' + err.message + '\\nCheck console & Apache error_log', 'error');
    })
    .finally(() => {
        resetSaveButton();
    });
}

function resetSaveButton() {
    const saveBtn = document.getElementById('save-slider-btn');
    const spinner = document.getElementById('save-spinner');
    saveBtn.disabled = false;
    spinner.classList.add('hidden');
    saveBtn.innerHTML = 'Save<i class="fas fa-spinner fa-spin ml-1 hidden" id="save-spinner"></i>';
}

function toggleSliderStatus(id) {
    showConfirm('Toggle slider status?', () => {
        const formData = new FormData();
        formData.append('action', 'toggle');
        formData.append('id', id);
        
        fetch('../actions/sliders_action.php', {
            method: 'POST',
            body: formData
        })
        .then(res => {
            if (!res.ok) throw new Error(res.status);
            return res.json();
        })
        .then(result => {
            if (result.success) {
                loadSliders();
            } else {
                showToast(result.message || 'Toggle failed', 'error');
            }
        })
        .catch(err => console.error('Toggle failed:', err));
    });
}

function deleteSlider(id) {
    showConfirm('Are you sure you want to delete this slider? This action cannot be undone.', () => {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        
        fetch('../actions/sliders_action.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                loadSliders();
                showToast(result.message, 'success');
            } else {
                showToast(result.message, 'error');
            }
        })
        .catch(err => console.error('Failed to delete slider:', err));
    });
}


</script>
