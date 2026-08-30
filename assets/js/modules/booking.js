/**
 * Multi-Tier Booking & Reservation Engine Client Module
 */

let selectedRoom = null;
let currentQuote = null;
let selectedPathway = 'full_gcash'; // 'full_gcash', 'deposit_gcash', 'pay_at_hotel'
let selectedReceiptFile = null;
let holdCountdownInterval = null;

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', function() {
    initBookingModule();
});

function initBookingModule() {
    // Set default dates if empty
    const checkinInput = document.getElementById('book-checkin-date');
    const checkoutInput = document.getElementById('book-checkout-date');
    
    if (checkinInput && !checkinInput.value) {
        const today = new Date();
        const tomorrow = new Date();
        tomorrow.setDate(today.getDate() + 1);

        checkinInput.min = today.toISOString().split('T')[0];
        checkinInput.value = today.toISOString().split('T')[0];

        checkoutInput.min = tomorrow.toISOString().split('T')[0];
        checkoutInput.value = tomorrow.toISOString().split('T')[0];
    }

    // Prefill user data if logged in
    const currentUser = (window.appState && window.appState.currentUser) ? window.appState.currentUser : null;
    if (currentUser) {
        const nameInput = document.getElementById('book-guest-name');
        const emailInput = document.getElementById('book-guest-email');
        const phoneInput = document.getElementById('book-guest-phone');
        if (nameInput && !nameInput.value) nameInput.value = currentUser.name || '';
        if (emailInput && !emailInput.value) emailInput.value = currentUser.email || '';
        if (phoneInput && !phoneInput.value) phoneInput.value = currentUser.phone || '';
    }
}

/**
 * Open Booking Form for a Specific Room
 */
function openBookingForRoom(roomId) {
    // Fetch room details from rooms or appState
    fetch('actions/rooms_action.php')
        .then(res => res.json())
        .then(data => {
            if (data.success && data.rooms) {
                const room = data.rooms.find(r => r.id == roomId);
                if (room) {
                    setBookingRoom(room);
                    // Navigate / Scroll to booking section
                    const bookSection = document.getElementById('book');
                    if (bookSection) {
                        bookSection.scrollIntoView({ behavior: 'smooth' });
                    }
                }
            }
        })
        .catch(err => console.error('Error fetching room for booking:', err));
}

function setBookingRoom(room) {
    selectedRoom = room;
    document.getElementById('book-room-id').value = room.id;
    document.getElementById('summary-room-name').innerText = room.name;
    document.getElementById('summary-room-type').innerText = room.type || 'Hostel Room';
    document.getElementById('summary-room-price').innerText = parseFloat(room.price).toFixed(2);
    
    const imgElem = document.getElementById('summary-room-img');
    if (imgElem) {
        imgElem.src = (room.image && !room.image.startsWith('http')) ? room.image : (room.image || 'assets/image/background.jpg');
    }

    fetchBookingQuote();
}

/**
 * Triggered whenever check-in or check-out dates change
 */
function onBookingDatesChanged() {
    const checkin = document.getElementById('book-checkin-date').value;
    const checkoutInput = document.getElementById('book-checkout-date');
    
    if (checkin) {
        const nextDay = new Date(checkin);
        nextDay.setDate(nextDay.getDate() + 1);
        checkoutInput.min = nextDay.toISOString().split('T')[0];
        if (checkoutInput.value && checkoutInput.value <= checkin) {
            checkoutInput.value = nextDay.toISOString().split('T')[0];
        }
    }

    fetchBookingQuote();
}

/**
 * Calculate Quote & Evaluate 24-Hour Rule
 */
