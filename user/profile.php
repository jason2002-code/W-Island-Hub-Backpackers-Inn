<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Require user to be logged in as guest
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php#login");
    exit();
}

require_once __DIR__ . '/../config/db.php';
include __DIR__ . '/../includes/header.php'; 

$pdo = getDB();

// Fetch Full User Details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: ../logout.php?type=user");
    exit();
}

// Parse preferences if JSON
$preferences = [];
if (!empty($user['travel_preferences'])) {
    $preferences = json_decode($user['travel_preferences'], true) ?: [];
}

// Fetch Booking History with Room info
$bookingStmt = $pdo->prepare("
    SELECT b.*, r.name as room_name, r.type as room_type, r.image as room_image 
    FROM bookings b 
    LEFT JOIN rooms r ON b.room_id = r.id 
    WHERE b.user_id = ? 
    ORDER BY b.created_at DESC
");
$bookingStmt->execute([$_SESSION['user_id']]);
$bookings = $bookingStmt->fetchAll(PDO::FETCH_ASSOC);

// Map DB columns to JS expected format
$jsBookings = array_map(function($b) {
    $total = (float)($b['total_amount'] > 0 ? $b['total_amount'] : ($b['total_price'] ?? 0));
    $paid = (float)($b['paid_amount'] ?? 0);
    $balance = (float)($b['balance_due'] ?? ($total - $paid));

    return [
        'id' => (int)$b['id'],
        'refId' => $b['ref_id'] ?? ('WIH-' . $b['id']),
        'ref_id' => $b['ref_id'] ?? ('WIH-' . $b['id']),
        'roomName' => $b['room_name'] ?? 'Island Room',
        'roomId' => $b['room_id'] ?? null,
        'roomImage' => $b['room_image'] ?? 'assets/image/background.jpg',
        'checkIn' => $b['check_in_date'] ?: ($b['check_in'] ?? ''),
        'checkOut' => $b['check_out_date'] ?: ($b['check_out'] ?? ''),
        'nights' => (int)($b['nights'] ?? 1),
        'dateBooked' => $b['created_at'] ?? '',
        'total' => $total,
        'paidAmount' => $paid,
        'balanceDue' => max(0, $balance),
        'status' => $b['status'] ?? 'Pending',
        'bookingStatus' => $b['booking_status'] ?? 'pending_verification',
        'paymentMethod' => $b['payment_method'] ?? 'Full Payment',
        'gcashRefNo' => $b['gcash_reference_no'] ?? null,
        'paymongoSessionId' => $b['paymongo_session_id'] ?? null,
        'paymongoPaymentId' => $b['paymongo_payment_id'] ?? null,
        'userId' => $b['user_id'],
        'guestName' => $b['guest_name'] ?? ''
    ];
}, $bookings);

// Fetch User Reports
$reportStmt = $pdo->prepare("SELECT * FROM reports WHERE user_id = ? ORDER BY created_at DESC");
$reportStmt->execute([$_SESSION['user_id']]);
$userReports = $reportStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch User Reviews
$reviewStmt = $pdo->prepare("
    SELECT rev.*, r.name as room_name 
    FROM reviews rev 
    LEFT JOIN rooms r ON rev.room_id = r.id 
    WHERE rev.user_id = ? 
    ORDER BY rev.created_at DESC
");
$reviewStmt->execute([$_SESSION['user_id']]);
$userReviews = $reviewStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate Statistics
$totalBookings = count($bookings);
$todayStr = date('Y-m-d');
$upcomingBookings = 0;
$completedBookings = 0;
$cancelledBookings = 0;
$totalSpent = 0;

foreach ($bookings as $b) {
    if ($b['status'] === 'Confirmed') {
        if ($b['check_out'] >= $todayStr) {
            $upcomingBookings++;
        } else {
            $completedBookings++;
        }
        $totalSpent += (float)$b['total_price'];
    } elseif ($b['status'] === 'Cancelled') {
        $cancelledBookings++;
    }
}
?>

<div class="min-h-screen bg-slate-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Top Profile Banner & Hero -->
        <div class="bg-gradient-to-r from-brand-dark via-slate-900 to-brand-blue rounded-3xl p-6 sm:p-8 text-white shadow-xl mb-8 relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none text-9xl text-white">
                <i class="fas fa-umbrella-beach"></i>
            </div>
            
            <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="flex items-center gap-5">
                    <div class="relative w-20 h-20 sm:w-24 sm:h-24 rounded-full border-4 border-white/20 overflow-hidden bg-brand-blue/30 shadow-inner flex-shrink-0">
                        <?php if (!empty($user['avatar'])): ?>
                            <img id="header-avatar-img" src="../<?php echo htmlspecialchars($user['avatar']); ?>" class="w-full h-full object-cover" alt="User Avatar">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-3xl sm:text-4xl font-bold text-white bg-gradient-to-br from-brand-blue to-teal-400">
                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 id="header-user-name" class="text-2xl sm:text-3xl font-bold tracking-tight"><?php echo htmlspecialchars($user['name']); ?></h1>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                <i class="fas fa-shield-alt mr-1 text-[10px]"></i> Verified Guest
                            </span>
                        </div>
                        <p class="text-slate-300 text-xs sm:text-sm mt-1 flex flex-wrap items-center gap-x-4 gap-y-1">
                            <span id="header-user-email"><i class="far fa-envelope mr-1 text-teal-400"></i> <?php echo htmlspecialchars($user['email']); ?></span>
                            <?php if (!empty($user['phone'])): ?>
                                <span id="header-user-phone"><i class="fas fa-phone-alt mr-1 text-teal-400"></i> <?php echo htmlspecialchars($user['phone']); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($user['nationality'])): ?>
                                <span><i class="fas fa-globe mr-1 text-teal-400"></i> <?php echo htmlspecialchars($user['nationality']); ?></span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="../index.php#rooms" class="bg-gradient-to-r from-teal-400 to-emerald-400 hover:from-teal-500 hover:to-emerald-500 text-brand-dark px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm shadow-md hover:shadow-lg transition flex items-center gap-2">
                        <i class="fas fa-plus-circle"></i> Book A Stay
                    </a>
                    <button onclick="logout()" class="bg-white/10 hover:bg-white/20 text-white border border-white/20 px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition flex items-center gap-2">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </div>
            </div>

            <!-- Profile Completeness Bar & Quick Stats -->
            <div class="mt-6 pt-6 border-t border-white/10 grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Completeness -->
                <div class="bg-white/5 rounded-2xl p-3.5 border border-white/10 backdrop-blur-sm">
                    <div class="flex justify-between items-center text-xs font-semibold mb-1.5">
                        <span class="text-slate-300">Profile Completeness</span>
                        <span id="completeness-percentage" class="text-teal-300 font-bold">0%</span>
                    </div>
                    <div class="w-full bg-white/20 h-2 rounded-full overflow-hidden">
                        <div id="completeness-bar" class="bg-gradient-to-r from-teal-400 to-emerald-400 h-full rounded-full transition-all duration-500" style="width: 0%;"></div>
                    </div>
                    <p id="completeness-hint" class="text-[11px] text-slate-400 mt-1 truncate"></p>
                </div>

                <!-- Stats 1 -->
                <div class="bg-white/5 rounded-2xl p-3.5 border border-white/10 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-teal-400/20 text-teal-300 flex items-center justify-center text-lg">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div>
                        <div class="text-lg font-bold text-white"><?php echo $upcomingBookings; ?></div>
                        <div class="text-[11px] text-slate-300">Upcoming Stays</div>
                    </div>
                </div>

                <!-- Stats 2 -->
                <div class="bg-white/5 rounded-2xl p-3.5 border border-white/10 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-400/20 text-blue-300 flex items-center justify-center text-lg">
                        <i class="fas fa-bed"></i>
                    </div>
                    <div>
                        <div class="text-lg font-bold text-white"><?php echo $totalBookings; ?></div>
                        <div class="text-[11px] text-slate-300">Total Reservations</div>
                    </div>
                </div>

                <!-- Stats 3 -->
                <div class="bg-white/5 rounded-2xl p-3.5 border border-white/10 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-400/20 text-emerald-300 flex items-center justify-center text-lg">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div>
                        <div class="text-lg font-bold text-white">₱<?php echo number_format($totalSpent, 2); ?></div>
                        <div class="text-[11px] text-slate-300">Total Spent</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs Bar -->
        <div class="bg-white rounded-2xl p-2 shadow-sm border border-gray-100 mb-8 flex flex-wrap items-center gap-2">
            <button id="tab-btn-overview" onclick="switchTab('overview')" class="profile-tab-btn active px-5 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition flex items-center gap-2 bg-brand-blue text-white shadow-md">
                <i class="fas fa-compass"></i> Overview
            </button>
            <button id="tab-btn-edit-profile" onclick="switchTab('edit-profile')" class="profile-tab-btn text-gray-600 hover:bg-gray-100 px-5 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition flex items-center gap-2">
                <i class="fas fa-user-edit"></i> Edit Profile & Preferences
            </button>
            <button id="tab-btn-bookings" onclick="switchTab('bookings')" class="profile-tab-btn text-gray-600 hover:bg-gray-100 px-5 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition flex items-center gap-2">
                <i class="fas fa-history"></i> My Bookings & Stays
            </button>
            <button id="tab-btn-security" onclick="switchTab('security')" class="profile-tab-btn text-gray-600 hover:bg-gray-100 px-5 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition flex items-center gap-2">
                <i class="fas fa-lock"></i> Security & Password
            </button>
            <button id="tab-btn-feedback" onclick="switchTab('feedback')" class="profile-tab-btn text-gray-600 hover:bg-gray-100 px-5 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition flex items-center gap-2">
                <i class="fas fa-comments"></i> Support & Reviews
            </button>
        </div>

        <!-- ================= TAB 1: OVERVIEW ================= -->
        <div id="tab-content-overview" class="profile-tab-content space-y-8">
            <!-- Next Upcoming Stay Highlight -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100">
                <div id="next-stay-container">
                    <!-- Populated dynamically via JS -->
                </div>
            </div>

            <!-- Guidelines & Backpacker Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-brand-blue flex items-center justify-center text-xl flex-shrink-0">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 text-sm">Hostel Check-in & Out</h4>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">Check-in begins at <strong>2:00 PM</strong>. Check-out is by <strong>12:00 PM</strong>. Early luggage storage is available free of charge at front desk.</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl flex-shrink-0">
                        <i class="fas fa-wifi"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 text-sm">High-Speed Wi-Fi</h4>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">Free fiber island Wi-Fi is available across all dorms and communal spaces. Network details are printed on your voucher.</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl flex-shrink-0">
                        <i class="fas fa-moon"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 text-sm">Quiet Hours Policy</h4>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">Quiet hours are strictly observed between <strong>10:00 PM and 7:00 AM</strong> in all shared dormitories.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= TAB 2: EDIT PROFILE & PREFERENCES ================= -->
        <div id="tab-content-edit-profile" class="profile-tab-content hidden">
            <form id="edit-profile-form" onsubmit="saveProfile(event)" class="space-y-8">
                
                <!-- Personal Information Card -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-1 flex items-center gap-2">
                        <i class="fas fa-user-circle text-brand-blue"></i> Personal Information
                    </h3>
                    <p class="text-xs text-gray-500 mb-6">Keep your contact and identification details up to date for smooth check-in.</p>

                    <div class="flex flex-col sm:flex-row items-center gap-6 pb-6 mb-6 border-b border-gray-100">
                        <div class="relative w-24 h-24 rounded-full overflow-hidden bg-slate-100 border-2 border-brand-blue/30 shadow-inner flex items-center justify-center flex-shrink-0">
                            <img id="edit-avatar-preview" src="<?php echo !empty($user['avatar']) ? ('../' . htmlspecialchars($user['avatar'])) : ''; ?>" class="<?php echo !empty($user['avatar']) ? '' : 'hidden'; ?> w-full h-full object-cover" alt="Avatar">
                            <div id="edit-avatar-placeholder" class="<?php echo !empty($user['avatar']) ? 'hidden' : ''; ?> text-3xl font-bold text-gray-400">
                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Profile Photo / Avatar</label>
                            <p class="text-[11px] text-gray-500 mb-2">Upload a clear photo (JPG, PNG, WEBP, up to 5MB).</p>
                            <input type="file" id="edit-avatar" accept="image/*" onchange="previewAvatar(event)" class="text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-blue/10 file:text-brand-blue hover:file:bg-brand-blue/20 cursor-pointer">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wide">Full Name <span class="text-rose-500">*</span></label>
                            <input type="text" id="edit-name" value="<?php echo htmlspecialchars($user['name']); ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wide">Email Address <span class="text-rose-500">*</span></label>
                            <input type="email" id="edit-email" value="<?php echo htmlspecialchars($user['email']); ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wide">Phone / WhatsApp Number</label>
                            <input type="text" id="edit-phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="+63 912 345 6789" class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wide">Nationality / Country</label>
                            <input type="text" id="edit-nationality" value="<?php echo htmlspecialchars($user['nationality'] ?? ''); ?>" placeholder="Philippines, Germany, etc." class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wide">Gender</label>
                            <select id="edit-gender" class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition">
                                <option value="" <?php echo empty($user['gender']) ? 'selected' : ''; ?>>Prefer not to say</option>
                                <option value="Male" <?php echo ($user['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($user['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                                <option value="Non-Binary" <?php echo ($user['gender'] ?? '') === 'Non-Binary' ? 'selected' : ''; ?>>Non-Binary</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wide">Birthdate</label>
                            <input type="date" id="edit-birthdate" value="<?php echo htmlspecialchars($user['birthdate'] ?? ''); ?>" class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition">
                        </div>

                        <div class="sm:col-span-2 lg:col-span-3">
                            <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wide">Home Address / Location</label>
                            <input type="text" id="edit-address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" placeholder="City, Province, Country" class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition">
                        </div>

                        <div class="sm:col-span-2 lg:col-span-3">
                            <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wide">About Me / Traveler Bio</label>
                            <textarea id="edit-bio" rows="2" placeholder="Tell us a little bit about your travel plans or hobbies..." class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Emergency Contact Section -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-1 flex items-center gap-2">
                        <i class="fas fa-heartbeat text-rose-500"></i> Emergency Contact
                    </h3>
                    <p class="text-xs text-gray-500 mb-6">Crucial for your safety during island adventures and hostel stays.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wide">Contact Person Name</label>
                            <input type="text" id="edit-emergency-name" value="<?php echo htmlspecialchars($user['emergency_name'] ?? ''); ?>" placeholder="Full Name" class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wide">Relationship</label>
                            <input type="text" id="edit-emergency-relation" value="<?php echo htmlspecialchars($user['emergency_relation'] ?? ''); ?>" placeholder="Parent, Spouse, Sibling, Friend" class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wide">Emergency Phone Number</label>
                            <input type="text" id="edit-emergency-phone" value="<?php echo htmlspecialchars($user['emergency_phone'] ?? ''); ?>" placeholder="+63 900 000 0000" class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition">
                        </div>
                    </div>
                </div>

                <!-- Stay & Bunk Preferences -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-1 flex items-center gap-2">
                        <i class="fas fa-sliders-h text-brand-blue"></i> Hostel Stay Preferences
                    </h3>
                    <p class="text-xs text-gray-500 mb-6">Customize your dormitory experience so our staff can prepare your room.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wide">Bunk Preference</label>
                            <select id="edit-pref-bunk" class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition">
                                <option value="no_preference" <?php echo ($preferences['bunk'] ?? '') === 'no_preference' ? 'selected' : ''; ?>>No Preference</option>
                                <option value="lower" <?php echo ($preferences['bunk'] ?? '') === 'lower' ? 'selected' : ''; ?>>Lower Bunk (Easier Access)</option>
                                <option value="upper" <?php echo ($preferences['bunk'] ?? '') === 'upper' ? 'selected' : ''; ?>>Upper Bunk (More Privacy)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wide">Dietary Requirements</label>
                            <select id="edit-pref-dietary" class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition">
                                <option value="none" <?php echo ($preferences['dietary'] ?? '') === 'none' ? 'selected' : ''; ?>>None / Standard</option>
                                <option value="vegetarian" <?php echo ($preferences['dietary'] ?? '') === 'vegetarian' ? 'selected' : ''; ?>>Vegetarian</option>
                                <option value="vegan" <?php echo ($preferences['dietary'] ?? '') === 'vegan' ? 'selected' : ''; ?>>Vegan</option>
                                <option value="halal" <?php echo ($preferences['dietary'] ?? '') === 'halal' ? 'selected' : ''; ?>>Halal</option>
                                <option value="gluten_free" <?php echo ($preferences['dietary'] ?? '') === 'gluten_free' ? 'selected' : ''; ?>>Gluten-Free</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wide">Estimated Arrival Time</label>
                            <select id="edit-pref-arrival" class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition">
                                <option value="standard" <?php echo ($preferences['arrival_time'] ?? '') === 'standard' ? 'selected' : ''; ?>>Standard (2:00 PM - 6:00 PM)</option>
                                <option value="early" <?php echo ($preferences['arrival_time'] ?? '') === 'early' ? 'selected' : ''; ?>>Early Luggage Drop (10:00 AM - 1:00 PM)</option>
                                <option value="late" <?php echo ($preferences['arrival_time'] ?? '') === 'late' ? 'selected' : ''; ?>>Late Check-in (After 8:00 PM)</option>
                            </select>
                        </div>

                        <div class="sm:col-span-3">
                            <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wide">Special Requests or Needs</label>
                            <input type="text" id="edit-pref-notes" value="<?php echo htmlspecialchars($preferences['notes'] ?? ''); ?>" placeholder="E.g., Surfboard storage, bottom bunk for medical reasons, etc." class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition">
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="switchTab('overview')" class="px-6 py-3 rounded-xl border border-gray-200 bg-white text-gray-700 font-bold text-xs hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit" id="save-profile-btn" class="bg-gradient-to-r from-brand-blue to-teal-500 text-white font-bold text-xs px-8 py-3 rounded-xl shadow-md hover:shadow-lg transition flex items-center gap-2">
                        <i class="fas fa-check"></i> Save Profile Details
                    </button>
                </div>
            </form>
        </div>

        <!-- ================= TAB 3: MY BOOKINGS & RESERVATIONS ================= -->
        <div id="tab-content-bookings" class="profile-tab-content hidden space-y-6">
            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-6 border-b border-gray-100">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">My Island Reservations</h3>
                        <p class="text-xs text-gray-500 mt-1">Track all current, past, and pending hostel bookings.</p>
                    </div>

                    <!-- Search & Filter Controls -->
                    <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
                        <div class="relative flex-1 sm:w-64">
                            <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                            <input type="text" id="booking-search-input" onkeyup="searchBookings(this.value)" placeholder="Search room or Ref ID..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition">
                        </div>
                    </div>
                </div>

                <!-- Filter Pills -->
                <div class="flex flex-wrap items-center gap-2 pt-4 pb-6">
                    <button onclick="filterBookings('all')" data-filter="all" class="booking-filter-pill px-4 py-1.5 rounded-full text-xs font-bold transition bg-gray-900 text-white shadow-sm">
                        All (<?php echo count($bookings); ?>)
                    </button>
                    <button onclick="filterBookings('Confirmed')" data-filter="Confirmed" class="booking-filter-pill px-4 py-1.5 rounded-full text-xs font-bold transition text-gray-600 hover:bg-gray-100">
                        Confirmed (<?php echo $upcomingBookings + $completedBookings; ?>)
                    </button>
                    <button onclick="filterBookings('Pending')" data-filter="Pending" class="booking-filter-pill px-4 py-1.5 rounded-full text-xs font-bold transition text-gray-600 hover:bg-gray-100">
                        Pending
                    </button>
                    <button onclick="filterBookings('Cancelled')" data-filter="Cancelled" class="booking-filter-pill px-4 py-1.5 rounded-full text-xs font-bold transition text-gray-600 hover:bg-gray-100">
                        Cancelled (<?php echo $cancelledBookings; ?>)
                    </button>
                </div>

                <!-- Bookings Card Container -->
                <div id="user-bookings-container" class="space-y-4">
                    <!-- Populated dynamically via JS -->
                </div>
            </div>
        </div>

        <!-- ================= TAB 4: SECURITY & PASSWORD ================= -->
        <div id="tab-content-security" class="profile-tab-content hidden space-y-8">
            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100 max-w-2xl mx-auto">
                <h3 class="text-xl font-bold text-gray-800 mb-1 flex items-center gap-2">
                    <i class="fas fa-lock text-brand-blue"></i> Change Account Password
                </h3>
                <p class="text-xs text-gray-500 mb-6">Ensure your account uses a secure password of at least 8 characters.</p>

                <form id="password-change-form" onsubmit="updateUserPassword(event)" class="space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wide">Current Password</label>
                        <div class="relative">
                            <input type="password" id="current-password" required placeholder="Enter current password" class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition">
                            <i onclick="togglePassword('current-password', this)" class="fas fa-eye absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 cursor-pointer hover:text-gray-600"></i>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wide">New Password</label>
                        <div class="relative">
                            <input type="password" id="new-password" required oninput="checkPasswordStrength(this.value)" placeholder="At least 8 characters" class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition">
                            <i onclick="togglePassword('new-password', this)" class="fas fa-eye absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 cursor-pointer hover:text-gray-600"></i>
                        </div>

                        <!-- Strength meter -->
                        <div class="mt-2">
                            <div class="flex justify-between items-center text-[10px] text-gray-500 mb-1">
                                <span>Password Strength</span>
                                <span id="pwd-strength-text" class="font-bold text-gray-400">None</span>
                            </div>
                            <div class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden">
                                <div id="pwd-strength-bar" class="h-full bg-gray-300 transition-all duration-300" style="width: 0%;"></div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wide">Confirm New Password</label>
                        <div class="relative">
                            <input type="password" id="confirm-password" required placeholder="Re-type new password" class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition">
                            <i onclick="togglePassword('confirm-password', this)" class="fas fa-eye absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 cursor-pointer hover:text-gray-600"></i>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" id="update-pwd-btn" class="w-full bg-brand-blue hover:bg-sky-600 text-white font-bold text-xs py-3 rounded-xl shadow-md hover:shadow-lg transition flex items-center justify-center gap-2">
                            <i class="fas fa-lock"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ================= TAB 5: SUPPORT & REVIEWS ================= -->
        <div id="tab-content-feedback" class="profile-tab-content hidden space-y-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Support Inquiries & Tickets -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100">
                    <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Support Inquiries</h3>
                            <p class="text-xs text-gray-500">View questions & feedback you submitted.</p>
                        </div>
                        <button onclick="openReportModal()" class="bg-brand-blue text-white px-3 py-1.5 rounded-xl text-xs font-bold hover:bg-sky-600 transition flex items-center gap-1.5 shadow-sm">
                            <i class="fas fa-plus"></i> New Ticket
                        </button>
                    </div>

                    <div id="user-reports-container" class="space-y-4">
                        <!-- Populated dynamically via JS -->
                    </div>
                </div>

                <!-- My Room Reviews -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100">
                    <div class="mb-6 pb-4 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800">My Room Reviews</h3>
                        <p class="text-xs text-gray-500">Your published feedback for W Island Hub stays.</p>
                    </div>

                    <div id="user-reviews-container" class="space-y-4">
                        <!-- Populated dynamically via JS -->
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ================= MODAL: PRINTABLE OFFICIAL VOUCHER ================= -->
<div id="voucher-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[100] hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-gray-100 animate-in fade-in zoom-in duration-200">
        <!-- Voucher Header -->
        <div class="bg-gradient-to-r from-brand-dark to-brand-blue p-6 text-white text-center relative">
            <button onclick="closeVoucherModal()" class="absolute top-4 right-4 text-white/70 hover:text-white text-lg">
                <i class="fas fa-times"></i>
            </button>
            <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center text-2xl mx-auto mb-2 text-teal-300">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <h3 class="text-xl font-bold tracking-tight">Official Booking Voucher</h3>
            <p class="text-xs text-teal-200 mt-0.5">W Island Hub Backpackers Inn</p>
        </div>

        <!-- Voucher Body -->
        <div class="p-6 space-y-4">
            <div class="flex justify-between items-center pb-4 border-b border-gray-100">
                <div>
                    <span class="text-[10px] text-gray-400 uppercase font-bold block">Primary Guest</span>
                    <h4 id="voucher-guest-name" class="font-bold text-gray-800 text-sm">Guest Name</h4>
                </div>
                <div class="text-right">
                    <span id="voucher-status-badge" class="inline-block px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold">Confirmed</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 pb-4 border-b border-gray-100">
                <div>
                    <span class="text-[10px] text-gray-400 uppercase font-bold block">Room Selection</span>
                    <p id="voucher-room-name" class="font-bold text-gray-800 text-sm">Standard Room</p>
                </div>
                <div>
                    <span class="text-[10px] text-gray-400 uppercase font-bold block">Duration</span>
                    <p id="voucher-nights" class="font-bold text-brand-blue text-sm">1 Night</p>
                </div>
                <div>
                    <span class="text-[10px] text-gray-400 uppercase font-bold block">Check-in</span>
                    <p id="voucher-check-in" class="font-bold text-gray-700 text-xs">Oct 12, 2026 (2:00 PM)</p>
                </div>
                <div>
                    <span class="text-[10px] text-gray-400 uppercase font-bold block">Check-out</span>
                    <p id="voucher-check-out" class="font-bold text-gray-700 text-xs">Oct 13, 2026 (12:00 PM)</p>
                </div>
            </div>

            <div class="flex justify-between items-center pb-2">
                <div>
                    <span class="text-[10px] text-gray-400 uppercase font-bold block">Payment Method</span>
                    <p id="voucher-payment-method" class="font-semibold text-gray-700 text-xs">Full Payment</p>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-gray-400 uppercase font-bold block">Total Amount</span>
                    <p id="voucher-total-price" class="text-lg font-bold text-brand-blue">₱0.00</p>
                </div>
            </div>

            <div class="bg-slate-50 p-3.5 rounded-2xl border border-gray-100 text-center">
                <span id="voucher-ref-code" class="font-mono font-bold text-xs text-gray-800 tracking-wider">Ref: WIH-0000</span>
                <p class="text-[10px] text-gray-400 mt-1">Please present this reference or QR code upon check-in at the reception desk.</p>
            </div>
        </div>

        <!-- Voucher Footer -->
        <div class="p-6 bg-slate-50 border-t border-gray-100 flex gap-3">
            <button onclick="window.print()" class="flex-1 bg-brand-blue hover:bg-sky-600 text-white font-bold text-xs py-3 rounded-xl transition flex items-center justify-center gap-2 shadow-sm">
                <i class="fas fa-print"></i> Print Voucher
            </button>
            <button onclick="closeVoucherModal()" class="px-5 bg-white border border-gray-200 text-gray-700 font-bold text-xs rounded-xl hover:bg-gray-100 transition">
                Close
            </button>
        </div>
    </div>
</div>

<!-- ================= MODAL: SUPPORT / FEEDBACK TICKET ================= -->
<div id="report-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[100] hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 border border-gray-100">
        <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-800">Support Inquiry / Feedback</h3>
            <button onclick="closeReportModal()" class="text-gray-400 hover:text-gray-600 text-lg">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="report-form" onsubmit="submitReport(event)" class="space-y-4">
            <input type="hidden" id="report-booking-id">

            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wide">Category</label>
                <select id="report-type" class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition">
                    <option value="feedback">General Feedback</option>
                    <option value="complaint">Stay Issue / Complaint</option>
                    <option value="suggestion">Hostel Suggestion</option>
                    <option value="inquiry">Booking Inquiry</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wide">Subject</label>
                <input type="text" id="report-subject" required placeholder="Brief summary of your inquiry" class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wide">Message Details</label>
                <textarea id="report-message" required rows="4" placeholder="Describe the details so our hostel staff can assist you promptly..." class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition"></textarea>
            </div>

            <div class="pt-2 flex gap-3">
                <button type="button" onclick="closeReportModal()" class="flex-1 bg-white border border-gray-200 text-gray-700 font-bold text-xs py-3 rounded-xl hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 bg-brand-blue hover:bg-sky-600 text-white font-bold text-xs py-3 rounded-xl shadow-md transition">
                    Submit Inquiry
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: WRITE ROOM REVIEW ================= -->
<div id="review-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[100] hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 border border-gray-100">
        <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-800">Write a Room Review</h3>
            <button onclick="closeReviewModal()" class="text-gray-400 hover:text-gray-600 text-lg">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="review-form" onsubmit="submitReviewFromModal(event)" class="space-y-5">
            <input type="hidden" id="review-room-id">
            <input type="hidden" id="review-rating-val" value="5">

            <div>
                <span id="review-modal-room-title" class="text-xs font-bold text-brand-blue block mb-2">Reviewing: Room</span>
                <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wide">Rating</label>
                <div id="star-rating-picker" class="flex items-center gap-2 text-2xl text-amber-400 cursor-pointer">
                    <i class="fas fa-star" onclick="setModalRating(1)"></i>
                    <i class="fas fa-star" onclick="setModalRating(2)"></i>
                    <i class="fas fa-star" onclick="setModalRating(3)"></i>
                    <i class="fas fa-star" onclick="setModalRating(4)"></i>
                    <i class="fas fa-star" onclick="setModalRating(5)"></i>
                    <span id="rating-text-label" class="text-xs font-bold text-gray-500 ml-2">5.0 - Excellent!</span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wide">Review & Comments</label>
                <textarea id="review-comment" required rows="4" placeholder="How was the bed comfort, cleanliness, Wi-Fi, and hostel vibe?" class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition"></textarea>
            </div>

            <div class="pt-2 flex gap-3">
                <button type="button" onclick="closeReviewModal()" class="flex-1 bg-white border border-gray-200 text-gray-700 font-bold text-xs py-3 rounded-xl hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold text-xs py-3 rounded-xl shadow-md transition">
                    Post Review
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<!-- Bootstrap Initial State for JavaScript -->
<script>
    window.appState = {
        currentUser: <?php echo json_encode($user); ?>,
        bookings: <?php echo json_encode($jsBookings); ?>,
        reports: <?php echo json_encode($userReports); ?>,
        reviews: <?php echo json_encode($userReviews); ?>,
        users: []
    };

    window.saveState = function() {
        if (window.appState && window.appState.currentUser) {
            sessionStorage.setItem('w_currentUser', JSON.stringify(window.appState.currentUser));
        }
    };

    window.router = function(page) {
        window.location.href = '../index.php#' + page;
    };

    document.addEventListener('DOMContentLoaded', function() {
        const loader = document.getElementById('loader');
        if (loader) {
            loader.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        if (typeof initGuestProfile === 'function') {
            initGuestProfile();
        } else if (typeof renderUserProfile === 'function') {
            renderUserProfile();
        }
    });
</script>

<!-- Modern Notifications -->
<script src="../assets/js/notifications.js"></script>
<!-- Profile Management Script -->
<script src="../assets/js/user_profile.js?v=<?php echo time(); ?>"></script>

