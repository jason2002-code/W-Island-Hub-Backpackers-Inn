/**
 * ROOMS & AVAILABILITY MODULE
 * Handles fetching rooms from backend API, rendering room catalog cards,
 * date picker constraints (no past dates, check-out after check-in), guest selector constraints,
 * homepage search bar redirection, and real-time room availability filtering.
 */

/**
 * Returns a date formatted as a local YYYY-MM-DD string.
 * Prevents UTC timezone discrepancies when computing date constraints.
 * 
 * @param {Date} [d=new Date()] - The date object to format.
 * @returns {string} Formatted date string (YYYY-MM-DD).
 */
function getLocalDateString(d) {
    const date = d || new Date();
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

/**
 * Toggles the guest dropdown popover open/closed for a given scope ('home' or 'rooms').
 * @param {'home'|'rooms'} scope
 */
function toggleGuestDropdown(scope) {
    const dropdown = document.getElementById(`${scope}-guest-dropdown`);
    const chevron = document.getElementById(`${scope}-guest-chevron`);
    if (!dropdown) return;

    // Close the other scope if open
    const otherScope = scope === 'home' ? 'rooms' : 'home';
    closeGuestDropdown(otherScope);

    const isHidden = dropdown.classList.contains('hidden');
    if (isHidden) {
        dropdown.classList.remove('hidden');
        if (chevron) chevron.classList.add('rotate-180');
    } else {
        dropdown.classList.add('hidden');
        if (chevron) chevron.classList.remove('rotate-180');
    }
}

/**
 * Closes the guest dropdown popover for a given scope ('home' or 'rooms').
 * @param {'home'|'rooms'} scope
 */
let currentChildAges = [];

/**
 * Retrieves the currently selected child ages from state or hidden inputs.
 * @returns {Array<number|string>}
 */
function getChildAges() {
    if (!currentChildAges || !Array.isArray(currentChildAges) || currentChildAges.length === 0) {
        try {
            const raw = document.getElementById('children-ages')?.value || document.getElementById('home-children-ages')?.value || '[]';
            const parsed = JSON.parse(raw);
            if (Array.isArray(parsed) && parsed.length > 0) {
                currentChildAges = parsed;
            }
        } catch (e) {
            currentChildAges = [];
        }
    }
    return currentChildAges;
}

/**
 * Synchronizes child ages count with the stepper value.
 * Expands or truncates the array as children are added or removed.
 * @param {number} targetCount
 */
function adjustChildAgesCount(targetCount) {
    const ages = getChildAges();
    while (ages.length < targetCount) {
        ages.push(''); // new child requires age selection
    }
    if (ages.length > targetCount) {
        ages.splice(targetCount);
    }
    currentChildAges = ages;
    syncChildAgesInputs();
}

/**
 * Persists current child ages JSON string into hidden inputs across both scopes.
 */
function syncChildAgesInputs() {
    const jsonStr = JSON.stringify(currentChildAges || []);
    const roomsInput = document.getElementById('children-ages');
    const homeInput = document.getElementById('home-children-ages');
    if (roomsInput) roomsInput.value = jsonStr;
    if (homeInput) homeInput.value = jsonStr;
}

/**
 * Generates options HTML for the child age dropdown (0 to 17 years old).
 * @param {number|string} selectedVal
 * @returns {string}
 */
function getChildAgeOptionsHtml(selectedVal) {
    const isValSet = (selectedVal !== '' && selectedVal !== undefined && selectedVal !== null);
    let html = `<option value="" disabled ${!isValSet ? 'selected' : ''}>Select age (0 to 17)</option>`;
    html += `<option value="0" ${(isValSet && parseInt(selectedVal, 10) === 0) ? 'selected' : ''}>0 years old (Infant / under 1)</option>`;
    for (let age = 1; age <= 17; age++) {
        const isSel = (isValSet && parseInt(selectedVal, 10) === age);
        html += `<option value="${age}" ${isSel ? 'selected' : ''}>${age} year${age > 1 ? 's' : ''} old</option>`;
    }
    return html;
}

/**
 * Renders dynamic child age dropdown selectors for a specific scope.
 * @param {'home'|'rooms'} scope
 * @param {number} childrenCount
 */
function renderChildAgesUI(scope, childrenCount) {
    const container = document.getElementById(`${scope}-child-ages-container`);
    const list = document.getElementById(`${scope}-child-ages-list`);
    if (!container || !list) return;

    if (childrenCount <= 0) {
        container.classList.add('hidden');
        list.innerHTML = '';
        return;
    }

    container.classList.remove('hidden');

    const ages = getChildAges();
    const isHome = scope === 'home';
    const focusRing = isHome ? 'focus:border-amber-500 focus:ring-amber-500/20' : 'focus:border-brand-blue focus:ring-brand-blue/20';
    const activeBorder = isHome ? 'hover:border-amber-300' : 'hover:border-sky-300';
    const labelColor = isHome ? 'text-slate-700' : 'text-gray-700';

    let html = '';
    for (let i = 0; i < childrenCount; i++) {
        const val = ages[i];
        const isSelected = (val !== '' && val !== undefined && val !== null);
        const colSpan = (childrenCount === 1) ? 'col-span-2' : 'col-span-1';

        let badgeHtml = '';
        if (isSelected) {
            const ageNum = parseInt(val, 10);
            const badgeText = ageNum === 0 ? 'Infant (0)' : `${ageNum} yr${ageNum > 1 ? 's' : ''}`;
            const badgeClass = isHome ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-blue-100 text-brand-blue border-blue-200';
            badgeHtml = `<span id="${scope}-child-age-badge-${i}" class="text-[10px] font-bold px-1.5 py-0.5 rounded ${badgeClass} border">${badgeText}</span>`;
        } else {
            badgeHtml = `<span id="${scope}-child-age-badge-${i}" class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-rose-50 text-rose-600 border border-rose-200">Age needed</span>`;
        }

        html += `
            <div class="${colSpan} bg-slate-50/80 rounded-xl p-2.5 border border-slate-200/90 transition hover:bg-white hover:border-slate-300 shadow-2xs">
                <div class="flex items-center justify-between mb-1.5">
                    <label for="${scope}-child-age-${i}" class="text-[11px] font-bold ${labelColor} flex items-center gap-1">
                        <i class="fas fa-child ${isHome ? 'text-amber-500' : 'text-brand-blue'}"></i>
                        <span>Child ${i + 1}</span>
                    </label>
                    ${badgeHtml}
                </div>
                <div class="relative">
                    <select id="${scope}-child-age-${i}"
                            onchange="updateChildAge(${i}, this.value, '${scope}')"
                            class="w-full appearance-none bg-white border ${isSelected ? 'border-slate-200' : 'border-rose-300 bg-rose-50/20'} ${activeBorder} ${focusRing} focus:ring-2 rounded-lg px-2.5 py-1.5 pr-7 text-xs font-semibold text-slate-800 transition outline-none cursor-pointer shadow-2xs">
                        ${getChildAgeOptionsHtml(val)}
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                        <i class="fas fa-chevron-down text-[9px]"></i>
                    </div>
                </div>
            </div>
        `;
    }
    list.innerHTML = html;
}

/**
 * Handles age changes on child dropdown selects and synchronizes across scopes.
 * @param {number} index - Index of child (0-based)
 * @param {string|number} ageValue - Selected age (0-17)
 * @param {'home'|'rooms'} sourceScope - Source triggering the update
 */
function updateChildAge(index, ageValue, sourceScope) {
    const age = (ageValue === '' || ageValue === null || ageValue === undefined) ? '' : parseInt(ageValue, 10);
    const ages = getChildAges();
    ages[index] = age;
    currentChildAges = ages;
    syncChildAgesInputs();

    // Synchronize the other scope's dropdown element if rendered
    const otherScope = sourceScope === 'home' ? 'rooms' : 'home';
    const otherSelect = document.getElementById(`${otherScope}-child-age-${index}`);
    if (otherSelect && otherSelect.value !== String(ageValue)) {
        otherSelect.value = String(ageValue);
    }

    // Refresh badges and select border states in both scopes
    ['home', 'rooms'].forEach(s => {
        const select = document.getElementById(`${s}-child-age-${index}`);
        const badge = document.getElementById(`${s}-child-age-badge-${index}`);
        const isH = s === 'home';
        if (select) {
            if (age !== '') {
                select.classList.remove('border-rose-300', 'bg-rose-50/20');
                select.classList.add('border-slate-200');
            } else {
                select.classList.add('border-rose-300', 'bg-rose-50/20');
                select.classList.remove('border-slate-200');
            }
        }
        if (badge) {
            if (age !== '') {
                const ageNum = parseInt(age, 10);
                const badgeText = ageNum === 0 ? 'Infant (0)' : `${ageNum} yr${ageNum > 1 ? 's' : ''}`;
                badge.className = `text-[10px] font-bold px-1.5 py-0.5 rounded ${isH ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-blue-100 text-brand-blue border-blue-200'} border`;
                badge.textContent = badgeText;
            } else {
                badge.className = 'text-[10px] font-semibold px-1.5 py-0.5 rounded bg-rose-50 text-rose-600 border border-rose-200';
                badge.textContent = 'Age needed';
            }
        }
    });

    // Update guest summary texts for both scopes
    updateGuestSummaryText('home');
    updateGuestSummaryText('rooms');
}

/**
 * Updates guest summary text label and total badge.
 * @param {'home'|'rooms'} scope
 */
function updateGuestSummaryText(scope) {
    const prefix = scope === 'home' ? 'home-' : '';
    const adultsInput = document.getElementById(prefix ? `${prefix}adults-count` : 'adults-count');
    const childrenInput = document.getElementById(prefix ? `${prefix}children-count` : 'children-count');
    const summaryText = document.getElementById(`${scope}-guest-summary-text`);
    const totalBadge = document.getElementById(`${scope}-total-guest-badge`);

    const adults = parseInt(adultsInput?.value || '1', 10) || 1;
    const children = parseInt(childrenInput?.value || '0', 10) || 0;
    const totalGuests = adults + children;

    const ages = getChildAges().slice(0, children);
    const validAges = ages.filter(a => a !== '' && a !== undefined && a !== null);

    let summaryStr = `${totalGuests} ${totalGuests === 1 ? 'Guest' : 'Guests'} (${adults} Adult${adults > 1 ? 's' : ''}`;
    if (children > 0) {
        summaryStr += `, ${children} Child${children > 1 ? 'ren' : ''}`;
        if (validAges.length === children && children > 0) {
            summaryStr += `: ${validAges.map(a => a === 0 ? '0' : a).join(', ')}y`;
        }
    }
    summaryStr += ')';

    if (summaryText) summaryText.textContent = summaryStr;
    if (totalBadge) totalBadge.textContent = `${totalGuests} ${totalGuests === 1 ? 'Guest' : 'Guests'}`;
}

/**
 * Validates whether all children have had their ages selected.
 * @param {'home'|'rooms'} scope
 * @returns {boolean}
 */
function validateChildAges(scope) {
    const prefix = scope === 'home' ? 'home-' : '';
    const children = parseInt(document.getElementById(prefix ? `${prefix}children-count` : 'children-count')?.value || '0', 10);
    if (children <= 0) return true;

    const ages = getChildAges().slice(0, children);
    const unselectedIdx = ages.findIndex(a => a === '' || a === undefined || a === null);
    if (unselectedIdx !== -1) {
        if (typeof window.showToast === 'function') {
            window.showToast(`Please select the age for Child ${unselectedIdx + 1} at check-in.`, 'warning');
        }
        const dropdown = document.getElementById(`${scope}-guest-dropdown`);
        if (dropdown && dropdown.classList.contains('hidden')) {
            toggleGuestDropdown(scope);
        }
        const select = document.getElementById(`${scope}-child-age-${unselectedIdx}`);
        if (select) {
            if (typeof select.focus === 'function') select.focus();
            select.classList.add('ring-2', 'ring-rose-400');
        }
        return false;
    }
    return true;
}

/**
 * Closes the guest dropdown popover for a given scope ('home' or 'rooms').
 * Validates that all children have ages selected if user clicks Done.
 * @param {'home'|'rooms'} scope
 */
function closeGuestDropdown(scope) {
    const prefix = scope === 'home' ? 'home-' : '';
    const children = parseInt(document.getElementById(prefix ? `${prefix}children-count` : 'children-count')?.value || '0', 10);
    if (children > 0) {
        const ages = getChildAges().slice(0, children);
        const unselectedIdx = ages.findIndex(a => a === '' || a === undefined || a === null);
        if (unselectedIdx !== -1) {
            if (typeof window.showToast === 'function') {
                window.showToast(`Please select the age for Child ${unselectedIdx + 1}.`, 'info');
            }
            const select = document.getElementById(`${scope}-child-age-${unselectedIdx}`);
            if (select) {
                if (typeof select.focus === 'function') select.focus();
                select.classList.add('ring-2', 'ring-rose-400');
            }
            return;
        }
    }

    const dropdown = document.getElementById(`${scope}-guest-dropdown`);
    const chevron = document.getElementById(`${scope}-guest-chevron`);
    if (dropdown) dropdown.classList.add('hidden');
    if (chevron) chevron.classList.remove('rotate-180');
}

/**
 * Updates adult or child count via stepper buttons and synchronizes values and summaries.
 * @param {'home'|'rooms'} scope
 * @param {'adults'|'children'} type
 * @param {number} delta - (+1 or -1)
 */
function updateGuestStepper(scope, type, delta) {
    const prefix = scope === 'home' ? 'home-' : '';
    const otherScope = scope === 'home' ? 'rooms' : 'home';

    let adultsInput = document.getElementById(prefix ? `${prefix}adults-count` : 'adults-count');
    let childrenInput = document.getElementById(prefix ? `${prefix}children-count` : 'children-count');

    let adults = parseInt(adultsInput?.value || '1', 10);
    let children = parseInt(childrenInput?.value || '0', 10);

    if (isNaN(adults) || adults < 1) adults = 1;
    if (isNaN(children) || children < 0) children = 0;

    if (type === 'adults') {
        adults = Math.max(1, adults + delta);
    } else if (type === 'children') {
        children = Math.max(0, children + delta);
    }

    const totalGuests = adults + children;

    // Apply to current scope
    applyGuestValuesToScope(scope, adults, children, totalGuests);
    // Sync to other scope so search states stay identical
    applyGuestValuesToScope(otherScope, adults, children, totalGuests);
}

/**
 * Computes total active room capacity across the inn.
 * @returns {number} Total max guest capacity of all non-maintenance rooms.
 */
function getTotalHotelCapacity() {
    let totalCap = 0;
    const rooms = (window.appState && Array.isArray(window.appState.rooms) && window.appState.rooms.length > 0)
        ? window.appState.rooms
        : (window.serverRoomsData ? Object.values(window.serverRoomsData) : []);

    rooms.forEach(r => {
        if (r.status !== 'Maintenance') {
            const cap = parseInt(r.max_capacity || r.capacity, 10) || 2;
            totalCap += cap;
        }
    });

    return totalCap > 0 ? totalCap : 17;
}

/**
 * Applies adult, child, and total guest values to UI elements for a scope.
 */
function applyGuestValuesToScope(scope, adults, children, totalGuests) {
    const prefix = scope === 'home' ? 'home-' : '';

    const adultsInput = document.getElementById(prefix ? `${prefix}adults-count` : 'adults-count');
    const childrenInput = document.getElementById(prefix ? `${prefix}children-count` : 'children-count');
    const guestInput = document.getElementById(prefix ? `${prefix}guest-count` : 'guest-count');

    if (adultsInput) adultsInput.value = adults;
    if (childrenInput) childrenInput.value = children;
    if (guestInput) guestInput.value = totalGuests;

    // Stepper displays
    const adultsDisplay = document.getElementById(`${scope}-adults-display`);
    const childrenDisplay = document.getElementById(`${scope}-children-display`);
    if (adultsDisplay) adultsDisplay.textContent = adults;
    if (childrenDisplay) childrenDisplay.textContent = children;

    // Minus button states
    const adultsMinus = document.getElementById(`${scope}-adults-minus`);
    const childrenMinus = document.getElementById(`${scope}-children-minus`);
    if (adultsMinus) adultsMinus.disabled = adults <= 1;
    if (childrenMinus) childrenMinus.disabled = children <= 0;

    // Synchronize child ages count
    adjustChildAgesCount(children);

    // Render dynamic child age dropdowns for this scope
    renderChildAgesUI(scope, children);

    // Update summary text
    updateGuestSummaryText(scope);

    // Live capacity warning inside dropdown stepper
    const hotelCap = getTotalHotelCapacity();
    const isExceeded = totalGuests > hotelCap;
    const stepperWarn = document.getElementById(`${scope}-stepper-capacity-warning`);
    const stepperWarnText = document.getElementById(`${scope}-stepper-capacity-warning-text`);
    if (stepperWarn) {
        if (isExceeded) {
            stepperWarn.classList.remove('hidden');
            if (stepperWarnText) {
                stepperWarnText.textContent = `The hotel does not accommodate ${totalGuests} guests as total room capacity is limited to ${hotelCap} guests.`;
            }
        } else {
            stepperWarn.classList.add('hidden');
        }
    }
}

function syncGuestSelectorLimits() {
    // Dynamic warning handled live via applyGuestValuesToScope and getTotalHotelCapacity
}

/**
 * Fetches active room catalog from backend database and updates state.
 * Re-renders rooms upon successful retrieval.
 */
function fetchRooms() {
    fetch('actions/rooms_action.php')
        .then(res => res.json())
        .then(data => {
            if (data && data.success && Array.isArray(data.rooms) && data.rooms.length > 0) {
                if (!window.appState) window.appState = {};
                window.appState.rooms = data.rooms.map(room => ({
                    id: room.id,
                    name: room.name,
                    type: room.type,
                    price: parseFloat(room.price) || 0,
                    capacity: parseInt(room.capacity) !== undefined ? parseInt(room.capacity) : 1,
                    max_capacity: parseInt(room.max_capacity) || parseInt(room.capacity) || 1,
                    image: room.image || 'assets/image/background.jpg',
                    desc: room.description || room.desc || '',
                    amenities: (() => {
                        if (typeof room.amenities === 'string') {
                            try {
                                return JSON.parse(room.amenities || '[]');
                            } catch(e) {
                                return room.amenities.split(',').map(s => s.trim());
                            }
                        }
                        return room.amenities || [];
                    })(),
                    rating: parseFloat(room.rating) || 5.0,
                    status: room.status || 'Available',
                    remaining_beds: room.remaining_beds !== undefined ? parseInt(room.remaining_beds) : undefined,
                    occupied_beds: room.occupied_beds !== undefined ? parseInt(room.occupied_beds) : undefined
                }));
                
                syncGuestSelectorLimits();
                renderRooms();
            }
        })
        .catch(err => console.error('Failed to load rooms:', err));
}

/**
 * Renders the room catalog cards dynamically into `#rooms-container`.
 * Includes room pricing, amenities badges, guest capacity, reviews accordion, and booking CTA.
 * 
 * @param {string|null} [filterDate=null] - Optional date filter for availability.
 */
function renderRooms(filterDate = null) {
    const container = document.getElementById('rooms-container');
    if (!container) return;
    
    const isLoggedIn = window.appState && window.appState.currentUser !== null;
    const roomsList = (window.appState && Array.isArray(window.appState.rooms)) ? window.appState.rooms : [];
    
    container.innerHTML = roomsList.map(room => {
        const stars = Array(Math.round(room.rating)).fill('<i class="fas fa-star text-yellow-400 text-xs"></i>').join('');
        
        let statusBadge = '';
        if (room.status === 'Maintenance') {
            statusBadge = `<span class="room-status-badge bg-red-100 text-red-800 text-xs font-bold px-2 py-1 rounded uppercase ml-2">Maintenance</span>`;
        }
        
        const isDorm = (parseInt(room.id) === 5 || (room.type && room.type.toLowerCase().includes('dorm')));
        const dormTotalPrice = 3680.00;
        const dormBedPrice = 460.00;
        const formattedPrice = isDorm ? dormTotalPrice.toFixed(2) : (room.price.toFixed ? room.price.toFixed(2) : parseFloat(room.price).toFixed(2));
        const roomMaxCap = isDorm ? 8 : (room.max_capacity || room.capacity || 2);
        return `
        <div class="room-card bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-slate-100 flex flex-col" data-room-id="${room.id}" data-capacity="${room.capacity}" data-max-capacity="${roomMaxCap}" data-is-dorm="${isDorm ? '1' : '0'}">
            <div class="h-48 overflow-hidden relative cursor-pointer group" onclick="openRoomInfoModal(${room.id})" title="Click to view full room information">
                 <img src="${room.image}" alt="${room.name}" class="w-full h-full object-cover transition duration-500 group-hover:scale-110">
                 <div class="absolute inset-0 bg-slate-950/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                    <span class="bg-white/95 backdrop-blur-md text-slate-900 text-xs font-bold px-3.5 py-1.5 rounded-full shadow-md flex items-center gap-1.5 transform translate-y-2 group-hover:translate-y-0 transition-transform">
                        <i class="fas fa-eye text-brand-blue"></i> View Details
                    </span>
                 </div>
                 <div class="absolute top-3 right-3 bg-white/95 backdrop-blur-md px-3 py-1.5 rounded-full text-xs font-bold text-slate-900 shadow-md flex items-center gap-1.5 border border-white/60">
                    <i class="fas fa-star text-amber-400 text-xs"></i>
                    <span class="rating-badge-value" data-room-id="${room.id}">${room.rating.toFixed(1)}</span>
                    <span class="text-slate-400 text-[10px] font-medium">/ 5</span>
                 </div>
            </div>
            <div class="p-6 flex-1 flex flex-col">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-xl font-bold text-brand-dark hover:text-brand-blue cursor-pointer transition" onclick="openRoomInfoModal(${room.id})" title="Click to view room details">${room.name}</h3>
                    <div class="flex items-center text-right justify-end flex-wrap gap-1">
                        <span class="bg-blue-50 text-brand-blue text-xs font-bold px-2 py-1 rounded uppercase">${room.type}</span>
                        ${statusBadge}
                    </div>
                </div>
                <p class="text-gray-500 text-sm mb-4 line-clamp-2">${room.desc}</p>
                
                <div class="flex flex-wrap gap-2 mb-4">
                    ${isDorm ? `
                        <span class="text-xs text-brand-blue bg-blue-50 px-2.5 py-1 rounded font-semibold flex items-center gap-1.5 room-capacity-pill" data-room-id="${room.id}">
                            <i class="fas fa-bed text-[11px] text-brand-blue"></i>
                            <span class="room-beds-text"><strong class="room-avail-beds">${(room.remaining_beds !== undefined ? room.remaining_beds : (room.capacity || 8))}</strong> Beds Available</span>
                        </span>
                    ` : `
                        <span class="text-xs text-brand-blue bg-blue-50 px-2 py-1 rounded font-semibold flex items-center gap-1"><i class="fas fa-user-friends text-[10px]"></i> Up to ${roomMaxCap} Guest${roomMaxCap > 1 ? 's' : ''}</span>
                    `}
                    ${room.amenities.slice(0,3).map(a => `<button type="button" onclick="openRoomInfoModal(${room.id}, 'amenities')" class="text-xs text-gray-600 bg-gray-100 hover:bg-amber-50 hover:text-amber-700 hover:border-amber-200 border border-transparent px-2 py-1 rounded transition-colors cursor-pointer" title="View all amenities">${a}</button>`).join('')}
                    ${room.amenities.length > 3 ? `<button type="button" onclick="openRoomInfoModal(${room.id}, 'amenities')" class="text-xs text-brand-blue bg-blue-50 hover:bg-blue-100 border border-blue-100 px-2 py-1 rounded font-semibold transition-colors cursor-pointer" title="View all amenities">+${room.amenities.length - 3} more</button>` : ''}
                </div>
                
                <!-- Reviews Quick Action Button (Opens Horizontal Reviews in Modal) -->
                <div class="mt-3">
                    <button type="button" onclick="openRoomInfoModal(${room.id}, 'reviews')" class="w-full text-left py-2 px-3.5 bg-slate-50 hover:bg-amber-50/70 rounded-xl flex items-center justify-between transition-colors border border-slate-200/60 group/rev cursor-pointer" title="View all guest reviews">
                        <div class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                            <i class="fas fa-star text-amber-400"></i>
                            <span>Guest Reviews</span>
                            <span class="reviews-count text-[11px] font-bold bg-white border border-slate-200 text-slate-600 px-2 py-0.5 rounded-full" data-room-id="${room.id}">(${room.reviews_count || 0})</span>
                        </div>
                        <span class="text-xs font-semibold text-brand-blue group-hover/rev:text-amber-600 flex items-center gap-1">
                            Read Reviews <i class="fas fa-arrow-right text-[10px] group-hover/rev:translate-x-0.5 transition-transform"></i>
                        </span>
                    </button>
                </div>

                <div class="mt-auto pt-4 border-t flex justify-between items-center gap-2">
                    ${isDorm ? `
                        <div>
                            <div class="flex items-baseline gap-1">
                                <span class="text-2xl font-bold text-brand-blue">₱${dormTotalPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                                <span class="text-gray-400 text-xs">/ night (8 Beds)</span>
                            </div>
                            <div class="text-xs font-semibold text-emerald-600 flex items-center gap-1 mt-0.5">
                                <i class="fas fa-bed text-[10px]"></i> ₱460.00 / bed / night
                            </div>
                        </div>
                    ` : `
                        <div>
                            <span class="text-2xl font-bold text-brand-blue">₱${formattedPrice}</span>
                            <span class="text-gray-400 text-sm">/ night</span>
                        </div>
                    `}
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="openRoomInfoModal(${room.id})" class="bg-slate-100 hover:bg-slate-200 text-slate-700 hover:text-slate-900 px-3 py-2 rounded-lg font-semibold transition text-sm flex items-center gap-1.5 border border-slate-200 shadow-sm" title="View Room Details">
                            <i class="fas fa-info-circle text-brand-blue text-xs"></i>
                            <span>Details</span>
                        </button>
                        <button onclick="initiateBooking(${room.id})" class="bg-brand-dark hover:bg-gray-800 text-white px-4 py-2 rounded-lg font-medium transition text-sm ${room.status === 'Maintenance' ? 'opacity-50 cursor-not-allowed' : ''}" ${room.status === 'Maintenance' ? 'disabled' : ''}>
                            ${room.status === 'Maintenance' ? 'Maintenance' : 'Book Now'}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        `;
    }).join('');

    // Initialize reviews for each room card
    roomsList.forEach(room => {
        if (typeof window.initReviewsSection === 'function') {
            window.initReviewsSection(room.id);
        }
    });
}

/**
 * Initializes and synchronizes Check-In & Check-Out date pickers.
 * Enforces today as minimum check-in date and ensures check-out is strictly after check-in.
 */
function initDateConstraints() {
    const pairs = [
        { in: document.getElementById('check-in-date'), out: document.getElementById('check-out-date') },
        { in: document.getElementById('home-check-in-date'), out: document.getElementById('home-check-out-date') }
    ];

    const todayStr = getLocalDateString(new Date());

    pairs.forEach(pair => {
        const checkInInput = pair.in;
        const checkOutInput = pair.out;
        if (!checkInInput || !checkOutInput) return;

        checkInInput.min = todayStr;
        if (!checkInInput.value || checkInInput.value < todayStr) {
            checkInInput.value = todayStr;
        }

        function syncCheckOut() {
            const currentCheckIn = checkInInput.value;
            if (!currentCheckIn || currentCheckIn < todayStr) {
                checkInInput.value = todayStr;
            }

            const cinDate = new Date(checkInInput.value + 'T00:00:00');
            const minCheckOutDate = new Date(cinDate);
            minCheckOutDate.setDate(minCheckOutDate.getDate() + 1);
            const minCheckOutStr = getLocalDateString(minCheckOutDate);

            checkOutInput.min = minCheckOutStr;

            if (!checkOutInput.value || checkOutInput.value <= checkInInput.value) {
                checkOutInput.value = minCheckOutStr;
            }
        }

        syncCheckOut();

        checkInInput.onchange = () => {
            if (checkInInput.value < todayStr) {
                window.showToast('Check-in date cannot be in the past or outdated.', 'warning');
                checkInInput.value = todayStr;
            }
            syncCheckOut();
        };

        checkOutInput.onchange = () => {
            const cinVal = checkInInput.value || todayStr;
            if (checkOutInput.value <= cinVal) {
                window.showToast('Check-out date must be strictly after check-in date.', 'warning');
                const cinDate = new Date(cinVal + 'T00:00:00');
                cinDate.setDate(cinDate.getDate() + 1);
                checkOutInput.value = getLocalDateString(cinDate);
            }
        };
    });
}

/**
 * Initializes outside-click listener for the unified guest selectors
 * and prepares initial child age selectors if children exist.
 */
function initGuestSelector() {
    document.addEventListener('click', (e) => {
        ['home', 'rooms'].forEach(scope => {
            const container = document.getElementById(scope === 'home' ? 'home-guest-box-container' : 'rooms-guest-box-container');
            const dropdown = document.getElementById(`${scope}-guest-dropdown`);
            const chevron = document.getElementById(`${scope}-guest-chevron`);
            if (container && dropdown && !dropdown.classList.contains('hidden')) {
                if (!container.contains(e.target)) {
                    dropdown.classList.add('hidden');
                    if (chevron) chevron.classList.remove('rotate-180');
                }
            }
        });
    });

    // Check initial child counts on page load and initialize dynamic child age selectors
    ['home', 'rooms'].forEach(scope => {
        const prefix = scope === 'home' ? 'home-' : '';
        const children = parseInt(document.getElementById(prefix ? `${prefix}children-count` : 'children-count')?.value || '0', 10);
        if (children > 0) {
            adjustChildAgesCount(children);
            renderChildAgesUI(scope, children);
            updateGuestSummaryText(scope);
        }
    });
}

/**
 * Transfers search inputs from the Homepage Hero Bar to the Accommodations catalog
 * and automatically triggers room availability verification.
 * 
 * @param {Event} [e] - Form submission or button click event.
 */
function searchFromHome(e) {
    if (e && e.preventDefault) e.preventDefault();
    
    // Validate child ages before redirecting
    if (!validateChildAges('home')) {
        return;
    }

    const homeCheckIn = document.getElementById('home-check-in-date')?.value;
    const homeCheckOut = document.getElementById('home-check-out-date')?.value;
    const homeAdults = parseInt(document.getElementById('home-adults-count')?.value || '1', 10) || 1;
    const homeChildren = parseInt(document.getElementById('home-children-count')?.value || '0', 10) || 0;
    const homeGuests = homeAdults + homeChildren;

    if (homeCheckIn && document.getElementById('check-in-date')) {
        document.getElementById('check-in-date').value = homeCheckIn;
    }
    if (homeCheckOut && document.getElementById('check-out-date')) {
        document.getElementById('check-out-date').value = homeCheckOut;
    }
    
    // Sync guest counters and child ages into rooms scope
    applyGuestValuesToScope('rooms', homeAdults, homeChildren, homeGuests);

    window.router('rooms');

    const form = document.getElementById('check-availability-form');
    if (form) {
        form.dispatchEvent(new Event('submit'));
    }
}

/**
 * Binds the real-time availability filter form handler.
 */
function initAvailabilityForm() {
    const availabilityForm = document.getElementById('check-availability-form');
    if (!availabilityForm || availabilityForm.dataset.initialized) return;
    availabilityForm.dataset.initialized = 'true';

    availabilityForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Validate child ages
        if (!validateChildAges('rooms')) {
            return;
        }

        const todayStr = getLocalDateString(new Date());
        let checkIn = document.getElementById('check-in-date').value;
        let checkOut = document.getElementById('check-out-date').value;
        const guestCountEl = document.getElementById('guest-count');
        let guests = parseInt(guestCountEl ? guestCountEl.value : 1, 10) || 1;
        if (guests < 1) {
            guests = 1;
            if (guestCountEl) guestCountEl.value = 1;
        }

        if (!checkIn || checkIn < todayStr) {
            window.showToast('Check-in date cannot be in the past or outdated.', 'warning');
            document.getElementById('check-in-date').value = todayStr;
            checkIn = todayStr;
            return;
        }
        
        if (!checkOut || checkOut <= checkIn) {
            window.showToast('Check-out date must be strictly after check-in date.', 'warning');
            const cinDate = new Date(checkIn + 'T00:00:00');
            cinDate.setDate(cinDate.getDate() + 1);
            document.getElementById('check-out-date').value = getLocalDateString(cinDate);
            return;
        }

        const submitBtn = availabilityForm.querySelector('button[type="submit"]');
        const origBtnHtml = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Checking Availability...';
        }

        try {
            const roomsContainer = document.getElementById('rooms-container');
            const capBanner = document.getElementById('capacity-warning-banner');
            const capBannerText = document.getElementById('capacity-warning-text');
            let allRoomEls = Array.from(document.querySelectorAll('.room-card[data-room-id]'));

            // Fallback if data-room-id or .room-card class is missing in PHP-rendered rooms
            if (allRoomEls.length === 0 && roomsContainer) {
                const buttons = roomsContainer.querySelectorAll('button[onclick^="initiateBooking"]');
                buttons.forEach(btn => {
                    const match = btn.getAttribute('onclick').match(/initiateBooking\((\d+)\)/);
                    if (match && match[1]) {
                        const card = btn.closest('.room-card') || btn.closest('[class*="col-"]') || btn.parentElement.parentElement;
                        if (card) {
                            card.dataset.roomId = match[1];
                            if (!allRoomEls.includes(card)) {
                                allRoomEls.push(card);
                            }
                        }
                    }
                });
            }

            // 1. Check total hotel capacity and available capacity for requested dates and guests
            let capData = null;
            try {
                const capRes = await fetch('actions/book_action.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        action: 'check_hotel_capacity',
                        check_in: checkIn,
                        check_out: checkOut,
                        guests: guests
                    })
                });
                capData = await capRes.json();
            } catch (capErr) {
                console.warn('Hotel capacity check error:', capErr);
            }

            // 2. Overcapacity Handling: Guest number exceeds total available room capacity
            if (capData && capData.success && !capData.can_accommodate) {
                if (capBanner) {
                    capBanner.classList.add('hidden');
                }

                // Filter all rooms out
                allRoomEls.forEach(el => {
                    el.style.display = 'none';
                });

                let noAvailEl = document.getElementById('no-available-rooms');
                if (!noAvailEl && roomsContainer) {
                    noAvailEl = document.createElement('div');
                    noAvailEl.id = 'no-available-rooms';
                    noAvailEl.className = 'col-span-full text-center text-gray-500 py-6';
                    roomsContainer.appendChild(noAvailEl);
                }
                if (noAvailEl) {
                    if (capData.total_available_capacity <= 0) {
                        noAvailEl.innerHTML = `
                            <div class="max-w-xl mx-auto my-6 text-center">
                                <div class="bg-slate-50 border border-slate-200 rounded-3xl p-8 shadow-sm">
                                    <div class="w-14 h-14 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-2xl mx-auto mb-4">
                                        <i class="fas fa-calendar-times"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-slate-800 mb-2">No Rooms Available</h4>
                                    <p class="text-sm text-slate-600 mb-4 leading-relaxed font-medium">
                                        <strong>Reminder:</strong> All rooms are currently booked for your selected dates from <strong class="text-slate-800">${checkIn}</strong> to <strong class="text-slate-800">${checkOut}</strong>.
                                    </p>
                                    <p class="text-xs text-brand-blue font-semibold">Please try choosing different check-in / check-out dates above.</p>
                                </div>
                            </div>
                        `;
                    } else {
                        noAvailEl.innerHTML = `
                            <div class="max-w-xl mx-auto my-6 text-center">
                                <div class="bg-amber-50/95 border-2 border-amber-300 rounded-3xl p-8 shadow-sm">
                                    <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-2xl mx-auto mb-4 shadow-xs">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-amber-950 mb-2">Guest Limit Exceeded</h4>
                                    <p class="text-sm text-amber-900 mb-4 leading-relaxed font-medium">
                                        <strong>Reminder:</strong> Your requested party of <strong>${guests} guests</strong> exceeds the total capacity limit of all available rooms (<strong>${capData.total_available_capacity} guests</strong>) from <strong class="text-amber-950">${checkIn}</strong> to <strong class="text-amber-950">${checkOut}</strong>.
                                    </p>
                                    <div class="p-3.5 bg-white/90 rounded-2xl border border-amber-200 text-xs text-amber-800 mb-5 font-medium">
                                        <i class="fas fa-info-circle mr-1 text-amber-600"></i> Please reduce your party size to <strong>${capData.total_available_capacity} ${capData.total_available_capacity === 1 ? 'guest' : 'guests'}</strong> or select alternative dates.
                                    </div>
                                    <button type="button" onclick="window.router('contact')" class="bg-brand-dark hover:bg-gray-800 text-white font-bold px-6 py-2.5 rounded-xl text-xs transition shadow-sm inline-flex items-center gap-2">
                                        <i class="fas fa-envelope"></i> Contact Front Desk
                                    </button>
                                </div>
                            </div>
                        `;
                    }
                    noAvailEl.style.display = '';
                }

                if (typeof window.showToast === 'function') {
                    window.showToast(`Requested ${guests} guests exceeds the total available capacity limit (${capData.total_available_capacity} guests).`, 'warning');
                }
                return;
            } else {
                if (capBanner) {
                    capBanner.classList.add('hidden');
                }
            }

            allRoomEls.forEach(el => {
                el.style.display = '';
            });

            const results = await Promise.all(allRoomEls.map(async (roomEl) => {
                const roomId = roomEl.dataset.roomId;
                const roomCapacity = parseInt(roomEl.dataset.capacity, 10) || 
                                     (window.appState?.rooms?.find(r => r.id == roomId)?.capacity) || 2;
                const roomMaxCapacity = parseInt(roomEl.dataset.maxCapacity, 10) || 
                                        parseInt(roomEl.dataset.capacity, 10) || 
                                        (window.appState?.rooms?.find(r => r.id == roomId)?.max_capacity) || 
                                        roomCapacity;

                try {
                    const response = await fetch('actions/book_action.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({
                            action: 'check_availability',
                            room_id: roomId,
                            check_in: checkIn,
                            check_out: checkOut,
                            guests: guests
                        })
                    });
                    const data = await response.json();
                    const isBooked = !!(data && data.is_booked);
                    const available = !!(data && data.success && data.available && !isBooked);

                    if (!available) {
                        // Filter out already booked or maintenance room for these dates
                        roomEl.style.display = 'none';
                        return { roomId, available: false, capacityMatch: false, isBooked: true };
                    } else {
                        // Room is available on these dates! Keep it visible - do not remove available room
                        roomEl.style.display = '';
                        const statusBadge = roomEl.querySelector('.room-status-badge');
                        if (statusBadge && statusBadge.textContent.trim().toLowerCase() === 'available') {
                            statusBadge.remove();
                        }

                        // Remove any previous recommendation or fit badges
                        const existingBadge = roomEl.querySelector('.room-rec-card-badge');
                        if (existingBadge) existingBadge.remove();

                        const isDormRoom = (data.is_dorm || parseInt(roomId) === 5 || roomEl.dataset.isDorm === '1');
                        if (isDormRoom && data.remaining_beds !== undefined) {
                            const dormPill = roomEl.querySelector('.room-capacity-pill');
                            if (dormPill) {
                                dormPill.innerHTML = `<i class="fas fa-bed text-[11px] text-brand-blue"></i> <span class="room-beds-text"><strong class="room-avail-beds">${data.remaining_beds}</strong> Beds Available</span>`;
                            }
                        }

                        // Add reminder / capacity match message badge
                        const descEl = roomEl.querySelector('p.line-clamp-2') || roomEl.querySelector('.room-card > div.p-6');
                        if (descEl && descEl.parentNode) {
                            const badgeDiv = document.createElement('div');
                            badgeDiv.className = 'room-rec-card-badge mt-2 mb-2 p-2.5 rounded-xl text-xs font-semibold flex items-start gap-2';

                            if (isDormRoom && data.remaining_beds !== undefined) {
                                if (data.remaining_beds < guests) {
                                    // Reminder: fewer beds available than requested party size
                                    badgeDiv.className += ' bg-amber-50 border border-amber-200 text-amber-900';
                                    badgeDiv.innerHTML = `<i class="fas fa-info-circle text-amber-600 mt-0.5 flex-shrink-0"></i><span><strong>Reminder:</strong> Only <strong>${data.remaining_beds} bed${data.remaining_beds > 1 ? 's' : ''}</strong> available for your party of <strong>${guests} guests</strong>.</span>`;
                                } else {
                                    badgeDiv.className += ' bg-teal-50 border border-teal-200 text-teal-900';
                                    badgeDiv.innerHTML = `<i class="fas fa-bed text-teal-600 mt-0.5 flex-shrink-0"></i><span><strong>${data.remaining_beds} Beds Available</strong> &bull; <strong>₱460.00</strong> / bed (Fits your ${guests} guest${guests > 1 ? 's' : ''})</span>`;
                                }
                            } else if (roomMaxCapacity < guests) {
                                // Reminder: room capacity is smaller than requested guests, but room is available!
                                const needed = Math.ceil(guests / roomMaxCapacity);
                                badgeDiv.className += ' bg-amber-50 border border-amber-200 text-amber-900';
                                badgeDiv.innerHTML = `<i class="fas fa-info-circle text-amber-600 mt-0.5 flex-shrink-0"></i><span><strong>Reminder:</strong> This room accommodates up to <strong>${roomMaxCapacity} guests</strong>. For your party of <strong>${guests} guests</strong>, you can book <strong>${needed} rooms</strong>.</span>`;
                            } else {
                                // Direct capacity fit
                                badgeDiv.className += ' bg-emerald-50 border border-emerald-200 text-emerald-800';
                                badgeDiv.innerHTML = `<i class="fas fa-check-circle text-emerald-500 mt-0.5 flex-shrink-0"></i><span><strong>Available:</strong> Fits your group (Up to ${roomMaxCapacity} Guests)</span>`;
                            }
                            descEl.parentNode.insertBefore(badgeDiv, descEl.nextSibling);
                        }

                        const roomAvailCap = isDormRoom ? (data.remaining_beds !== undefined ? data.remaining_beds : 8) : roomMaxCapacity;
                        const capacityMatch = isDormRoom ? (data.remaining_beds >= guests) : (roomMaxCapacity >= guests);
                        return { roomId, available: true, capacity: roomAvailCap, capacityMatch, isBooked: false };
                    }
                } catch (err) {
                    console.error('Availability check failed for room ' + roomId, err);
                    return { roomId, available: false, capacity: 0, capacityMatch: false, isBooked: false };
                }
            }));

            const availableRooms = results.filter(r => r.available);
            const totalAvailableCap = availableRooms.reduce((sum, r) => sum + (r.capacity || 0), 0);
            const anyAvailable = availableRooms.length > 0;
            const bookedCount = results.filter(r => r.isBooked).length;

            let noAvailEl = document.getElementById('no-available-rooms');
            if (!noAvailEl && roomsContainer) {
                noAvailEl = document.createElement('div');
                noAvailEl.id = 'no-available-rooms';
                noAvailEl.className = 'col-span-full text-center text-gray-500 py-6';
                roomsContainer.appendChild(noAvailEl);
            }

            // If the requested guest number exceeds the total combined limit of all available rooms:
            if (anyAvailable && guests > totalAvailableCap) {
                allRoomEls.forEach(el => {
                    el.style.display = 'none';
                });
                if (noAvailEl) {
                    noAvailEl.innerHTML = `
                        <div class="max-w-xl mx-auto my-6 text-center">
                            <div class="bg-amber-50/95 border-2 border-amber-300 rounded-3xl p-8 shadow-sm">
                                <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-2xl mx-auto mb-4 shadow-xs">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <h4 class="text-xl font-bold text-amber-950 mb-2">Guest Limit Exceeded</h4>
                                <p class="text-sm text-amber-900 mb-4 leading-relaxed font-medium">
                                    <strong>Reminder:</strong> Your requested party of <strong>${guests} guests</strong> exceeds the total capacity limit of all available rooms (<strong>${totalAvailableCap} guests</strong>) from <strong class="text-amber-950">${checkIn}</strong> to <strong class="text-amber-950">${checkOut}</strong>.
                                </p>
                                <div class="p-3.5 bg-white/90 rounded-2xl border border-amber-200 text-xs text-amber-800 mb-5 font-medium">
                                    <i class="fas fa-info-circle mr-1 text-amber-600"></i> Please reduce your party size to <strong>${totalAvailableCap} ${totalAvailableCap === 1 ? 'guest' : 'guests'}</strong> or select alternative dates.
                                </div>
                                <button type="button" onclick="window.router('contact')" class="bg-brand-dark hover:bg-gray-800 text-white font-bold px-6 py-2.5 rounded-xl text-xs transition shadow-sm inline-flex items-center gap-2">
                                    <i class="fas fa-envelope"></i> Contact Front Desk
                                </button>
                            </div>
                        </div>
                    `;
                    noAvailEl.style.display = '';
                }
                if (typeof window.showToast === 'function') {
                    window.showToast(`Requested ${guests} guests exceeds the total available capacity limit (${totalAvailableCap} guests).`, 'warning');
                }
                return;
            }

            if (noAvailEl) {
                if (!anyAvailable) {
                    noAvailEl.innerHTML = `
                        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-8 max-w-md mx-auto text-center">
                            <i class="fas fa-calendar-times text-rose-500 text-4xl mb-3"></i>
                            <h4 class="text-lg font-bold text-slate-800 mb-1">No Rooms Available</h4>
                            <p class="text-xs text-slate-500 mb-4">All rooms are booked or unavailable for selected dates from <strong class="text-slate-700">${checkIn}</strong> to <strong class="text-slate-700">${checkOut}</strong>.</p>
                            <p class="text-xs text-brand-blue font-semibold">Try choosing different check-in / check-out dates above.</p>
                        </div>
                    `;
                    noAvailEl.style.display = '';
                } else {
                    noAvailEl.style.display = 'none';
                }
            }

            if (typeof window.showToast === 'function') {
                if (anyAvailable) {
                    if (bookedCount > 0) {
                        window.showToast(`Showing ${availableRooms.length} available room${availableRooms.length > 1 ? 's' : ''}. (${bookedCount} already booked room${bookedCount > 1 ? 's' : ''} filtered out)`, 'info');
                    } else {
                        window.showToast(`All ${availableRooms.length} rooms are available for your selected dates!`, 'success');
                    }
                } else {
                    window.showToast(`No rooms available for ${checkIn} to ${checkOut}. Please try other dates.`, 'warning');
                }
            }
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = origBtnHtml;
            }
        }
    });
}

