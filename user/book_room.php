  <!-- ================= ROOMS PAGE ================= -->
        <div id="rooms" class="page-section">
            <div class="bg-brand-dark text-white py-16 text-center">
                <h1 class="text-4xl font-serif font-bold">Our Accommodations</h1>
                <p class="mt-4 text-brand-sand">Choose the perfect space for your relaxation</p>
            </div>
            
            <div class="max-w-7xl mx-auto px-4 py-12">
                <!-- Date Filter for Availability -->
                <div class="bg-white p-6 rounded-xl shadow-lg -mt-24 relative z-10 mb-12 border border-gray-100">
                    <form id="check-availability-form" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Check-In</label>
                            <input type="date" id="check-in-date" class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-brand-blue outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Check-Out</label>
                            <input type="date" id="check-out-date" class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-brand-blue outline-none">
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label class="block text-sm font-bold text-gray-700">Guests</label>
                                <span id="guest-count-label" class="text-xs font-bold text-brand-blue bg-blue-50 px-2 py-0.5 rounded-full border border-blue-100">1 Guest (Max 5)</span>
                            </div>
                            <div class="border rounded-lg p-2 bg-white shadow-sm hover:border-brand-blue transition focus-within:border-brand-blue focus-within:ring-2 focus-within:ring-brand-blue/20">
                                <!-- Number Input with +/- Stepper buttons -->
                                <div class="flex items-center justify-between gap-1 mb-1.5 bg-gray-50 rounded-md p-1 border border-gray-200">
                                    <button type="button" id="guest-decrease-btn" class="w-7 h-7 rounded bg-white hover:bg-brand-blue hover:text-white text-gray-700 font-bold transition flex items-center justify-center cursor-pointer shadow-sm border border-gray-200 disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:bg-white disabled:hover:text-gray-700" title="Decrease guests">
                                        <i class="fas fa-minus text-[10px]"></i>
                                    </button>
                                    <div class="flex items-center justify-center gap-1.5 flex-1">
                                        <i class="fas fa-user-friends text-brand-blue text-xs"></i>
                                        <input type="number" id="guest-count" name="guest_count" min="1" max="5" value="1" 
                                               class="w-10 text-center font-bold text-gray-800 text-sm outline-none bg-transparent p-0" 
                                               title="Enter number of guests (1 to 5)">
                                    </div>
                                    <button type="button" id="guest-increase-btn" class="w-7 h-7 rounded bg-white hover:bg-brand-blue hover:text-white text-gray-700 font-bold transition flex items-center justify-center cursor-pointer shadow-sm border border-gray-200 disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:bg-white disabled:hover:text-gray-700" title="Increase guests">
                                        <i class="fas fa-plus text-[10px]"></i>
                                    </button>
                                </div>
                                <!-- Draggable Range Slider (1 to 5) -->
                                <div class="px-1 pt-0.5">
                                    <input type="range" id="guest-range" min="1" max="5" value="1" step="1" 
                                           class="w-full h-1.5 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-sky-600 hover:accent-sky-700 transition" 
                                           title="Drag to select number of guests (1-5)">
                                    <div class="flex justify-between text-[10px] font-bold text-gray-400 px-0.5 mt-0.5 select-none">
                                        <span>1</span>
                                        <span>2</span>
                                        <span>3</span>
                                        <span>4</span>
                                        <span>5</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="bg-brand-blue hover:bg-sky-600 text-white font-bold py-3 rounded-lg transition shadow-md">
                            Check Availability
                        </button>
                    </form>
                </div>
                
                <!-- Rooms Grid -->
                <?php include "user/rooms.php"; ?>
            </div>
        </div>
 
 
 
 <!-- ================= BOOKING SUMMARY ================= -->
        <div id="booking-summary" class="page-section">
            <div class="max-w-4xl mx-auto px-4 py-12">
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-brand-blue p-6 text-white flex justify-between items-center">
                        <h2 class="text-2xl font-bold">Booking Confirmation</h2>
                        <button onclick="router('rooms')" class="text-sm hover:underline"><i class="fas fa-arrow-left"></i> Back</button>
                    </div>
                    <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Stay Details</h3>
                            <div id="booking-details-content" class="space-y-3 text-gray-600">
                                <!-- Filled by JS -->
                            </div>
                            
                            <h3 class="text-lg font-bold text-gray-800 mt-8 mb-4 border-b pb-2">Guest Information</h3>
                            <form id="booking-form" class="space-y-4">
                                <div>
                                    <label class="block text-sm">Full Name</label>
                                    <input type="text" id="book-name" required class="w-full border rounded p-2">
                                </div>
                                <div>
                                    <label class="block text-sm">Email</label>
                                    <input type="email" id="book-email" required class="w-full border rounded p-2">
                                </div>
                                <div>
                                    <label class="block text-sm">Special Requests</label>
                                    <textarea id="book-requests" class="w-full border rounded p-2" rows="2"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold mb-2">Payment Method</label>
                                    <div class="flex flex-col space-y-2">

                                        <label class="flex items-center space-x-2 border p-3 rounded cursor-pointer hover:bg-gray-50">
                                            <input type="radio" name="payment" value="GCash">
                                            <i class="fas fa-wallet text-brand-blue"></i>
                                            <span>GCash / E-Wallet</span>
                                        </label>
                                        <label class="flex items-center space-x-2 border p-3 rounded cursor-pointer hover:bg-gray-50">
                                            <input type="radio" name="payment" value="Pay at Hotel" checked>
                                            <i class="fas fa-store text-brand-blue"></i>
                                            <span>Pay at Hotel</span>
                                        </label>
                                    </div>
                                </div>
                                <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-4 rounded-lg mt-4 shadow-lg transition">
                                    Confirm Reservation
                                </button>
                            </form>
                        </div>
                        <div class="bg-slate-50 p-6 rounded-xl h-fit">
                            <img id="summary-room-img" src="" class="w-full h-48 object-cover rounded-lg mb-4" alt="Room">
                            <h3 id="summary-room-name" class="text-xl font-bold text-brand-dark"></h3>
                            <p id="summary-room-desc" class="text-sm text-gray-500 mt-2"></p>
                            <div class="mt-6 border-t pt-4">
                                <div class="flex justify-between mb-2">
                                    <span>Price per night</span>
                                    <span id="summary-price" class="font-bold"></span>
                                </div>
                                <div class="flex justify-between mb-2">
                                    <span>Nights</span>
                                    <span id="summary-nights"></span>
                                </div>
                                <div class="flex justify-between mb-2">
                                    <span>Taxes & Fees</span>
                                <span id="summary-tax">₱15.00</span>
                                </div>
                                <div class="flex justify-between mt-4 text-xl font-bold text-brand-blue border-t pt-4">
                                    <span>Total</span>
                                    <span id="summary-total"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>