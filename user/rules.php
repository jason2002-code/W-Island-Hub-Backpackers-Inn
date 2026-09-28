<div id="rules" class="page-section">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
        
        <!-- Header Hero -->
        <div class="text-center max-w-3xl mx-auto mb-12">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-brand-blue/10 text-brand-blue text-xs font-bold uppercase tracking-wider mb-4 border border-brand-blue/20">
                <i class="fas fa-shield-alt"></i> Official Property Guidelines & Policies
            </div>
            <h1 class="text-4xl sm:text-5xl font-serif font-bold text-slate-900 tracking-tight mb-4">
                House Rules & Island Etiquette
            </h1>
            <p class="text-base sm:text-lg text-slate-600 leading-relaxed font-light">
                Welcome to <strong>W Island Hub Backpackers Inn</strong>! To provide a safe, respectful, and relaxing community for all travelers, please observe our house rules during your stay.
            </p>
            <div class="w-20 h-1 bg-gradient-to-r from-brand-blue to-teal-400 mx-auto mt-6 rounded-full"></div>
        </div>

        <!-- Quick Reference Pillars (4-Card Highlights) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
            <!-- 1. Check-In & Out -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-brand-blue flex items-center justify-center text-lg mb-3">
                    <i class="fas fa-clock"></i>
                </div>
                <h4 class="font-bold text-slate-900 text-sm mb-1">Check-In & Check-Out</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Check-in: <strong>2:00 PM</strong> onwards<br>
                    Check-out: By <strong>11:00 AM</strong><br>
                    <span class="text-[11px] text-teal-600 font-semibold">Free early luggage storage</span>
                </p>
            </div>

            <!-- 2. Quiet Hours -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition">
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg mb-3">
                    <i class="fas fa-moon"></i>
                </div>
                <h4 class="font-bold text-slate-900 text-sm mb-1">Quiet Hours</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    <strong>10:00 PM – 7:00 AM</strong><br>
                    Respect fellow travelers' sleep. Please use headphones for all personal audio.
                </p>
            </div>

            <!-- 3. Smoke-Free Indoors -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition">
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg mb-3">
                    <i class="fas fa-smoking-ban"></i>
                </div>
                <h4 class="font-bold text-slate-900 text-sm mb-1">100% Smoke-Free</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Strictly <strong>NO smoking or vaping</strong> inside dorms or rooms. Designated outdoor zones only.
                </p>
            </div>

            <!-- 4. Multi-Tier Booking Hold -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg mb-3">
                    <i class="fas fa-receipt"></i>
                </div>
                <h4 class="font-bold text-slate-900 text-sm mb-1">Official Payments</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    <strong>100% Online Payment</strong> (Instant Lock) or <strong>Pay-at-Hotel (20% Online Deposit)</strong> to secure against no-shows.
                </p>
            </div>
        </div>

        <!-- Interactive Filter & Search Bar -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs mb-10">
            <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                <!-- Filter Pills -->
                <div class="flex flex-wrap gap-2 w-full md:w-auto" id="rule-category-filters">
                    <button type="button" onclick="filterRuleCategory('all', this)" class="rule-filter-btn active px-4 py-2 rounded-xl text-xs font-bold bg-brand-blue text-white shadow-xs transition">
                        All Guidelines
                    </button>
                    <button type="button" onclick="filterRuleCategory('checkin', this)" class="rule-filter-btn px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                        Check-in & Payments
                    </button>
                    <button type="button" onclick="filterRuleCategory('dorm', this)" class="rule-filter-btn px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                        Dorm Etiquette
                    </button>
                    <button type="button" onclick="filterRuleCategory('conduct', this)" class="rule-filter-btn px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                        Conduct & Visitors
                    </button>
                    <button type="button" onclick="filterRuleCategory('eco', this)" class="rule-filter-btn px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                        Eco & Island Living
                    </button>
                    <button type="button" onclick="filterRuleCategory('safety', this)" class="rule-filter-btn px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                        Cancellations & Safety
                    </button>
                </div>

                <!-- Instant Search Input -->
                <div class="relative w-full md:w-72 flex-shrink-0">
                    <i class="fas fa-search absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                    <input type="text" id="rule-search-input" oninput="searchRules(this.value)" placeholder="Search rules (e.g. WiFi, smoking, deposit)..." class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition">
                </div>
            </div>
        </div>

        <!-- Rules Content Sections -->
        <div id="rules-cards-container" class="space-y-8">

            <!-- SECTION 1: Check-in, Registration & Payment Policies -->
            <section class="rule-section bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs" data-category="checkin">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-4 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-brand-blue flex items-center justify-center font-bold text-lg">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-serif font-bold text-slate-900">Check-In, Registration & Payment Policies</h2>
                        <p class="text-xs text-slate-500">Government compliance, identification, and booking hold policies</p>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-6 text-sm text-slate-600">
                    <div class="space-y-4">
                        <div class="flex items-start gap-3 bg-slate-50/80 p-4 rounded-2xl border border-slate-100">
                            <i class="fas fa-passport text-brand-blue text-base mt-0.5 flex-shrink-0"></i>
                            <div>
                                <strong class="text-slate-900 block font-semibold text-xs uppercase tracking-wide">Valid Identification Required</strong>
                                <span class="text-xs leading-relaxed text-slate-600">All guests must present a valid government-issued ID or international passport upon arrival for official Philippine tourism registry and guest safety.</span>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 bg-slate-50/80 p-4 rounded-2xl border border-slate-100">
                            <i class="fas fa-clock text-brand-blue text-base mt-0.5 flex-shrink-0"></i>
                            <div>
                                <strong class="text-slate-900 block font-semibold text-xs uppercase tracking-wide">Timelines & Baggage Storage</strong>
                                <span class="text-xs leading-relaxed text-slate-600">Check-in begins at <strong>2:00 PM</strong>. Check-out is by <strong>11:00 AM</strong>. Arriving early? Complimentary luggage drop-off is available at the front desk starting at 8:00 AM so you can explore the beach immediately.</span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-start gap-3 bg-slate-50/80 p-4 rounded-2xl border border-slate-100">
                            <i class="fas fa-credit-card text-blue-600 text-base mt-0.5 flex-shrink-0"></i>
                            <div>
                                <strong class="text-slate-900 block font-semibold text-xs uppercase tracking-wide">Option A: 100% Full Payment (Instant Priority Lock)</strong>
                                <span class="text-xs leading-relaxed text-slate-600">Pay 100% online via PayMongo (GCash, Maya, GrabPay, Credit/Debit Cards). Your reservation is instantly fully confirmed with zero balance due upon check-in.</span>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 bg-slate-50/80 p-4 rounded-2xl border border-slate-100">
                            <i class="fas fa-hotel text-emerald-600 text-base mt-0.5 flex-shrink-0"></i>
                            <div>
                                <strong class="text-slate-900 block font-semibold text-xs uppercase tracking-wide">Option B: Pay at Hotel (20% Online Deposit)</strong>
                                <span class="text-xs leading-relaxed text-slate-600">Secure your room by paying a <strong>20% online deposit</strong> via PayMongo at booking time to protect against no-shows. The remaining <strong>80% balance</strong> is settled at the front desk upon check-in.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION 2: Shared Dormitory & Room Etiquette -->
            <section class="rule-section bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs" data-category="dorm">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-4 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-lg">
                        <i class="fas fa-bed"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-serif font-bold text-slate-900">Shared Dormitory & Accommodation Etiquette</h2>
                        <p class="text-xs text-slate-500">Creating a courteous, clean, and restful sanctuary for all bunkmates</p>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-6 text-sm text-slate-600">
                    <div class="space-y-4">
                        <div class="flex items-start gap-3 bg-slate-50/80 p-4 rounded-2xl border border-slate-100">
                            <i class="fas fa-volume-mute text-purple-600 text-base mt-0.5 flex-shrink-0"></i>
                            <div>
                                <strong class="text-slate-900 block font-semibold text-xs uppercase tracking-wide">Quiet Hours (10:00 PM – 7:00 AM)</strong>
                                <span class="text-xs leading-relaxed text-slate-600">Dorms are dedicated sleep zones. Turn off main overhead lights after 10 PM and use personal reading lamps. Conversations should be taken to the outdoor terrace or garden lounge. Always use headphones for music and videos.</span>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 bg-slate-50/80 p-4 rounded-2xl border border-slate-100">
                            <i class="fas fa-tag text-purple-600 text-base mt-0.5 flex-shrink-0"></i>
                            <div>
                                <strong class="text-slate-900 block font-semibold text-xs uppercase tracking-wide">Assigned Bunk Integrity</strong>
                                <span class="text-xs leading-relaxed text-slate-600">Please occupy strictly the bed number assigned to you upon check-in. Do not place backpacks, wet clothes, or personal items on unoccupied beds, as they are sanitized and prepared for arriving guests.</span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-start gap-3 bg-slate-50/80 p-4 rounded-2xl border border-slate-100">
                            <i class="fas fa-utensils text-purple-600 text-base mt-0.5 flex-shrink-0"></i>
                            <div>
                                <strong class="text-slate-900 block font-semibold text-xs uppercase tracking-wide">No Messy Meals in Sleeping Quarters</strong>
                                <span class="text-xs leading-relaxed text-slate-600">Bottled water and light dry snacks are permitted in rooms. To prevent island ants and pests, all cooked food and takeaway meals must be enjoyed in the communal dining lounge or shared kitchen.</span>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 bg-slate-50/80 p-4 rounded-2xl border border-slate-100">
                            <i class="fas fa-tshirt text-purple-600 text-base mt-0.5 flex-shrink-0"></i>
                            <div>
                                <strong class="text-slate-900 block font-semibold text-xs uppercase tracking-wide">Damp Beachwear & Towels</strong>
                                <span class="text-xs leading-relaxed text-slate-600">Please do not hang wet swimwear or towels over wooden railings or air conditioning vents. Dedicated clotheslines and drying racks are provided in the outdoor sunshine patio.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION 3: Code of Conduct & Visitors Policy -->
            <section class="rule-section bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs" data-category="conduct">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-4 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-lg">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-serif font-bold text-slate-900">Code of Conduct & Outside Visitors</h2>
                        <p class="text-xs text-slate-500">Ensuring safety, mutual respect, and peaceful coexistence</p>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-6 text-sm text-slate-600">
                    <div class="space-y-4">
                        <div class="flex items-start gap-3 bg-slate-50/80 p-4 rounded-2xl border border-slate-100">
                            <i class="fas fa-user-lock text-amber-600 text-base mt-0.5 flex-shrink-0"></i>
                            <div>
                                <strong class="text-slate-900 block font-semibold text-xs uppercase tracking-wide">Registered Guests Only in Rooms</strong>
                                <span class="text-xs leading-relaxed text-slate-600">For resident privacy and safety, unregistered outside visitors are strictly prohibited from entering dormitory rooms or private guest suites. Non-staying guests are welcome in the lobby and outdoor café until <strong>9:00 PM</strong>.</span>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 bg-slate-50/80 p-4 rounded-2xl border border-slate-100">
                            <i class="fas fa-paw text-amber-600 text-base mt-0.5 flex-shrink-0"></i>
                            <div>
                                <strong class="text-slate-900 block font-semibold text-xs uppercase tracking-wide">No Pets Policy</strong>
                                <span class="text-xs leading-relaxed text-slate-600">In adherence to local island wildlife protection policies and to guarantee an allergen-free environment for all backpackers, pets and animals are not permitted on the property.</span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-start gap-3 bg-slate-50/80 p-4 rounded-2xl border border-slate-100">
                            <i class="fas fa-ban text-rose-600 text-base mt-0.5 flex-shrink-0"></i>
                            <div>
                                <strong class="text-slate-900 block font-semibold text-xs uppercase tracking-wide">Zero Tolerance on Illicit Substances</strong>
                                <span class="text-xs leading-relaxed text-slate-600">Possession or consumption of illegal drugs, narcotics, firearms, or hazardous weapons is strictly prohibited. Immediate eviction without refund and police reporting will be enforced.</span>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 bg-slate-50/80 p-4 rounded-2xl border border-slate-100">
                            <i class="fas fa-heart text-emerald-600 text-base mt-0.5 flex-shrink-0"></i>
                            <div>
                                <strong class="text-slate-900 block font-semibold text-xs uppercase tracking-wide">Community Respect & Anti-Harassment</strong>
                                <span class="text-xs leading-relaxed text-slate-600">We take pride in our inclusive, multicultural hostel family. Any form of harassment, discrimination, verbal hostility, or disrespectful behavior toward staff or fellow guests will result in prompt removal.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION 4: Eco & Island Sustainability -->
            <section class="rule-section bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs" data-category="eco">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-4 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center font-bold text-lg">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-serif font-bold text-slate-900">Island Eco-Conservation & Sustainable Living</h2>
                        <p class="text-xs text-slate-500">Protecting the pristine natural beauty and fragile ecosystems of Palawan & Bohol</p>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-6 text-sm text-slate-600">
                    <div class="space-y-4">
                        <div class="flex items-start gap-3 bg-slate-50/80 p-4 rounded-2xl border border-slate-100">
                            <i class="fas fa-bolt text-teal-600 text-base mt-0.5 flex-shrink-0"></i>
                            <div>
                                <strong class="text-slate-900 block font-semibold text-xs uppercase tracking-wide">Energy & Power Mindfulness</strong>
                                <span class="text-xs leading-relaxed text-slate-600">Island electricity grids are delicate and precious. Please help us conserve by turning off air conditioning units, fans, and room lights whenever leaving your room for island exploration, beach visits, or meals.</span>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 bg-slate-50/80 p-4 rounded-2xl border border-slate-100">
                            <i class="fas fa-tint text-teal-600 text-base mt-0.5 flex-shrink-0"></i>
                            <div>
                                <strong class="text-slate-900 block font-semibold text-xs uppercase tracking-wide">Water Conservation</strong>
                                <span class="text-xs leading-relaxed text-slate-600">Fresh water is supplied through island cisterns and deep wells. Please keep shower times conscious and report any dripping faucets or running toilets to front desk immediately.</span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-start gap-3 bg-slate-50/80 p-4 rounded-2xl border border-slate-100">
                            <i class="fas fa-recycle text-teal-600 text-base mt-0.5 flex-shrink-0"></i>
                            <div>
                                <strong class="text-slate-900 block font-semibold text-xs uppercase tracking-wide">3-Way Waste Segregation</strong>
                                <span class="text-xs leading-relaxed text-slate-600">Please sort your refuse into our clearly labeled recycling receptacles: <em>Biodegradable</em> (food scraps), <em>Recyclables</em> (cans, plastics, glass), and <em>Residual Waste</em>. Refillable water stations are accessible in the lobby.</span>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 bg-slate-50/80 p-4 rounded-2xl border border-slate-100">
                            <i class="fas fa-umbrella-beach text-teal-600 text-base mt-0.5 flex-shrink-0"></i>
                            <div>
                                <strong class="text-slate-900 block font-semibold text-xs uppercase tracking-wide">Rinse Stations & Marine Preservation</strong>
                                <span class="text-xs leading-relaxed text-slate-600">Please rinse sandy feet and snorkel gear at our outdoor foot-wash stations before entering rooms. Never collect or remove corals, shells, or sea life from local beaches.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION 5: Cancellations, Safety & Emergencies -->
            <section class="rule-section bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs" data-category="safety">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-4 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-lg">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-serif font-bold text-slate-900">Booking Cancellations, Safety & Emergency Procedures</h2>
                        <p class="text-xs text-slate-500">Fair cancellation schedules, personal security, and 24/7 hotline directory</p>
                    </div>
                </div>

                <!-- Cancellation Tier Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                    <div class="bg-emerald-50/70 border border-emerald-200 rounded-2xl p-4 text-center">
                        <span class="text-xs uppercase font-bold text-emerald-700 block mb-1">48+ Hours Prior</span>
                        <strong class="text-2xl font-bold text-emerald-900 block mb-1">100% Free</strong>
                        <p class="text-xs text-emerald-700">Full refund or free date modification</p>
                    </div>
                    <div class="bg-amber-50/70 border border-amber-200 rounded-2xl p-4 text-center">
                        <span class="text-xs uppercase font-bold text-amber-700 block mb-1">24 – 48 Hours</span>
                        <strong class="text-2xl font-bold text-amber-900 block mb-1">50% Charge</strong>
                        <p class="text-xs text-amber-700">50% of the first night charge retained</p>
                    </div>
                    <div class="bg-rose-50/70 border border-rose-200 rounded-2xl p-4 text-center">
                        <span class="text-xs uppercase font-bold text-rose-700 block mb-1">&lt;24 Hours / No-Show</span>
                        <strong class="text-2xl font-bold text-rose-900 block mb-1">Non-Refundable</strong>
                        <p class="text-xs text-rose-700">Online payments & 20% deposits forfeited; room released</p>
                    </div>
                </div>

                <!-- Safety & Contacts Detail -->
                <div class="grid md:grid-cols-2 gap-6 text-sm text-slate-600">
                    <div class="space-y-4">
                        <div class="flex items-start gap-3 bg-slate-50/80 p-4 rounded-2xl border border-slate-100">
                            <i class="fas fa-lock text-emerald-600 text-base mt-0.5 flex-shrink-0"></i>
                            <div>
                                <strong class="text-slate-900 block font-semibold text-xs uppercase tracking-wide">Personal Belongings & Security Lockers</strong>
                                <span class="text-xs leading-relaxed text-slate-600">Individual lockers are assigned to every dorm guest. Please secure your passport, electronics, and cash with a padlock. W Island Hub is not liable for unsecured valuables left in open spaces.</span>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 bg-slate-50/80 p-4 rounded-2xl border border-slate-100">
                            <i class="fas fa-cloud-sun-rain text-emerald-600 text-base mt-0.5 flex-shrink-0"></i>
                            <div>
                                <strong class="text-slate-900 block font-semibold text-xs uppercase tracking-wide">Severe Weather & Typhoon Force Majeure</strong>
                                <span class="text-xs leading-relaxed text-slate-600">In the event of official Philippine Coast Guard sea travel bans, flight cancellations, or PAGASA tropical storm warnings, free rescheduling or 12-month travel vouchers are unconditionally honored.</span>
                            </div>
                        </div>
                    </div>

                    <!-- 24/7 Contacts Card -->
                    <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white p-6 rounded-2xl shadow-sm space-y-4">
                        <h4 class="font-bold text-sm text-amber-400 uppercase tracking-wider flex items-center gap-2">
                            <i class="fas fa-phone-volume"></i> 24/7 Concierge & Emergency Hotlines
                        </h4>
                        <div class="space-y-3 text-xs">
                            <div class="flex justify-between items-center py-1.5 border-b border-white/10">
                                <span class="text-slate-400">Front Desk & Reception:</span>
                                <strong class="font-mono text-teal-300 font-bold text-sm">+63 945 688 2266</strong>
                            </div>
                            <div class="flex justify-between items-center py-1.5 border-b border-white/10">
                                <span class="text-slate-400">GCash Billing Hotline:</span>
                                <strong class="font-mono text-blue-300 font-bold text-sm">0975-361-4030</strong>
                            </div>
                            <div class="flex justify-between items-center py-1.5 border-b border-white/10">
                                <span class="text-slate-400">Official Inquiries Email:</span>
                                <strong class="text-slate-200">wislandhub2025@gmail.com</strong>
                            </div>
                            <div class="flex justify-between items-center py-1.5 border-b border-white/10">
                                <span class="text-slate-400">First Aid & Medicine:</span>
                                <span class="text-emerald-300 font-semibold">Available 24/7 at Reception</span>
                            </div>
                            <div class="flex justify-between items-center pt-1">
                                <span class="text-slate-400">Emergency Assembly Point:</span>
                                <span class="text-amber-300 font-semibold">Main Beach Lawn / Front Courtyard</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </div>

        <!-- No Results Search Fallback -->
        <div id="no-rules-found" class="hidden text-center py-16 bg-white rounded-3xl border border-slate-200/80 p-8 shadow-xs">
            <i class="fas fa-search text-3xl text-slate-300 mb-3 block"></i>
            <h4 class="text-base font-bold text-slate-700 mb-1">No matching rules found</h4>
            <p class="text-xs text-slate-500 mb-4">Try searching for keywords like "WiFi", "smoking", "checkin", or "deposit".</p>
            <button type="button" onclick="resetRuleFilters()" class="text-xs font-bold text-brand-blue bg-blue-50 px-4 py-2 rounded-xl hover:bg-blue-100 transition">
                Reset Filter
            </button>
        </div>

        <!-- Bottom CTA -->
        <div class="mt-12 text-center bg-gradient-to-r from-brand-blue/10 via-teal-50 to-emerald-50/50 rounded-3xl p-8 border border-brand-blue/20">
            <h3 class="text-xl sm:text-2xl font-serif font-bold text-slate-900 mb-2">Have questions about our house rules?</h3>
            <p class="text-xs sm:text-sm text-slate-600 max-w-xl mx-auto mb-6">Our concierge team is here 24/7 to make sure your island holiday is effortless, relaxed, and worry-free.</p>
            <div class="flex flex-wrap justify-center items-center gap-3">
                <button type="button" onclick="router('contact')" class="bg-brand-blue hover:bg-sky-600 text-white font-bold text-xs sm:text-sm px-6 py-3 rounded-xl shadow transition flex items-center gap-2">
                    <i class="fas fa-envelope"></i> Message Inn Reception
                </button>
                <button type="button" onclick="router('rooms')" class="bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs sm:text-sm px-6 py-3 rounded-xl border border-slate-300 shadow-xs transition flex items-center gap-2">
                    <i class="fas fa-bed"></i> Browse Accommodations
                </button>
            </div>
        </div>

    </div>