// Auto-bind availability form listener on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAvailabilityForm);
} else {
    initAvailabilityForm();
}

// Global window bindings for inline HTML and cross-module access
window.getLocalDateString = getLocalDateString;
window.fetchRooms = fetchRooms;
window.renderRooms = renderRooms;
window.initDateConstraints = initDateConstraints;
window.initGuestSelector = initGuestSelector;
window.searchFromHome = searchFromHome;
window.initAvailabilityForm = initAvailabilityForm;

/**
 * Maps amenity keywords to relevant FontAwesome icon classes.
 * 
 * @param {string} amenity - The name of the amenity.
 * @returns {string} FontAwesome icon class name.
 */
function getAmenityIcon(amenity) {
    if (!amenity) return 'fa-check-circle';
    const lower = amenity.toLowerCase();
    if (lower.includes('wifi') || lower.includes('internet')) return 'fa-wifi';
    if (lower.includes('air') || lower.includes('ac') || lower.includes('condition')) return 'fa-snowflake';
    if (lower.includes('shower') || lower.includes('bath') || lower.includes('bidet') || lower.includes('toilet') || lower.includes('hot water')) return 'fa-shower';
    if (lower.includes('tv') || lower.includes('television')) return 'fa-tv';
    if (lower.includes('bed') || lower.includes('linen')) return 'fa-bed';
    if (lower.includes('locker') || lower.includes('safe') || lower.includes('security')) return 'fa-lock';
    if (lower.includes('pool') || lower.includes('swim')) return 'fa-swimming-pool';
    if (lower.includes('towel')) return 'fa-bath';
    if (lower.includes('coffee') || lower.includes('tea') || lower.includes('breakfast')) return 'fa-coffee';
    if (lower.includes('balcony') || lower.includes('terrace') || lower.includes('view')) return 'fa-door-open';
    if (lower.includes('fan')) return 'fa-fan';
    if (lower.includes('desk') || lower.includes('work')) return 'fa-laptop';
    if (lower.includes('plug') || lower.includes('socket') || lower.includes('charge')) return 'fa-plug';
    if (lower.includes('kitchen') || lower.includes('fridge') || lower.includes('kettle')) return 'fa-utensils';
    if (lower.includes('non-smoking') || lower.includes('smoking')) return 'fa-ban-smoking';
    return 'fa-check-circle';
}

