<!-- ================= CAROUSEL & SLIDER MANAGEMENT ================= -->
<div id="manage-sliders" class="page-section">
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h3 class="text-2xl font-bold text-brand-dark">Carousel & Slider Management</h3>
            <p class="text-gray-500 text-sm">Manage hero carousel and marquee gallery images</p>
        </div>
        <button onclick="openSliderModal()" class="bg-brand-purple hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium transition flex items-center gap-2">
            <i class="fas fa-plus"></i> Add New Slider
        </button>
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

    <!-- Marquee Gallery Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h4 class="text-lg font-bold text-brand-dark mb-4 flex items-center gap-2">
            <i class="fas fa-film text-brand-purple"></i> Marquee Gallery
        </h4>
        <div id="marquee-sliders-grid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <!-- Marquee sliders will be loaded here -->
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
                <p class="text-xs text-gray-500 mt-1">Place images in: assets/image/</p>
                <div id="slider-image-preview" class="mt-2 hidden">
                    <img src="" alt="Preview" class="w-full h-32 object-cover rounded-lg">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select id="slider-type" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-purple/20">
                    <option value="hero">Hero Carousel</option>
                    <option value="marquee">Marquee Gallery</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                <input type="number" id="slider-sort-order" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-purple/20" value="0" min="0">
            </div>
            
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeSliderModal()" class="flex-1 px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-brand-purple hover:bg-purple-700 text-white rounded-lg transition">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Image Browser Modal -->
<div id="image-browser-modal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-[80vh] overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-brand-dark">Select Image</h3>
            <button onclick="closeImageBrowser()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-4">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Or enter image URL:</label>
                <input type="text" id="custom-image-url" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-purple/20" placeholder="assets/image/your-image.jpg">
            </div>
            <div class="border-2 border-dashed border-gray-200 rounded-lg p-8 text-center">
                <i class="fas fa-cloud-upload-alt text-4xl text-gray-300 mb-2"></i>
                <p class="text-gray-500">Place your images in <code class="bg-gray-100 px-2 py-1 rounded">assets/image/</code> folder</p>
                <p class="text-gray-400 text-sm mt-1">Then enter the path above</p>
            </div>
        </div>
        <div class="p-4 border-t border-gray-100 flex justify-end">
            <button onclick="selectCustomImage()" class="px-4 py-2 bg-brand-purple hover:bg-purple-700 text-white rounded-lg transition">Use This URL</button>
        </div>
    </div>
</div>

<script>
let currentImageTarget = '';

// Load sliders on page load
document.addEventListener('DOMContentLoaded', function() {
    loadSliders();
    
    // Setup form submission
    document.getElementById('slider-form').addEventListener('submit', function(e) {
        e.preventDefault();
        saveSlider();
    });
    
    // Image URL preview
    document.getElementById('slider-image-url').addEventListener('input', function() {
        const preview = document.getElementById('slider-image-preview');
        const img = preview.querySelector('img');
        if (this.value) {
            img.src = this.value;
            preview.classList.remove('hidden');
        } else {
            preview.classList.add('hidden');
        }
    });
});

function loadSliders() {
    fetch('actions/sliders_action.php?action=get_all')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderSliders(data.sliders);
            }
        })
        .catch(err => console.error('Failed to load sliders:', err));
}

function renderSliders(sliders) {
    const heroGrid = document.getElementById('hero-sliders-grid');
    const marqueeGrid = document.getElementById('marquee-sliders-grid');
    
    heroGrid.innerHTML = '';
    marqueeGrid.innerHTML = '';
    
    sliders.forEach(slider => {
        const card = `
            <div class="relative group rounded-lg overflow-hidden border ${slider.is_active ? 'border-gray-200' : 'border-red-200'}">
                <img src="${slider.image_url}" alt="${slider.title}" class="w-full h-32 object-cover">
                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2">
                    <button onclick="editSlider(${slider.id})" class="p-2 bg-white rounded-full text-brand-dark hover:text-brand-purple transition" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="toggleSliderStatus(${slider.id})" class="p-2 ${slider.is_active ? 'bg-green-500' : 'bg-gray-500'} rounded-full text-white transition" title="${slider.is_active ? 'Disable' : 'Enable'}">
                        <i class="fas fa-${slider.is_active ? 'eye' : 'eye-slash'}"></i>
                    </button>
                    <button onclick="deleteSlider(${slider.id})" class="p-2 bg-red-500 rounded-full text-white hover:bg-red-600 transition" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-2">
                    <p class="text-white text-sm font-medium truncate">${slider.title || 'Untitled'}</p>
                    <p class="text-white/70 text-xs">Order: ${slider.sort_order}</p>
                </div>
                ${!slider.is_active ? '<div class="absolute inset-0 bg-black/50 flex items-center justify-center"><span class="bg-red-500 text-white text-xs px-2 py-1 rounded">Disabled</span></div>' : ''}
            </div>
        `;
        
        if (slider.type === 'hero') {
            heroGrid.innerHTML += card;
        } else {
            marqueeGrid.innerHTML += card;
        }
    });
}