function fetchBookingQuote() {
    const roomId = document.getElementById('book-room-id').value;
    const checkin = document.getElementById('book-checkin-date').value;
    const checkout = document.getElementById('book-checkout-date').value;

    if (!roomId || !checkin || !checkout) return;

    fetch(`actions/book_action.php?action=calculate_quote&room_id=${roomId}&check_in=${checkin}&check_out=${checkout}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.quote) {
                currentQuote = data.quote;
                updateQuoteUI(data);
            } else if (!data.is_available) {
                if (typeof showToast === 'function') {
                    showToast(data.conflict_reason || 'Room is not available for these dates', 'error');
                }
            }
        })
        .catch(err => console.error('Quote fetch error:', err));
}

/**
 * Update UI with Quote & 24-Hour Rule State
 */
function updateQuoteUI(data) {
    const quote = data.quote;
    const isWithin24h = data.is_within_24h;

    // Summary Card values
    document.getElementById('summary-checkin').innerText = new Date(quote.check_in).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    document.getElementById('summary-checkout').innerText = new Date(quote.check_out).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    document.getElementById('summary-nights').innerText = `${quote.nights} Night${quote.nights > 1 ? 's' : ''}`;
    document.getElementById('summary-total-bill').innerText = `₱${parseFloat(quote.total_amount).toFixed(2)}`;

    // Payment pathway cards
    document.getElementById('opt-a-amount').innerText = `₱${parseFloat(quote.total_amount).toFixed(2)}`;
    document.getElementById('opt-b-deposit').innerText = `₱${parseFloat(quote.deposit_amount).toFixed(2)}`;
    document.getElementById('opt-b-balance').innerText = `₱${parseFloat(quote.deposit_balance_due).toFixed(2)}`;
    document.getElementById('opt-c-total').innerText = `₱${parseFloat(quote.total_amount).toFixed(2)}`;

    // 24-Hour Rule: Lock or Unlock Option C
    const optCLocked = document.getElementById('opt-c-locked-overlay');
    const optCRadio = document.getElementById('opt-c-radio');
    const tierBadge = document.getElementById('tier-badge');

    if (isWithin24h) {
        optCLocked.classList.add('hidden');
        optCRadio.disabled = false;
        tierBadge.innerText = '3 Options Available';
        tierBadge.className = 'text-xs px-2.5 py-0.5 rounded-full font-semibold bg-emerald-100 text-emerald-800';
    } else {
        optCLocked.classList.remove('hidden');
        optCRadio.disabled = true;
        tierBadge.innerText = '2 Options (Advance Booking)';
        tierBadge.className = 'text-xs px-2.5 py-0.5 rounded-full font-semibold bg-slate-100 text-slate-600';
        
        // If Option C was selected, fall back to Option A
        if (selectedPathway === 'pay_at_hotel') {
            selectPaymentPathway('full_gcash');
        }
    }

    refreshAmountsDue();
}

/**
 * Pathway Selection Handler
 */
function selectPaymentPathway(pathway) {
    selectedPathway = pathway;

    // Update radio inputs & card borders
    document.querySelectorAll('.payment-card').forEach(card => {
        card.classList.remove('border-brand-blue', 'bg-blue-50/40');
        card.classList.add('border-slate-200', 'bg-white');
    });

    const activeRadio = document.querySelector(`input[name="payment_pathway"][value="${pathway}"]`);
    if (activeRadio) {
        activeRadio.checked = true;
        const parentCard = activeRadio.closest('.payment-card');
        if (parentCard) {
            parentCard.classList.remove('border-slate-200', 'bg-white');
            parentCard.classList.add('border-brand-blue', 'bg-blue-50/40');
        }
    }

    // Toggle GCash Proof Section vs Arrival Time Section
    const gcashProofSection = document.getElementById('gcash-proof-section');
    const arrivalTimeSection = document.getElementById('arrival-time-section');

    if (pathway === 'pay_at_hotel') {
        gcashProofSection.classList.add('hidden');
        arrivalTimeSection.classList.remove('hidden');
    } else {
        gcashProofSection.classList.remove('hidden');
        arrivalTimeSection.classList.add('hidden');
    }

    refreshAmountsDue();
}

/**
 * Refresh Amount Due Today and Balance Due
 */
function refreshAmountsDue() {
    if (!currentQuote) return;

    let dueToday = 0;
    let balanceDue = currentQuote.total_amount;

    if (selectedPathway === 'full_gcash') {
        dueToday = currentQuote.total_amount;
        balanceDue = 0.00;
    } else if (selectedPathway === 'deposit_gcash') {
        dueToday = currentQuote.deposit_amount;
        balanceDue = currentQuote.deposit_balance_due;
    } else if (selectedPathway === 'pay_at_hotel') {
        dueToday = 0.00;
        balanceDue = currentQuote.total_amount;
    }

    document.getElementById('summary-amount-due').innerText = `₱${parseFloat(dueToday).toFixed(2)}`;
    document.getElementById('summary-balance-due').innerText = `₱${parseFloat(balanceDue).toFixed(2)}`;
    document.getElementById('transfer-amount-due').innerText = `₱${parseFloat(dueToday).toFixed(2)}`;
}

/**
 * Handle Receipt File Drag & Drop / Selection
 */
function handleReceiptFileSelect(e) {
    const file = e.target.files[0];
    if (!file) return;

    if (file.size > 5 * 1024 * 1024) {
        if (typeof showToast === 'function') {
            showToast('File is too large (Maximum size is 5MB)', 'error');
        } else {
            alert('File is too large (Maximum size is 5MB)');
        }
        return;
    }

    selectedReceiptFile = file;
    const reader = new FileReader();
    reader.onload = function(evt) {
        document.getElementById('receipt-preview-img').src = evt.target.result;
        document.getElementById('receipt-filename').innerText = file.name;
        document.getElementById('receipt-filesize').innerText = `${(file.size / (1024 * 1024)).toFixed(2)} MB`;
        
        document.getElementById('dropzone-prompt').classList.add('hidden');
        document.getElementById('receipt-preview-container').classList.remove('hidden');
    };
    reader.readAsDataURL(file);
}

function removeReceiptFile() {
    selectedReceiptFile = null;
    document.getElementById('receipt-file-input').value = '';
    document.getElementById('receipt-preview-img').src = '';
    document.getElementById('dropzone-prompt').classList.remove('hidden');
    document.getElementById('receipt-preview-container').classList.add('hidden');
}

function copyGcashNumber() {
    const gcashNum = document.getElementById('inn-gcash-number').innerText.replace(/-/g, '');
    navigator.clipboard.writeText(gcashNum).then(() => {
        if (typeof showToast === 'function') {
            showToast('GCash Number copied to clipboard!', 'success');
        } else {
            alert('GCash Number copied to clipboard!');
        }
    });
}

function openQrZoomModal() {
    const modal = document.getElementById('qr-zoom-modal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeQrZoomModal() {
    const modal = document.getElementById('qr-zoom-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

/**
 * Submit Reservation Form
 */
function submitReservation() {
    const roomId = document.getElementById('book-room-id').value;
    const guestName = document.getElementById('book-guest-name').value.trim();
    const guestEmail = document.getElementById('book-guest-email').value.trim();
    const guestPhone = document.getElementById('book-guest-phone').value.trim();
    const checkin = document.getElementById('book-checkin-date').value;
    const checkout = document.getElementById('book-checkout-date').value;
    const termsCheck = document.getElementById('book-terms-check').checked;

    if (!roomId) {
        if (typeof showToast === 'function') showToast('Please select a room to book', 'error');
        else alert('Please select a room to book');
        return;
    }

    if (!guestName || !guestEmail || !guestPhone || !checkin || !checkout) {
        if (typeof showToast === 'function') showToast('Please complete all required guest and date fields', 'error');
        else alert('Please complete all required guest and date fields');
        return;
    }

    if (!termsCheck) {
        if (typeof showToast === 'function') showToast('Please accept the hostel terms & cancellation policy', 'error');
        else alert('Please accept the hostel terms & cancellation policy');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'create');
    formData.append('room_id', roomId);
    formData.append('guest_name', guestName);
    formData.append('guest_email', guestEmail);
    formData.append('guest_phone', guestPhone);
    formData.append('check_in', checkin);
    formData.append('check_out', checkout);
    formData.append('payment_method', selectedPathway);

    if (selectedPathway === 'full_gcash' || selectedPathway === 'deposit_gcash') {
        const gcashRef = document.getElementById('book-gcash-ref').value.trim();
        if (!gcashRef || gcashRef.length < 10) {
            if (typeof showToast === 'function') showToast('Please enter a valid GCash Reference Number (min 10 digits)', 'error');
            else alert('Please enter a valid GCash Reference Number (min 10 digits)');
            return;
        }
        if (!selectedReceiptFile) {
            if (typeof showToast === 'function') showToast('Please upload your GCash transfer receipt screenshot', 'error');
            else alert('Please upload your GCash transfer receipt screenshot');
            return;
        }
        formData.append('gcash_reference_no', gcashRef);
        formData.append('receipt_image', selectedReceiptFile);
    } else if (selectedPathway === 'pay_at_hotel') {
        const arrivalTime = document.getElementById('book-expected-arrival').value;
        formData.append('expected_arrival_time', arrivalTime);
    }

    const submitBtn = document.getElementById('submit-booking-btn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Placing Reservation...`;

    fetch('actions/book_action.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.booking) {
            showSuccessModal(data.booking);
        } else {
            if (typeof showToast === 'function') {
                showToast(data.message || 'Failed to place booking', 'error');
            } else {
                alert(data.message || 'Failed to place booking');
            }
        }
    })
    .catch(err => {
        console.error('Booking submission error:', err);
        if (typeof showToast === 'function') {
            showToast('Network error while placing reservation', 'error');
        } else {
            alert('Network error while placing reservation');
        }
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = `<i class="fas fa-lock"></i> Confirm & Place Reservation`;
    });
}

