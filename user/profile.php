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

// Fetch Booking History with Room info (matching user_id or guest_email)
$bookingStmt = $pdo->prepare("
    SELECT b.*, r.name as room_name, r.type as room_type, r.image as room_image 
    FROM bookings b 
    LEFT JOIN rooms r ON b.room_id = r.id 
    WHERE b.user_id = ? OR (b.guest_email IS NOT NULL AND b.guest_email != '' AND b.guest_email = ?)
    ORDER BY b.created_at DESC
");
$bookingStmt->execute([$_SESSION['user_id'], $user['email'] ?? '']);
$bookings = $bookingStmt->fetchAll(PDO::FETCH_ASSOC);

// Auto-link any matching bookings that had NULL user_id
if (!empty($bookings)) {
    $unlinkedIds = [];
    foreach ($bookings as $b) {
        if (empty($b['user_id'])) {
            $unlinkedIds[] = (int)$b['id'];
        }
    }
    if (!empty($unlinkedIds)) {
        $inClause = implode(',', array_fill(0, count($unlinkedIds), '?'));
        $linkStmt = $pdo->prepare("UPDATE bookings SET user_id = ? WHERE id IN ($inClause)");
        $linkStmt->execute(array_merge([$_SESSION['user_id']], $unlinkedIds));
    }
}

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
        'expectedArrivalTime' => !empty($b['expected_arrival_time']) ? $b['expected_arrival_time'] : '14:00:00',
        'nights' => (int)($b['nights'] ?? 1),
        'dateBooked' => $b['created_at'] ?? '',
        'createdAt' => $b['created_at'] ?? '',
        'total' => $total,
        'paidAmount' => $paid,
        'balanceDue' => max(0, $balance),
        'status' => $b['status'] ?? 'Pending',
        'bookingStatus' => $b['booking_status'] ?? 'pending_verification',
        'paymentMethod' => $b['payment_method'] ?? 'Full Payment',
        'gcashRefNo' => $b['gcash_reference_no'] ?? null,
        'paymongoSessionId' => $b['paymongo_session_id'] ?? null,
        'paymongoPaymentId' => $b['paymongo_payment_id'] ?? null,
        'adults' => (int)($b['adults'] ?? 1),
        'children' => (int)($b['children'] ?? 0),
        'guests' => (int)(($b['adults'] ?? 1) + ($b['children'] ?? 0)),
        'bedsCount' => (int)($b['beds_count'] ?? 1),
        'roomsCount' => (int)($b['rooms_count'] ?? 1),
        'childrenAges' => !empty($b['children_ages']) ? (is_array($b['children_ages']) ? $b['children_ages'] : json_decode($b['children_ages'], true)) : [],
        'userId' => $b['user_id'],
        'guestName' => !empty($b['guest_name']) ? $b['guest_name'] : ($user['name'] ?? ''),
        'guestEmail' => !empty($b['guest_email']) ? $b['guest_email'] : ($user['email'] ?? ''),
        'guestPhone' => !empty($b['guest_phone']) ? $b['guest_phone'] : ($user['phone'] ?? ''),
        'expiresAt' => $b['expires_at'] ?? null,
        'specialRequests' => $b['special_requests'] ?? ''
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
$totalSpent = 0.0;

foreach ($bookings as $b) {
    $bStatus = strtolower($b['booking_status'] ?? 'pending_verification');
    $status = strtolower($b['status'] ?? 'pending');
    $checkIn  = $b['check_in_date']  ?: ($b['check_in']  ?? '');
    $checkOut = $b['check_out_date'] ?: ($b['check_out'] ?? '');

    $isCancelled = in_array($bStatus, ['cancelled_expired', 'cancelled', 'no_show']) || $status === 'cancelled';
    $isCompleted = ($bStatus === 'completed') || ($status === 'confirmed' && !empty($checkOut) && $checkOut < $todayStr);

    if ($isCancelled) {
        $cancelledBookings++;
    } else {
        if ($isCompleted) {
            $completedBookings++;
        } elseif (!empty($checkIn) && $checkIn > $todayStr) {
            $upcomingBookings++;
        }

        // Total Spent Calculation: Count actual paid amount or confirmed/deposit amount
        $paid = (float)($b['paid_amount'] ?? 0);
        $total = (float)($b['total_amount'] > 0 ? $b['total_amount'] : ($b['total_price'] ?? 0));

        if ($paid > 0) {
            $totalSpent += $paid;
        } elseif ($bStatus === 'confirmed' || $bStatus === 'completed' || $status === 'confirmed') {
            $totalSpent += $total;
        } elseif ($bStatus === 'deposit_paid') {
            $totalSpent += round($total * 0.20, 2);
        }
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
                        <h1 id="header-user-name" class="text-2xl sm:text-3xl font-bold tracking-tight"><?php echo htmlspecialchars($user['name']); ?></h1>
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
                        <div id="upcoming-stays-display" class="text-lg font-bold text-white"><?php echo $upcomingBookings; ?></div>
                        <div class="text-[11px] text-slate-300">Upcoming Stays</div>
                    </div>
                </div>

                <!-- Stats 2 -->
                <div class="bg-white/5 rounded-2xl p-3.5 border border-white/10 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-400/20 text-blue-300 flex items-center justify-center text-lg">
                        <i class="fas fa-bed"></i>
                    </div>
                    <div>
                        <div id="total-reservations-display" class="text-lg font-bold text-white"><?php echo $totalBookings; ?></div>
                        <div class="text-[11px] text-slate-300">Total Reservations</div>
                    </div>
                </div>

                <!-- Stats 3 -->
                <div class="bg-white/5 rounded-2xl p-3.5 border border-white/10 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-400/20 text-emerald-300 flex items-center justify-center text-lg">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div>
                        <div id="total-spent-display" class="text-lg font-bold text-white">₱<?php echo number_format($totalSpent, 2); ?></div>
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
                    <button onclick="filterBookings('Upcoming')" data-filter="Upcoming" class="booking-filter-pill px-4 py-1.5 rounded-full text-xs font-bold transition text-gray-600 hover:bg-gray-100">
                        Upcoming (<?php echo $upcomingBookings; ?>)
                    </button>
                    <button onclick="filterBookings('Confirmed')" data-filter="Confirmed" class="booking-filter-pill px-4 py-1.5 rounded-full text-xs font-bold transition text-gray-600 hover:bg-gray-100">
                        Confirmed (<?php echo count(array_filter($bookings, fn($b) => ($b['status'] ?? '') === 'Confirmed' || in_array($b['booking_status'] ?? '', ['confirmed', 'deposit_paid']))); ?>)
                    </button>
                    <button onclick="filterBookings('Pending')" data-filter="Pending" class="booking-filter-pill px-4 py-1.5 rounded-full text-xs font-bold transition text-gray-600 hover:bg-gray-100">
                        Pending (<?php echo count(array_filter($bookings, fn($b) => ($b['status'] ?? '') === 'Pending' && !in_array($b['booking_status'] ?? '', ['cancelled_expired', 'no_show']))); ?>)
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

<!-- ================= MODAL: REPORT LATE ARRIVAL ================= -->
<div id="late-arrival-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[100] hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 sm:p-7 border border-gray-100 relative overflow-hidden max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-start pb-4 border-b border-gray-100 mb-4">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center text-lg flex-shrink-0">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Report Late Arrival</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Protect your room reservation from no-show cancellation</p>
                </div>
            </div>
            <button type="button" onclick="closeLateArrivalModal()" class="text-gray-400 hover:text-gray-600 text-lg p-1 rounded-lg">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Reservation Preview Card -->
        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 mb-4">
            <div class="flex justify-between items-start">
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Reservation</span>
                    <h4 id="late-modal-room-name" class="font-bold text-slate-800 text-sm">Room Name</h4>
                    <p id="late-modal-ref-id" class="text-xs font-mono text-slate-500">Ref: WIH-0000</p>
                </div>
                <div class="text-right">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Scheduled Check-in</span>
                    <p id="late-modal-checkin-date" class="font-bold text-brand-blue text-xs">Today</p>
                    <p id="late-modal-current-time" class="text-[11px] text-purple-700 font-semibold">2:00 PM</p>
                </div>
            </div>
        </div>

        <form id="late-arrival-form" onsubmit="submitLateArrival(event)" class="space-y-4">
            <input type="hidden" id="late-arrival-booking-id">

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wide">
                    New Estimated Arrival Time <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input type="time" id="late-arrival-new-time" required 
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition cursor-pointer">
                </div>
                <p class="text-[11px] text-gray-500 mt-1">
                    <i class="fas fa-info-circle text-amber-500 mr-1"></i> Front desk will extend your room hold window until this time.
                </p>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wide">
                    Reason for Delay <span class="text-rose-500">*</span>
                </label>
                <select id="late-arrival-reason" required 
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition">
                    <option value="Flight Delay / Rescheduled">Flight Delay / Rescheduled</option>
                    <option value="Ferry / Sea Boat Transit Delay">Ferry / Sea Boat Transit Delay</option>
                    <option value="Road Traffic / Van Transfer Delay">Road Traffic / Van Transfer Delay</option>
                    <option value="Tour / Island Activity Overrun">Tour / Island Activity Overrun</option>
                    <option value="Weather / Heavy Rain">Weather / Heavy Rain</option>
                    <option value="Late Departure / Personal Delay">Late Departure / Personal Delay</option>
                    <option value="Other Travel Delay">Other Travel Delay</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wide">
                    Contact Phone / WhatsApp <span class="text-rose-500">*</span>
                </label>
                <input type="tel" id="late-arrival-contact-phone" required placeholder="e.g. +63 912 345 6789" 
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wide">
                    Additional Details / Travel Notes (Optional)
                </label>
                <textarea id="late-arrival-notes" rows="3" placeholder="e.g., Flight #PR 2871 arriving at airport, van transfer booked; estimated arrival at hotel reception around 5:30 PM..." 
                          class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"></textarea>
            </div>

            <!-- Guarantee Notice Box -->
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3.5 flex items-start gap-2.5 text-xs text-amber-900 leading-relaxed">
                <i class="fas fa-shield-alt text-amber-600 text-sm mt-0.5 flex-shrink-0"></i>
                <span>Submitting this form immediately alerts our front desk team and updates your expected arrival window so your reservation hold is maintained safely against no-show release.</span>
            </div>

            <div class="pt-2 flex flex-col sm:flex-row gap-2.5">
                <button type="button" onclick="closeLateArrivalModal()" class="flex-1 bg-white border border-gray-200 text-gray-700 font-bold text-xs py-3 rounded-xl hover:bg-gray-50 transition text-center">
                    Cancel
                </button>
                <button type="submit" id="btn-submit-late-arrival" class="flex-1 bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-700 hover:to-amber-600 text-white font-bold text-xs py-3 rounded-xl shadow-md transition text-center flex items-center justify-center gap-2">
                    <i class="fas fa-paper-plane"></i> Send Arrival Notice
                </button>
            </div>

            <div class="text-center pt-1 border-t border-slate-100">
                <a href="tel:+639456882266" class="text-xs text-amber-700 hover:text-amber-800 font-bold inline-flex items-center gap-1.5">
                    <i class="fas fa-phone-alt"></i> Urgent? Call Front Desk Directly: +63 945 688 2266
                </a>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: BOOKING MESSAGES & REPORTS ================= -->
<div id="booking-reports-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[100] hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 border border-gray-100 flex flex-col max-h-[90vh]">
        <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-100 shrink-0">
            <div>
                <h3 class="text-lg font-bold text-gray-800">Messages & Reports</h3>
                <p id="br-modal-room-name" class="text-xs text-gray-500">Booking Reference</p>
            </div>
            <button onclick="closeBookingReportsModal()" class="text-gray-400 hover:text-gray-600 text-lg">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Messages History Area -->
        <div id="br-messages-container" class="flex-1 overflow-y-auto space-y-4 mb-4 pr-2 custom-scrollbar min-h-[200px]">
            <!-- Messages will be injected here via JS -->
        </div>

        <!-- New Report Form -->
        <form id="br-report-form" onsubmit="submitBookingReport(event)" class="shrink-0 pt-4 border-t border-gray-100 space-y-3">
            <input type="hidden" id="br-booking-id">
            
            <div class="flex gap-2">
                <select id="br-report-type" class="w-1/3 px-3 py-2 bg-slate-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition">
                    <option value="complaint">Problem/Issue</option>
                    <option value="inquiry">Question</option>
                    <option value="feedback">Feedback</option>
                </select>
                <input type="text" id="br-report-subject" required placeholder="Subject / Topic" class="w-2/3 px-3 py-2 bg-slate-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition">
            </div>
            
            <textarea id="br-report-message" required rows="2" placeholder="Type your message or report a problem here..." class="w-full px-3 py-2 bg-slate-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition"></textarea>
            
            <div class="flex justify-end">
                <button type="submit" id="br-submit-btn" class="bg-brand-blue hover:bg-sky-600 text-white font-bold text-xs px-5 py-2.5 rounded-xl shadow-md transition flex items-center gap-1.5">
                    <i class="fas fa-paper-plane"></i> Send Message
                </button>
            </div>
        </form>
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
    /**
     * Cross-tab session guard.
     *
     * Problem: PHP sessions are shared across all browser tabs. When a second
     * account logs in on Tab 2, it overwrites $_SESSION['user_id'] on the server.
     * Tab 1 (still on profile.php) would then show the wrong account on next load.
     *
     * Fix: On every profile page load, compare what PHP rendered (phpRenderedUserId)
     * against what this tab's sessionStorage says is the logged-in user. If they
     * don't match, call sync_session.php to re-establish the correct PHP session
     * for this tab, then reload so PHP re-fetches the right user's data.
     */
    (function() {
        const phpRenderedUserId = <?php echo (int)$_SESSION['user_id']; ?>;

        // Read the user this tab believes is logged in
        let tabUser = null;
        try {
            const stored = sessionStorage.getItem('w_currentUser');
            tabUser = stored ? JSON.parse(stored) : null;
        } catch(e) {}

        const tabUserId = tabUser ? (parseInt(tabUser.id, 10) || 0) : 0;

        // Only sync when:
        // 1. Tab has a user in sessionStorage
        // 2. It differs from what PHP session has
        if (tabUserId > 0 && tabUserId !== phpRenderedUserId) {
            // Session mismatch detected: re-sync PHP session to this tab's user
            fetch('../actions/sync_session.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: tabUserId })
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    // Reload the page — PHP will now use the correct session
                    window.location.reload();
                } else {
                    // User no longer valid: clear tab state and redirect to login
                    sessionStorage.removeItem('w_currentUser');
                    window.location.href = '../index.php#login';
                }
            })
            .catch(function() {
                // On network error, stay on page but don't initialize with wrong data
                sessionStorage.removeItem('w_currentUser');
                window.location.href = '../index.php#login';
            });

            // Block profile initialization until reload completes
            return;
        }

        // Sessions match (or no sessionStorage user) — safe to bootstrap normally
        window.appState = {
            currentUser: <?php echo json_encode($user); ?>,
            bookings: <?php echo json_encode($jsBookings); ?>,
            reports: <?php echo json_encode($userReports); ?>,
            reviews: <?php echo json_encode($userReviews); ?>,
            users: []
        };

        // Also update sessionStorage to keep it in sync with what PHP confirmed
        try {
            sessionStorage.setItem('w_currentUser', JSON.stringify(window.appState.currentUser));
        } catch(e) {}

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
    })();
</script>

<!-- Modern Notifications -->
<script src="../assets/js/notifications.js"></script>
<!-- Invoice & Voucher Module -->
<script src="../assets/js/modules/invoice.js?v=<?php echo time(); ?>"></script>
<!-- Profile Management Script -->
<script src="../assets/js/user_profile.js?v=<?php echo time(); ?>"></script>