function openSliderModal() {
    document.getElementById('slider-modal-title').textContent = 'Add New Slider';
    document.getElementById('slider-form').reset();
    document.getElementById('slider-id').value = '';
    document.getElementById('slider-image-preview').classList.add('hidden');
    document.getElementById('slider-modal').classList.remove('hidden');
}

function closeSliderModal() {
    document.getElementById('slider-modal').classList.add('hidden');
}

function editSlider(id) {
    fetch('actions/sliders_action.php?action=get_all')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const slider = data.sliders.find(s => s.id === id);
                if (slider) {
                    document.getElementById('slider-modal-title').textContent = 'Edit Slider';
                    document.getElementById('slider-id').value = slider.id;
                    document.getElementById('slider-title').value = slider.title || '';
                    document.getElementById('slider-image-url').value = slider.image_url || '';
                    document.getElementById('slider-type').value = slider.type || 'hero';
                    document.getElementById('slider-sort-order').value = slider.sort_order || 0;
                    
                    // Show preview
                    const preview = document.getElementById('slider-image-preview');
                    const img = preview.querySelector('img');
                    img.src = slider.image_url;
                    preview.classList.remove('hidden');
                    
                    document.getElementById('slider-modal').classList.remove('hidden');
                }
            }
        });
}

function saveSlider() {
    const id = document.getElementById('slider-id').value;
    const data = {
        title: document.getElementById('slider-title').value,
        image_url: document.getElementById('slider-image-url').value,
        type: document.getElementById('slider-type').value,
        sort_order: parseInt(document.getElementById('slider-sort-order').value) || 0
    };
    
    if (!data.image_url) {
        alert('Please enter an image URL');
        return;
    }
    
    const action = id ? 'update' : 'create';
    const formData = new FormData();
    formData.append('action', action);
    if (id) formData.append('id', id);
    formData.append('title', data.title);
    formData.append('image_url', data.image_url);
    formData.append('type', data.type);
    formData.append('sort_order', data.sort_order);
    
    fetch('actions/sliders_action.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            closeSliderModal();
            loadSliders();
            alert(result.message);
        } else {
            alert(result.message);
        }
    })
    .catch(err => console.error('Failed to save slider:', err));
}

function toggleSliderStatus(id) {
    if (!confirm('Are you sure you want to toggle this slider status?')) return;
    
    const formData = new FormData();
    formData.append('action', 'toggle');
    formData.append('id', id);
    
    fetch('actions/sliders_action.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            loadSliders();
        } else {
            alert(result.message);
        }
    })
    .catch(err => console.error('Failed to toggle slider:', err));
}

function deleteSlider(id) {
    if (!confirm('Are you sure you want to delete this slider? This action cannot be undone.')) return;
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);
    
    fetch('actions/sliders_action.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            loadSliders();
            alert(result.message);
        } else {
            alert(result.message);
        }
    })
    .catch(err => console.error('Failed to delete slider:', err));
}

function browseImage(targetId) {
    currentImageTarget = targetId;
    document.getElementById('image-browser-modal').classList.remove('hidden');
}

function closeImageBrowser() {
    document.getElementById('image-browser-modal').classList.add('hidden');
}

function selectCustomImage() {
    const url = document.getElementById('custom-image-url').value;
    if (url && currentImageTarget) {
        document.getElementById(currentImageTarget).value = url;
        // Trigger input event for preview
        document.getElementById(currentImageTarget).dispatchEvent(new Event('input'));
    }
    closeImageBrowser();
}
</script>