/**
 * Escapes HTML characters for safe text insertion.
 * 
 * @param {string} str - Raw string.
 * @returns {string} Escaped string.
 */
function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

/**
 * Retrieves room object by ID from client state or preloaded server data.
 * 
 * @param {string|number} roomId 
 * @returns {Object|null} Room data object
 */
function getRoomById(roomId) {
    if (window.appState && Array.isArray(window.appState.rooms)) {
        const found = window.appState.rooms.find(r => String(r.id) === String(roomId));
        if (found) return found;
    }
    if (window.serverRoomsData && window.serverRoomsData[roomId]) {
        return window.serverRoomsData[roomId];
    }
    return null;
}

/**
 * Injects modal HTML container into DOM if missing.
 */
function ensureRoomInfoModalInDom() {
    let modal = document.getElementById('room-info-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'room-info-modal';
        modal.className = 'fixed inset-0 bg-black/75 backdrop-blur-sm z-[100] hidden items-center justify-center p-3 sm:p-6 transition-opacity duration-300 opacity-0';
        modal.setAttribute('onclick', 'closeRoomInfoModal(event)');
        modal.innerHTML = `
            <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full overflow-hidden border border-slate-100 my-auto transform transition-all duration-300 scale-95 max-h-[92vh] flex flex-col" onclick="event.stopPropagation()">
                <div class="relative h-56 sm:h-72 w-full bg-slate-950 overflow-hidden flex-shrink-0">
                    <img id="modal-room-image" src="" alt="Room Photo" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/30 to-transparent"></div>
                    <button onclick="closeRoomInfoModal()" type="button" class="absolute top-4 right-4 bg-black/60 hover:bg-black/80 text-white rounded-full w-9 h-9 flex items-center justify-center backdrop-blur-md transition-all duration-200 border border-white/20 shadow-lg cursor-pointer z-20" aria-label="Close modal">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                    <div class="absolute top-4 left-4 flex flex-wrap items-center gap-2 z-20">
                        <div class="bg-white/95 backdrop-blur-md px-3 py-1 rounded-full text-xs font-bold text-slate-900 shadow-md flex items-center gap-1.5 border border-white/60">
                            <i class="fas fa-star text-amber-400 text-xs"></i>
                            <span id="modal-room-rating">5.0</span>
                            <span class="text-slate-400 text-[10px] font-medium">/ 5</span>
                        </div>
                        <span id="modal-room-status" class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 shadow-sm border border-emerald-200">
                            Available
                        </span>
                    </div>
                    <div class="absolute bottom-4 left-4 right-4 text-white z-20">
                        <div class="flex items-center gap-2 mb-1.5">
                            <span id="modal-room-type" class="bg-amber-400 text-slate-950 text-[11px] font-extrabold uppercase px-2.5 py-0.5 rounded-full tracking-wide shadow-sm">
                                STANDARD
                            </span>
                            <span class="text-xs text-slate-200 font-medium bg-black/40 backdrop-blur-sm px-2.5 py-0.5 rounded-full border border-white/20">
                                <i class="fas fa-user-friends mr-1 text-[10px]"></i> <span id="modal-room-capacity">Up to 2 Guests</span>
                            </span>
                        </div>
                        <h3 id="modal-room-name" class="text-2xl sm:text-3xl font-serif font-bold text-white drop-shadow-md leading-tight">
                            Room Name
                        </h3>
                    </div>
                </div>
                <div class="p-6 sm:p-8 space-y-6 overflow-y-auto flex-1">
                    <div class="grid grid-cols-3 gap-3 p-3.5 bg-slate-50 rounded-2xl border border-slate-100 text-center">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-tag text-amber-500 text-sm mb-1"></i>
                            <span class="text-[10px] uppercase font-bold text-slate-400">Nightly Rate</span>
                            <span id="modal-highlight-price" class="text-sm sm:text-base font-bold text-brand-blue">₱0.00</span>
                        </div>
                        <div class="flex flex-col items-center border-x border-slate-200">
                            <i class="fas fa-user-friends text-sky-500 text-sm mb-1"></i>
                            <span class="text-[10px] uppercase font-bold text-slate-400">Max Capacity</span>
                            <span id="modal-highlight-capacity" class="text-sm sm:text-base font-bold text-slate-700">2 Guests</span>
                        </div>
                        <div class="flex flex-col items-center">
                            <i class="fas fa-wifi text-teal-500 text-sm mb-1"></i>
                            <span class="text-[10px] uppercase font-bold text-slate-400">Internet</span>
                            <span class="text-sm sm:text-base font-bold text-emerald-600">Free Starlink</span>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2 flex items-center gap-2">
                            <i class="fas fa-align-left text-amber-500"></i> Room Overview & Details
                        </h4>
                        <div id="modal-room-description" class="text-slate-600 text-sm leading-relaxed whitespace-pre-line bg-slate-50/70 p-4 rounded-2xl border border-slate-100">
                            Room description...
                        </div>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-2">
                            <i class="fas fa-concierge-bell text-amber-500"></i> Included Amenities & Features
                        </h4>
                        <div id="modal-room-amenities" class="flex flex-wrap gap-2"></div>
                    </div>
                    <div class="border-t border-slate-100 pt-5">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-2">
                            <i class="fas fa-shield-alt text-amber-500"></i> Guest Guidelines & Inn Perks
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs text-slate-600">
                            <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                                <i class="fas fa-clock text-amber-500"></i>
                                <span>Check-in: <strong>2:00 PM</strong> | Check-out: <strong>12:00 PM</strong></span>
                            </div>
                            <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                                <i class="fas fa-water text-sky-500"></i>
                                <span><strong>2 mins</strong> walk to Alona beachfront</span>
                            </div>
                            <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                                <i class="fas fa-coffee text-amber-600"></i>
                                <span>Free drinking water & coffee station</span>
                            </div>
                            <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                                <i class="fas fa-lock text-emerald-500"></i>
                                <span>Individual guest storage & 24/7 front desk</span>
                            </div>
                        </div>
                    </div>

                    <!-- Guest Reviews & Ratings Section (Horizontal Scrollable) -->
                    <div id="modal-reviews-section" class="border-t border-slate-100 pt-5">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                                    <i class="fas fa-star text-amber-500"></i> Guest Reviews & Ratings
                                </h4>
                                <div class="flex items-center gap-2 mt-1">
                                    <div class="flex items-center text-amber-400 text-xs" id="modal-reviews-stars">
                                        <!-- Dynamic Stars -->
                                    </div>
                                    <span id="modal-reviews-avg-rating" class="font-bold text-slate-800 text-sm">5.0</span>
                                    <span class="text-slate-400 text-xs">&bull;</span>
                                    <span id="modal-reviews-count-text" class="text-xs text-slate-500 font-medium">0 verified reviews</span>
                                </div>
                            </div>
                            <!-- Horizontal Scroll Left/Right Arrow Controls -->
                            <div class="flex items-center gap-1.5" id="modal-reviews-nav-controls" style="display: none;">
                                <button type="button" onclick="scrollModalReviews('left')" class="w-8 h-8 rounded-full border border-slate-200 bg-white hover:bg-slate-100 text-slate-600 hover:text-slate-900 flex items-center justify-center transition shadow-xs cursor-pointer" title="Previous reviews" aria-label="Previous reviews">
                                    <i class="fas fa-chevron-left text-xs"></i>
                                </button>
                                <button type="button" onclick="scrollModalReviews('right')" class="w-8 h-8 rounded-full border border-slate-200 bg-white hover:bg-slate-100 text-slate-600 hover:text-slate-900 flex items-center justify-center transition shadow-xs cursor-pointer" title="Next reviews" aria-label="Next reviews">
                                    <i class="fas fa-chevron-right text-xs"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Horizontal Scrollable Container -->
                        <div id="modal-reviews-scroll-container" 
                             class="flex gap-3.5 overflow-x-auto pb-3 pt-1 snap-x snap-mandatory scroll-smooth"
                             style="scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent; -webkit-overflow-scrolling: touch;">
                            <div class="text-center py-6 w-full text-xs text-slate-400">Loading reviews...</div>
                        </div>

                        <!-- Review Form Trigger & Collapsible Form -->
                        <div id="modal-review-form-wrapper" class="mt-3">
                            <!-- Filled dynamically by reviews module -->
                        </div>
                    </div>
                </div>
                <div class="p-4 sm:p-6 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-4 flex-shrink-0">
                    <div>
                        <span class="text-[10px] font-bold uppercase text-slate-400 block">Nightly Rate</span>
                        <div class="flex items-baseline gap-1">
                            <span id="modal-footer-price" class="text-2xl font-bold text-brand-blue">₱0.00</span>
                            <span class="text-xs text-slate-500">/ night</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="closeRoomInfoModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-200/60 font-semibold text-sm transition cursor-pointer">
                            Close
                        </button>
                        <button type="button" id="modal-book-btn" class="bg-brand-dark hover:bg-slate-800 text-white px-5 py-2.5 rounded-xl font-bold text-sm transition shadow-md hover:shadow-lg flex items-center gap-2 cursor-pointer">
                            <i class="fas fa-calendar-check text-amber-400"></i>
                            <span id="modal-book-btn-text">Book Now</span>
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }
}

/**
 * Populates modal elements with the provided room data and shows it with transitions.
 * 
 * @param {Object} room - The room data object.
 */
function populateAndShowRoomModal(room, scrollToSection = null) {
    ensureRoomInfoModalInDom();

    const modal = document.getElementById('room-info-modal');
    if (!modal) return;

    // Image
    const imgEl = document.getElementById('modal-room-image');
    if (imgEl) {
        imgEl.src = room.image || 'assets/image/background.jpg';
        imgEl.alt = room.name || 'Room Photo';
    }

    // Name & Type
    const nameEl = document.getElementById('modal-room-name');
    if (nameEl) nameEl.textContent = room.name || 'Room Details';

    const typeEl = document.getElementById('modal-room-type');
    if (typeEl) typeEl.textContent = (room.type || 'Standard').toUpperCase();

    // Rating
    const ratingVal = parseFloat(room.rating) || 5.0;
    const ratingEl = document.getElementById('modal-room-rating');
    if (ratingEl) ratingEl.textContent = ratingVal.toFixed(1);

    // Capacity
    const isDorm = (parseInt(room.id) === 5 || (room.type && room.type.toLowerCase().includes('dorm')));
    const capacityVal = isDorm ? 8 : (parseInt(room.max_capacity || room.capacity, 10) || 2);
    const capText = isDorm ? '8 Beds (Shared Dormitory)' : `Up to ${capacityVal} Guest${capacityVal > 1 ? 's' : ''}`;
    const capEl = document.getElementById('modal-room-capacity');
    if (capEl) capEl.textContent = capText;

    const highlightCapEl = document.getElementById('modal-highlight-capacity');
    if (highlightCapEl) highlightCapEl.textContent = isDorm ? '8 Beds Total' : `${capacityVal} Guest${capacityVal > 1 ? 's' : ''}`;

    // Price
    const highlightPriceEl = document.getElementById('modal-highlight-price');
    const footerPriceEl = document.getElementById('modal-footer-price');
    if (isDorm) {
        if (highlightPriceEl) highlightPriceEl.innerHTML = `₱3,680.00 <span class="text-xs font-normal text-slate-500 block sm:inline">(₱460.00 / bed)</span>`;
        if (footerPriceEl) footerPriceEl.innerHTML = `₱3,680.00 <span class="text-xs text-gray-400 font-normal">/ night (8 Beds) &bull; ₱460.00/bed</span>`;
    } else {
        const priceVal = parseFloat(room.price) || 0;
        const formattedPrice = '₱' + priceVal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (highlightPriceEl) highlightPriceEl.textContent = formattedPrice;
        if (footerPriceEl) footerPriceEl.textContent = formattedPrice;
    }

    // Status Badge
    const statusEl = document.getElementById('modal-room-status');
    const isMaintenance = (room.status && room.status.toLowerCase() === 'maintenance');
    if (statusEl) {
        if (isMaintenance) {
            statusEl.className = 'px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-rose-100 text-rose-800 shadow-sm border border-rose-200';
            statusEl.textContent = 'Maintenance';
        } else {
            statusEl.className = 'px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 shadow-sm border border-emerald-200';
            statusEl.textContent = 'Available';
        }
    }

    // Description
    const descEl = document.getElementById('modal-room-description');
    const descriptionText = room.desc || room.description || 'Experience comfort and relaxing island living at W Island Hub Backpackers Inn, with convenient access to Panglao attractions and Alona Beach.';
    if (descEl) {
        descEl.textContent = descriptionText;
    }

    // Amenities
    const amenitiesContainer = document.getElementById('modal-room-amenities');
    if (amenitiesContainer) {
        amenitiesContainer.innerHTML = '';
        let amenitiesList = [];
        if (Array.isArray(room.amenities)) {
            amenitiesList = room.amenities;
        } else if (typeof room.amenities === 'string') {
            try {
                amenitiesList = JSON.parse(room.amenities);
            } catch (e) {
                amenitiesList = room.amenities.split(',').map(s => s.trim());
            }
        }
        amenitiesList = (Array.isArray(amenitiesList) ? amenitiesList : []).filter(a => a && typeof a === 'string' && a.trim().length > 0);

        if (amenitiesList.length === 0) {
            amenitiesContainer.innerHTML = '<span class="text-xs text-slate-400 italic">Essential island accommodation amenities provided.</span>';
        } else {
            amenitiesList.forEach(amenity => {
                const iconClass = getAmenityIcon(amenity);
                const pill = document.createElement('div');
                pill.className = 'inline-flex items-center gap-2 px-3.5 py-2 bg-slate-50 hover:bg-amber-50/60 border border-slate-200/80 rounded-xl text-xs font-semibold text-slate-700 shadow-sm transition-colors duration-200';
                pill.innerHTML = `<i class="fas ${iconClass} text-amber-500"></i> <span>${escapeHtml(amenity.trim())}</span>`;
                amenitiesContainer.appendChild(pill);
            });
        }
    }

    // Book Button
    const bookBtn = document.getElementById('modal-book-btn');
    const bookBtnText = document.getElementById('modal-book-btn-text');
    if (bookBtn) {
        if (isMaintenance) {
            bookBtn.disabled = true;
            bookBtn.className = 'bg-slate-300 text-slate-500 px-5 py-2.5 rounded-xl font-bold text-sm cursor-not-allowed flex items-center gap-2 shadow-none';
            if (bookBtnText) bookBtnText.textContent = 'Under Maintenance';
            bookBtn.onclick = null;
        } else {
            bookBtn.disabled = false;
            bookBtn.className = 'bg-brand-dark hover:bg-slate-800 text-white px-5 py-2.5 rounded-xl font-bold text-sm transition shadow-md hover:shadow-lg flex items-center gap-2 cursor-pointer';
            if (bookBtnText) bookBtnText.textContent = 'Book Now';
            bookBtn.onclick = () => {
                closeRoomInfoModal();
                if (typeof window.initiateBooking === 'function') {
                    window.initiateBooking(room.id);
                }
            };
        }
    }

    // Show modal with animation
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    void modal.offsetWidth;
    modal.classList.remove('opacity-0');
    
    const dialogBox = modal.querySelector('div');
    if (dialogBox) {
        dialogBox.classList.remove('scale-95');
        dialogBox.classList.add('scale-100');
    }

    // Automatically load verified guest reviews into modal
    if (typeof window.loadModalReviews === 'function') {
        window.loadModalReviews(room.id);
    }

    // Scroll to section if specified
    if (scrollToSection === 'reviews') {
        setTimeout(() => {
            const revSection = document.getElementById('modal-reviews-section');
            if (revSection) {
                revSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }, 300);
    } else if (scrollToSection === 'amenities') {
        setTimeout(() => {
            const amenSection = document.getElementById('modal-room-amenities');
            if (amenSection) {
                amenSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                // Briefly highlight the amenities section to draw attention
                amenSection.classList.add('ring-2', 'ring-amber-400', 'ring-offset-2', 'rounded-2xl');
                setTimeout(() => amenSection.classList.remove('ring-2', 'ring-amber-400', 'ring-offset-2', 'rounded-2xl'), 1500);
            }
        }, 300);
    }

    document.body.style.overflow = 'hidden';
}

/**
 * Closes the Customer Room Information Modal.
 * 
 * @param {Event} [e] - Click or keyboard event.
 */
function closeRoomInfoModal(e) {
    if (e && e.target) {
        const dialogBox = e.target.closest && e.target.closest('#room-info-modal > div');
        if (dialogBox && !e.target.closest('button[onclick*="closeRoomInfoModal"]')) {
            return;
        }
    }

    const modal = document.getElementById('room-info-modal');
    if (!modal) return;

    modal.classList.add('opacity-0');
    const dialogBox = modal.querySelector('div');
    if (dialogBox) {
        dialogBox.classList.remove('scale-100');
        dialogBox.classList.add('scale-95');
    }

    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }, 250);
}

/**
 * Opens Customer Room Information Modal for specified room ID.
 * Looks up room from state, preloaded server data, or fetches from API as fallback.
 * 
 * @param {number|string} roomId - The ID of the room.
 * @param {string|null} [scrollToSection=null] - Section ID to scroll to (e.g., 'reviews').
 */
async function openRoomInfoModal(roomId, scrollToSection = null) {
    let room = getRoomById(roomId);

    if (!room) {
        try {
            const res = await fetch('actions/rooms_action.php');
            const data = await res.json();
            if (data && data.success && Array.isArray(data.rooms)) {
                const matched = data.rooms.find(r => String(r.id) === String(roomId));
                if (matched) {
                    let parsedAmenities = [];
                    if (typeof matched.amenities === 'string') {
                        try {
                            parsedAmenities = JSON.parse(matched.amenities || '[]');
                        } catch(e) {
                            parsedAmenities = matched.amenities.split(',').map(s => s.trim());
                        }
                    } else if (Array.isArray(matched.amenities)) {
                        parsedAmenities = matched.amenities;
                    }
                    room = {
                        id: matched.id,
                        name: matched.name,
                        type: matched.type,
                        price: parseFloat(matched.price) || 0,
                        capacity: parseInt(matched.capacity) || 2,
                        image: matched.image || 'assets/image/background.jpg',
                        desc: matched.description || matched.desc || '',
                        description: matched.description || matched.desc || '',
                        amenities: parsedAmenities,
                        rating: parseFloat(matched.rating) || 5.0,
                        status: matched.status || 'Available'
                    };
                }
            }
        } catch (e) {
            console.error('Failed to fetch room info:', e);
        }
    }

    if (!room) {
        if (typeof window.showToast === 'function') {
            window.showToast('Unable to load room details.', 'error');
        }
        return;
    }

    populateAndShowRoomModal(room, scrollToSection);
}

// Global keydown listener for Escape key to close modals
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const modal = document.getElementById('room-info-modal');
        if (modal && !modal.classList.contains('hidden')) {
            closeRoomInfoModal();
        }
        const recModal = document.getElementById('room-recommendation-modal');
        if (recModal && !recModal.classList.contains('hidden')) {
            closeRoomRecommendationModal();
        }
    }
});

let recommendationContext = null;

/**
 * Displays the Room Recommendation Modal when group size exceeds room capacity or hotel capacity.
 * @param {Object} chosenRoom - The room object.
 * @param {number} totalGuests - Total number of guests.
 * @param {number} adults - Adults count.
 * @param {number} children - Children count.
 */
function showRoomRecommendationModal(chosenRoom, totalGuests, adults, children) {
    const modal = document.getElementById('room-recommendation-modal');
    if (!modal) return;

    const chosenCap = parseInt(chosenRoom.max_capacity || chosenRoom.capacity, 10) || 2;
    const hotelCap = getTotalHotelCapacity();
    const isHotelOvercapacity = totalGuests > hotelCap;
    const neededRooms = Math.ceil(totalGuests / chosenCap);
    const roomPrice = parseFloat(chosenRoom.price) || 0;
    const multiRoomNightPrice = roomPrice * neededRooms;

    recommendationContext = {
        chosenRoom,
        totalGuests,
        adults,
        children,
        neededRooms,
        chosenCap,
        hotelCap
    };

    // Populate explanation banner
    const banner = document.getElementById('rec-explanation-banner');
    if (banner) {
        if (isHotelOvercapacity) {
            banner.className = 'p-3.5 bg-rose-50/90 rounded-2xl border border-rose-200 text-xs text-rose-900 mb-4 leading-relaxed flex-shrink-0';
            banner.innerHTML = `
                <div class="flex items-start gap-2">
                    <i class="fas fa-exclamation-circle text-rose-600 mt-0.5 text-sm flex-shrink-0"></i>
                    <div>
                        <strong class="font-bold text-rose-950">Hotel Capacity Notice:</strong> The hotel does not accommodate <strong>${totalGuests} guests</strong> because our available room capacity is limited to <strong>${hotelCap} guests</strong>.
                    </div>
                </div>
            `;
        } else {
            banner.className = 'p-3.5 bg-amber-50/90 rounded-2xl border border-amber-200 text-xs text-amber-900 mb-4 leading-relaxed flex-shrink-0';
            banner.innerHTML = `
                <div class="flex items-start gap-2">
                    <i class="fas fa-exclamation-triangle text-amber-600 mt-0.5 text-sm flex-shrink-0"></i>
                    <div>
                        <strong>Group Size Exceeds Room Capacity:</strong> You selected <strong>${chosenRoom.name}</strong> (${chosenRoom.type || 'Standard'}), which accommodates up to <strong>${chosenCap} guests</strong>. Your party has <strong>${totalGuests} guests</strong> (${adults} Adult${adults > 1 ? 's' : ''}${children > 0 ? `, ${children} Child${children > 1 ? 'ren' : ''}` : ''}).
                    </div>
                </div>
            `;
        }
    }

    // Find larger alternative rooms from state that can accommodate the whole group
    const allRooms = (window.appState && Array.isArray(window.appState.rooms)) 
        ? window.appState.rooms 
        : (Object.values(window.serverRoomsData || {}));
    
    const largerRooms = allRooms.filter(r => {
        const cap = parseInt(r.max_capacity || r.capacity, 10) || 0;
        return r.id != chosenRoom.id && cap >= totalGuests && r.status !== 'Maintenance';
    });

    // Check how many rooms of the same category exist in the hotel
    const sameTypeRooms = allRooms.filter(r => (r.type || '').toLowerCase() === (chosenRoom.type || '').toLowerCase() && r.status !== 'Maintenance');
    const sameTypeCount = sameTypeRooms.length;
    const canDoMultiRoom = !isHotelOvercapacity && neededRooms <= sameTypeCount && (neededRooms * chosenCap <= hotelCap);

    const container = document.getElementById('rec-options-container');
    if (container) {
        let html = '';

        if (isHotelOvercapacity) {
            html += `
                <div class="p-6 bg-rose-50/70 border-2 border-rose-200 rounded-2xl text-center space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-2xl mx-auto shadow-inner">
                        <i class="fas fa-users-slash"></i>
                    </div>
                    <h4 class="text-sm font-bold text-rose-900">Hotel Cannot Accommodate ${totalGuests} Guests</h4>
                    <p class="text-xs text-rose-800 leading-relaxed max-w-md mx-auto font-medium">
                        The hotel does not accommodate ${totalGuests} guests as the available room capacity is limited to ${hotelCap} guests.
                    </p>
                    <div class="pt-2">
                        <button type="button" onclick="closeRoomRecommendationModal(); window.router('contact');" class="bg-rose-600 hover:bg-rose-700 text-white font-bold px-4 py-2 rounded-xl text-xs transition shadow-sm inline-flex items-center gap-2 cursor-pointer">
                            <i class="fas fa-phone-alt"></i> Contact Front Desk for Special Inquiries
                        </button>
                    </div>
                </div>
            `;
        } else {
            // Option 1: Add Multiple Rooms (e.g. 2x Standard Double)
            if (canDoMultiRoom) {
                html += `
                    <div class="p-4 rounded-2xl border-2 border-brand-blue/30 bg-blue-50/40 hover:border-brand-blue transition shadow-xs">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-brand-blue flex items-center gap-1.5">
                                <i class="fas fa-layer-group"></i> Option 1: Add More Rooms (${neededRooms} Rooms Needed)
                            </span>
                            <span class="text-[11px] font-bold bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full">
                                Recommended
                            </span>
                        </div>
                        <h4 class="text-base font-bold text-gray-800 mb-1">Book ${neededRooms}x ${chosenRoom.name}</h4>
                        <p class="text-xs text-gray-500 mb-3">
                            Reserve <strong>${neededRooms} rooms</strong> of this accommodation to fit all ${totalGuests} guests (${neededRooms} rooms &times; ${chosenCap} capacity = ${neededRooms * chosenCap} total capacity).
                        </p>
                        <div class="flex items-center justify-between pt-2 border-t border-blue-100/80">
                            <div>
                                <span class="text-[10px] text-gray-400 uppercase font-bold block">Combined Rate</span>
                                <strong class="text-sm font-bold text-brand-dark">₱${multiRoomNightPrice.toFixed(2)}</strong>
                                <span class="text-[11px] text-gray-500">/ night (${neededRooms} rooms)</span>
                            </div>
                            <button type="button" onclick="proceedMultiRoomBooking(${chosenRoom.id}, ${neededRooms})" class="bg-brand-blue hover:bg-sky-600 text-white font-bold px-4 py-2 rounded-xl text-xs transition shadow-sm flex items-center gap-1.5 cursor-pointer">
                                <i class="fas fa-check"></i> Book ${neededRooms} Rooms
                            </button>
                        </div>
                    </div>
                `;
            } else if (neededRooms > 1) {
                html += `
                    <div class="p-4 rounded-2xl border border-amber-200 bg-amber-50/40 text-xs text-amber-900 space-y-1">
                        <strong>Multi-Room Notice:</strong> The hotel does not have enough rooms of this category to accommodate ${totalGuests} guests in ${neededRooms} separate rooms (only ${sameTypeCount} room${sameTypeCount > 1 ? 's' : ''} available).
                    </div>
                `;
            }

            // Option 2: Recommended Larger Rooms (e.g. Family Suite / Dorm)
            if (largerRooms.length > 0) {
                html += `
                    <div class="space-y-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-700 flex items-center gap-1.5 pt-1">
                            <i class="fas fa-star text-amber-500"></i> Option 2: Switch to a Room that Fits Your Whole Group
                        </span>
                `;
                largerRooms.forEach(lr => {
                    const lrCap = parseInt(lr.max_capacity || lr.capacity, 10) || 0;
                    const lrPrice = parseFloat(lr.price) || 0;
                    html += `
                        <div class="p-4 rounded-2xl border border-emerald-200 bg-emerald-50/30 hover:border-emerald-400 transition shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <img src="${lr.image}" alt="${lr.name}" class="w-16 h-16 rounded-xl object-cover border border-gray-200 flex-shrink-0">
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <h4 class="font-bold text-gray-800 text-sm">${lr.name}</h4>
                                        <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded bg-white text-emerald-800 border border-emerald-200">${lr.type}</span>
                                    </div>
                                    <p class="text-xs text-emerald-700 font-semibold mt-0.5 flex items-center gap-1">
                                        <i class="fas fa-user-friends text-[10px]"></i> Accommodates up to ${lrCap} Guests
                                    </p>
                                    <span class="text-xs font-bold text-gray-900 mt-1 block">₱${lrPrice.toFixed(2)} <span class="text-[10px] text-gray-500 font-normal">/ night</span></span>
                                </div>
                            </div>
                            <button type="button" onclick="proceedSwitchRoomBooking(${lr.id})" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 py-2 rounded-xl text-xs transition shadow-sm flex-shrink-0 w-full sm:w-auto flex items-center justify-center gap-1.5 cursor-pointer">
                                Switch &amp; Book
                            </button>
                        </div>
                    `;
                });
                html += `</div>`;
            }
        }

        container.innerHTML = html;
    }

    const proceedSingleBtn = document.getElementById('rec-proceed-single-btn');
    if (proceedSingleBtn) {
        proceedSingleBtn.textContent = `Continue with 1 Room (${chosenCap} Guest${chosenCap > 1 ? 's' : ''} Max)`;
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeRoomRecommendationModal(e) {
    if (e && e.target && e.target.id !== 'room-recommendation-modal') {
        return;
    }
    const modal = document.getElementById('room-recommendation-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

function proceedMultiRoomBooking(roomId, roomsCount) {
    closeRoomRecommendationModal();
    const ctx = recommendationContext;
    if (typeof window.initiateBooking === 'function') {
        window.initiateBooking(roomId, {
            roomsCount: roomsCount,
            guests: ctx ? ctx.totalGuests : 4,
            adults: ctx ? ctx.adults : 2,
            children: ctx ? ctx.children : 2,
            bypassRecommendation: true
        });
    }
}

function proceedSwitchRoomBooking(newRoomId) {
    closeRoomRecommendationModal();
    const ctx = recommendationContext;
    if (typeof window.initiateBooking === 'function') {
        window.initiateBooking(newRoomId, {
            roomsCount: 1,
            guests: ctx ? ctx.totalGuests : 4,
            adults: ctx ? ctx.adults : 2,
            children: ctx ? ctx.children : 2,
            bypassRecommendation: true
        });
    }
}

function proceedSingleRoomAnyway() {
    closeRoomRecommendationModal();
    const ctx = recommendationContext;
    if (!ctx) return;
    const roomCap = parseInt(ctx.chosenRoom.max_capacity || ctx.chosenRoom.capacity, 10) || 2;
    const clampedAdults = Math.min(ctx.adults, roomCap);
    const clampedChildren = Math.max(0, Math.min(ctx.children, roomCap - clampedAdults));
    const clampedTotal = clampedAdults + clampedChildren;

    if (typeof window.showToast === 'function') {
        window.showToast(`Party size adjusted to ${clampedTotal} guests to match room capacity (${roomCap} max).`, 'info');
    }

    if (typeof window.initiateBooking === 'function') {
        window.initiateBooking(ctx.chosenRoom.id, {
            roomsCount: 1,
            guests: clampedTotal,
            adults: clampedAdults,
            children: clampedChildren,
            bypassRecommendation: true
        });
    }
}

// Window function bindings for inline HTML and cross-module access
window.openRoomInfoModal = openRoomInfoModal;
window.closeRoomInfoModal = closeRoomInfoModal;
window.openRoomModal = openRoomInfoModal;
window.closeRoomModal = closeRoomInfoModal;
window.toggleGuestDropdown = toggleGuestDropdown;
window.closeGuestDropdown = closeGuestDropdown;
window.updateGuestStepper = updateGuestStepper;
window.applyGuestValuesToScope = applyGuestValuesToScope;
window.showRoomRecommendationModal = showRoomRecommendationModal;
window.closeRoomRecommendationModal = closeRoomRecommendationModal;
window.proceedMultiRoomBooking = proceedMultiRoomBooking;
window.proceedSwitchRoomBooking = proceedSwitchRoomBooking;
window.proceedSingleRoomAnyway = proceedSingleRoomAnyway;
window.updateChildAge = updateChildAge;
window.getChildAges = getChildAges;
window.renderChildAgesUI = renderChildAgesUI;
window.validateChildAges = validateChildAges;