/**
 * Show Booking Success Modal with Active Hold Countdown Timer
 */
function showSuccessModal(booking) {
    document.getElementById('success-ref-id').innerText = booking.ref_id;
    document.getElementById('success-room-name').innerText = booking.room_name;
    document.getElementById('success-dates').innerText = `${booking.check_in} → ${booking.check_out} (${booking.nights} night${booking.nights > 1 ? 's' : ''})`;
    
    let methodText = 'Full Payment (GCash)';
    if (booking.payment_method === 'deposit_gcash') methodText = '30% Deposit (GCash)';
    else if (booking.payment_method === 'pay_at_hotel') methodText = 'Pay at Hotel (Arrival Hold)';
    document.getElementById('success-payment-method').innerText = methodText;
    document.getElementById('success-total').innerText = `₱${parseFloat(booking.total_amount).toFixed(2)}`;

    // Start Live Countdown Timer
    if (booking.expires_at) {
        startHoldCountdown(booking.expires_at);
        document.getElementById('success-hold-timer-box').classList.remove('hidden');
    } else {
        document.getElementById('success-hold-timer-box').classList.add('hidden');
    }

    const modal = document.getElementById('booking-success-modal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function startHoldCountdown(expiresAt) {
    if (holdCountdownInterval) clearInterval(holdCountdownInterval);

    const target = new Date(expiresAt).getTime();
    const clockElem = document.getElementById('hold-countdown-clock');

    function update() {
        const now = new Date().getTime();
        const diff = target - now;

        if (diff <= 0) {
            clearInterval(holdCountdownInterval);
            if (clockElem) clockElem.innerText = '00:00:00 (EXPIRED)';
            return;
        }

        const hours = Math.floor(diff / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        const pad = (n) => String(n).padStart(2, '0');
        if (clockElem) {
            clockElem.innerText = `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
        }
    }

    update();
    holdCountdownInterval = setInterval(update, 1000);
}

function closeSuccessModal() {
    if (holdCountdownInterval) clearInterval(holdCountdownInterval);
    const modal = document.getElementById('booking-success-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    window.location.reload();
}