</div>

<script>
/**
 * Interactive House Rules Filtering & Search
 */
function filterRuleCategory(category, buttonEl) {
    // Reset search bar
    const searchInput = document.getElementById('rule-search-input');
    if (searchInput) searchInput.value = '';

    // Update active filter button styling
    document.querySelectorAll('.rule-filter-btn').forEach(btn => {
        btn.classList.remove('bg-brand-blue', 'text-white', 'shadow-xs');
        btn.classList.add('bg-slate-100', 'text-slate-600');
    });
    if (buttonEl) {
        buttonEl.classList.remove('bg-slate-100', 'text-slate-600');
        buttonEl.classList.add('bg-brand-blue', 'text-white', 'shadow-xs');
    }

    const sections = document.querySelectorAll('.rule-section');
    let visibleCount = 0;

    sections.forEach(sec => {
        const secCat = sec.getAttribute('data-category');
        if (category === 'all' || secCat === category) {
            sec.classList.remove('hidden');
            visibleCount++;
        } else {
            sec.classList.add('hidden');
        }
    });

    const noResults = document.getElementById('no-rules-found');
    if (noResults) {
        noResults.classList.toggle('hidden', visibleCount > 0);
    }
}

function searchRules(query) {
    const q = (query || '').toLowerCase().trim();
    const sections = document.querySelectorAll('.rule-section');
    let visibleCount = 0;

    // Reset button states to 'all'
    document.querySelectorAll('.rule-filter-btn').forEach(btn => {
        btn.classList.remove('bg-brand-blue', 'text-white', 'shadow-xs');
        btn.classList.add('bg-slate-100', 'text-slate-600');
    });

    sections.forEach(sec => {
        const text = sec.innerText.toLowerCase();
        if (!q || text.includes(q)) {
            sec.classList.remove('hidden');
            visibleCount++;
        } else {
            sec.classList.add('hidden');
        }
    });

    const noResults = document.getElementById('no-rules-found');
    if (noResults) {
        noResults.classList.toggle('hidden', visibleCount > 0);
    }
}

function resetRuleFilters() {
    const firstBtn = document.querySelector('.rule-filter-btn');
    filterRuleCategory('all', firstBtn);
}
</script>

