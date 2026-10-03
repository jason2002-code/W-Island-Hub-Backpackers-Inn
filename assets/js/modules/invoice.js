/**
 * INVOICE & VOUCHER MODULE
 * Manages the official booking confirmation voucher modal, print stylesheets,
 * and thermal/standard printing.
 */

/**
 * Displays the official booking voucher / invoice modal upon successful reservation creation.
 * 
 * @param {string} refId - Booking reference code (e.g. 'WIH-XXXXXX').
 * @param {Object} bookingDetails - Pending booking object (roomName, nights, checkIn, checkOut, total, etc.).
 * @param {Object} [apiResponse={}] - Server response payload including paid_amount, balance_due.
 */
function showInvoiceModal(refId, bookingDetails, apiResponse = {}) {
    const modal = document.createElement('div');
    modal.id = 'invoice-modal';
    modal.className = 'fixed inset-0 bg-black/70 flex items-center justify-center z-[100] p-4';
    
    const paymentMethodEl = document.querySelector('input[name="payment"]:checked');
    const paymentMethodVal = apiResponse.payment_method || (paymentMethodEl ? paymentMethodEl.value : 'paymongo');
    const dateNow = new Date().toLocaleDateString();

    const isDorm = bookingDetails.isDorm || (bookingDetails.roomId == 5) || (bookingDetails.roomName && bookingDetails.roomName.toLowerCase().includes('room 5'));
    const bedsCount = bookingDetails.bedsCount || apiResponse.beds_count || (isDorm ? (bookingDetails.adults || 1) : 1);

    const isHotelPayment = (paymentMethodVal === 'pay_at_hotel' || (typeof paymentMethodVal === 'string' && (paymentMethodVal.toLowerCase().includes('hotel') || paymentMethodVal.toLowerCase().includes('deposit'))) || apiResponse.booking_status === 'deposit_paid' || apiResponse.is_deposit);
    
    let paymentLabel = 'Online Payment (PayMongo Full)';
    let statusBadge = '<span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-full font-bold text-xs"><i class="fas fa-check-circle mr-1"></i> Confirmed (100% Paid)</span>';
    let paidAmount = apiResponse.paid_amount !== undefined ? apiResponse.paid_amount : (isHotelPayment ? Math.round(bookingDetails.total * 0.20 * 100) / 100 : bookingDetails.total);
    let balanceDue = apiResponse.balance_due !== undefined ? apiResponse.balance_due : (isHotelPayment ? Math.round((bookingDetails.total - paidAmount) * 100) / 100 : 0);

    if (isHotelPayment) {
        paymentLabel = 'Pay at Hotel (20% Deposit Online)';
        statusBadge = '<span class="px-2.5 py-1 bg-blue-100 text-blue-800 rounded-full font-bold text-xs"><i class="fas fa-shield-alt mr-1"></i> Confirmed (20% Deposit Paid)</span>';
    } else {
        paymentLabel = 'Online Payment (PayMongo Full)';
        statusBadge = '<span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-full font-bold text-xs"><i class="fas fa-check-circle mr-1"></i> Confirmed (100% Paid)</span>';
    }

    modal.innerHTML = `
        <div class="bg-white rounded-3xl w-full max-w-2xl max-h-[90vh] flex flex-col shadow-2xl overflow-hidden border border-gray-100">
            <div class="p-6 border-b border-white/10 flex justify-between items-center bg-gradient-to-r from-brand-dark via-slate-900 to-brand-blue text-white">
                <div>
                    <span class="text-xs uppercase font-bold text-teal-300 tracking-wider">Official Booking Voucher</span>
                    <h3 class="text-2xl font-serif font-bold mt-0.5">Reservation Confirmation</h3>
                </div>
                <button onclick="closeInvoiceModal()" class="text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-xl transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <div id="invoice-content" class="p-6 sm:p-8 overflow-y-auto flex-1 bg-white text-gray-800 space-y-6">
                <div class="flex flex-col sm:flex-row justify-between items-start gap-4 border-b border-gray-100 pb-5">
                    <div class="flex items-start gap-3">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-teal-500 to-sky-600 text-white flex items-center justify-center font-bold text-lg shadow-sm shrink-0">
                            <i class="fas fa-hotel"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-serif font-bold text-gray-900 leading-tight">W Island Backpackers Inn</h2>
                            <p class="text-[11px] text-teal-700 font-semibold tracking-wide">W Island Hub Hospitality Services</p>
                            <p class="text-[11px] text-gray-600 mt-0.5"><i class="fas fa-map-marker-alt text-red-500 mr-1"></i>Dao, Dauis - Panglao Center Road, Dauis, Panglao Island, Bohol 6339, Philippines</p>
                            <p class="text-[10px] text-gray-500">DOT Accr. No.: DOT-R7-ACC-2024-0318 &bull; BIR TIN: 432-876-543-00000 &bull; LGU Permit: BP-2024-DAUIS-0521</p>
                        </div>
                    </div>
                    <div class="text-left sm:text-right shrink-0">
                        <span class="inline-block px-2.5 py-0.5 rounded bg-teal-50 text-teal-800 font-mono font-bold text-xs uppercase tracking-wider mb-1 border border-teal-200">
                            OFFICIAL VOUCHER
                        </span>
                        <div class="font-mono text-sm font-bold text-gray-900">${refId}</div>
                        <div class="text-[10px] text-gray-400 mt-0.5">Issued: ${dateNow}</div>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100 grid grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Guest Information:</span>
                        <strong class="text-gray-800 text-sm block">${document.getElementById('book-name')?.value || apiResponse.guest_name || bookingDetails.guestName || 'Guest'}</strong>
                        <span class="text-gray-500 block">${document.getElementById('book-email')?.value || apiResponse.guest_email || bookingDetails.guestEmail || ''}</span>
                        <div class="text-[11px] text-brand-blue font-semibold mt-1">
                            <i class="fas ${isDorm ? 'fa-bed' : 'fa-users'} mr-1"></i>
                            ${isDorm ? `${bedsCount} Bed${bedsCount > 1 ? 's' : ''} in Shared Dormitory` : `${bookingDetails.guests || 1} Guests (${bookingDetails.adults || 1} Adult${(bookingDetails.adults || 1) > 1 ? 's' : ''}${bookingDetails.children > 0 ? `, ${bookingDetails.children} Child${bookingDetails.children > 1 ? 'ren' : ''}` : ''}${bookingDetails.childrenAges && bookingDetails.childrenAges.length > 0 ? ` · Ages: ${bookingDetails.childrenAges.map(a => a == 0 ? '0' : a).join(', ')}` : ''})`}
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Payment Pathway:</span>
                        <strong class="text-gray-800 block text-xs mb-1">${paymentLabel}</strong>
                        <div>${statusBadge}</div>
                    </div>
                </div>

                ${isHotelPayment ? `
                    <div class="p-3 bg-blue-50/70 border border-blue-200 rounded-2xl text-xs text-brand-dark flex items-start gap-2.5">
                        <i class="fas fa-shield-alt text-brand-blue text-sm mt-0.5 shrink-0"></i>
                        <div>
                            <span class="font-bold block text-blue-900">20% Online Deposit Verified &bull; Reservation Secured Against No-Show</span>
                            <span class="text-[11px] text-blue-800">Your room is guaranteed. Please pay the remaining 80% balance (₱${parseFloat(balanceDue).toFixed(2)}) upon arrival at the hotel front desk.</span>
                        </div>
                    </div>
                ` : ''}

                <div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-2">Reservation Stay:</span>
                    <div class="bg-white rounded-2xl p-4 border border-gray-200 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                        <div>
                            <span class="text-gray-400 block text-[10px]">Room / Beds</span>
                            <strong class="text-gray-800">${!isDorm && bookingDetails.roomsCount > 1 ? `${bookingDetails.roomsCount}x ` : ''}${bookingDetails.roomName}</strong>
                            ${isDorm ? `<span class="text-[10px] text-teal-600 block font-semibold">(${bedsCount} Bed${bedsCount > 1 ? 's' : ''} Reserved)</span>` : (bookingDetails.roomsCount > 1 ? `<span class="text-[10px] text-brand-blue block font-semibold">(${bookingDetails.roomsCount} Rooms)</span>` : '')}
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[10px]">Duration</span>
                            <strong class="text-gray-800">${bookingDetails.nights} Night(s)</strong>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[10px]">Check-in</span>
                            <strong class="text-gray-800">${bookingDetails.checkIn}</strong>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[10px]">Check-out</span>
                            <strong class="text-gray-800">${bookingDetails.checkOut}</strong>
                        </div>
                    </div>
                </div>

                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-gray-200 text-gray-500">
                            <th class="py-2.5 font-bold">Item Description</th>
                            <th class="py-2.5 font-bold text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-gray-100">
                            <td class="py-3">
                                <div class="font-bold text-gray-800">${isDorm ? 'Dormitory Bed Accommodation Rate' : 'Room Accommodation Rate'}</div>
                                <div class="text-[10px] text-gray-400">${isDorm ? `₱460.00 × ${bedsCount} bed(s) × ${bookingDetails.nights} night(s)` : `₱${bookingDetails.pricePerNight.toFixed(2)} x ${bookingDetails.nights} night(s)${bookingDetails.roomsCount > 1 ? ` x ${bookingDetails.roomsCount} rooms` : ''}`}</div>
                            </td>
                            <td class="py-3 text-right font-semibold text-gray-700">₱${(isDorm ? (460.00 * bedsCount * bookingDetails.nights) : (bookingDetails.pricePerNight * bookingDetails.nights * (bookingDetails.roomsCount || 1))).toFixed(2)}</td>
                        </tr>
                        <tr class="border-b border-gray-100">
                            <td class="py-3">
                                <div class="font-bold text-gray-800">Taxes, Service & Eco Fees</div>
                            </td>
                            <td class="py-3 text-right font-semibold text-gray-700">₱15.00</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="border-b border-gray-200">
                            <td class="py-3 font-bold text-sm text-gray-800 text-right pr-4">Total Bill:</td>
                            <td class="py-3 font-bold text-base text-gray-900 text-right">₱${bookingDetails.total.toFixed(2)}</td>
                        </tr>
                        <tr class="bg-emerald-50/50">
                            <td class="py-2 font-bold text-xs text-emerald-800 text-right pr-4">${isHotelPayment ? 'Paid Online (20% Security Deposit):' : 'Paid in Full Online (100%):'}</td>
                            <td class="py-2 font-bold text-sm text-emerald-600 text-right">₱${parseFloat(paidAmount).toFixed(2)}</td>
                        </tr>
                        <tr class="bg-blue-50/50">
                            <td class="py-2 font-bold text-xs text-brand-blue text-right pr-4">${isHotelPayment ? 'Remaining Balance at Hotel (80%):' : 'Remaining Balance at Front Desk:'}</td>
                            <td class="py-2 font-bold text-sm text-brand-blue text-right">₱${parseFloat(balanceDue).toFixed(2)}</td>
                        </tr>
                    </tfoot>
                </table>
                
                <div class="pt-4 border-t border-gray-200 space-y-2 text-center text-xs text-gray-600">
                    <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-1 text-[11px] text-gray-700 font-medium bg-gray-50 py-2 px-3 rounded-lg border border-gray-100">
                        <span><i class="fas fa-clock text-teal-600 mr-1"></i>Check-in: <strong>02:00 PM</strong></span>
                        <span><i class="fas fa-sign-out-alt text-teal-600 mr-1"></i>Check-out: <strong>11:00 AM</strong></span>
                        <span><i class="fas fa-phone-alt text-teal-600 mr-1"></i>+63 945 688 2266</span>
                        <span><i class="fas fa-envelope text-teal-600 mr-1"></i>wislandhub2025@gmail.com</span>
                    </div>
                    <p class="text-[11px] text-gray-600">
                        Please present a valid government-issued photo ID along with this voucher at the front desk upon arrival.
                    </p>
                    <p class="text-[10px] text-gray-400 border-t border-gray-100 pt-2">
                        W Island Backpackers Inn &bull; Dauis, Panglao Island, Bohol 6339 &bull; Reg. LGU Dauis (BP-2024-DAUIS-0521) &bull; Subject to standard hostel house rules & RA 10173 Privacy Act.
                    </p>
                </div>
            </div>
            
            <div class="p-5 bg-gray-50 border-t border-gray-100 flex gap-3 justify-end">
                <button onclick="printInvoice()" class="px-5 py-2.5 bg-gray-800 hover:bg-gray-900 text-white rounded-xl font-medium transition shadow-sm flex items-center gap-2 text-xs">
                    <i class="fas fa-print"></i> Print Voucher
                </button>
                <button onclick="closeInvoiceModal()" class="px-6 py-2.5 bg-brand-blue hover:bg-sky-600 text-white rounded-xl font-medium transition shadow-sm text-xs">
                    Done
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

/**
 * Closes the invoice modal and returns user to the home view.
 */
function closeInvoiceModal() {
    const modal = document.getElementById('invoice-modal');
    if (modal) {
        modal.remove();
    }
    if (typeof window.router === 'function') {
        window.router('home');
    }
}

// Open print popup in the same tab using an iframe modal
function openSameTabPrintWindow() {
    let modal = document.getElementById('same-tab-print-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'same-tab-print-modal';
        modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:999999;display:flex;align-items:center;justify-content:center;';
        
        let container = document.createElement('div');
        container.style.cssText = 'width:90%;height:90%;background:#fff;border-radius:8px;box-shadow:0 10px 40px rgba(0,0,0,0.3);display:flex;flex-direction:column;overflow:hidden;';
        
        let header = document.createElement('div');
        header.style.cssText = 'padding:12px 20px;background:#f8fafc;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;';
        header.innerHTML = '<strong style="color:#1e293b;font-family:sans-serif;font-size:16px;">Print Preview</strong><button onclick="document.getElementById(\'same-tab-print-modal\').style.display=\'none\'" style="background:#ef4444;color:#fff;border:none;padding:6px 14px;border-radius:6px;cursor:pointer;font-weight:bold;">Close</button>';
        
        let iframe = document.createElement('iframe');
        iframe.id = 'same-tab-print-iframe';
        iframe.style.cssText = 'flex-grow:1;border:none;width:100%;background:#fff;';
        
        container.appendChild(header);
        container.appendChild(iframe);
        modal.appendChild(container);
        document.body.appendChild(modal);
    }
    
    modal.style.display = 'flex';
    let iframe = document.getElementById('same-tab-print-iframe');
    let win = iframe.contentWindow;
    
    win.document.open();
    win.document.write('');
    win.document.close();
    
    return win;
}

/**
 * Opens a clean pop-up print window containing the invoice voucher and triggers the browser print dialog.
 */
function printInvoice() {
    const content = document.getElementById('invoice-content');
    if (!content) return;
    
    const printWindow = openSameTabPrintWindow();
    if (!printWindow) {
        if (typeof window.showToast === 'function') {
            window.showToast('Popup was blocked by your browser. Please allow popups to print voucher.', 'warning');
        } else {
            window.print();
        }
        return;
    }
    printWindow.document.write(`
        <html>
            <head>
                <title>Official Booking Voucher - W Island Backpackers Inn, Dauis, Panglao, Bohol</title>
                <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                <style>
                    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; padding: 24px; color: #1e293b; }
                    @media print {
                        body { padding: 12px; }
                        .no-print { display: none !important; }
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                </style>
            </head>
            <body>
                ${content.innerHTML}
                <script>
                    window.onload = function() { 
                        setTimeout(function() {
                            window.print(); 
                            window.close(); 
                        }, 500);
                    }
                </script>
            </body>
        </html>
    `);
    printWindow.document.close();
}

// Global window bindings for inline HTML and cross-module access
window.showInvoiceModal = showInvoiceModal;
window.closeInvoiceModal = closeInvoiceModal;
window.printInvoice = printInvoice;

