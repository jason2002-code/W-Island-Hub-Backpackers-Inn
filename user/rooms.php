<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (file_exists(__DIR__ . '/../config/db.php')) {
    require_once __DIR__ . '/../config/db.php';
} else {
    require_once 'config/db.php';
}

$pdo = getDB();
$rooms = [];
$error = '';

if ($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT r.*, 
                   COALESCE(ROUND(AVG(rev.rating), 1), r.rating, 5.0) AS avg_rating,
                   COUNT(rev.id) AS total_reviews
            FROM rooms r
            LEFT JOIN reviews rev ON rev.room_id = r.id AND rev.status = 'approved' AND rev.visible = 1
            GROUP BY r.id
            ORDER BY r.created_at DESC
        ");
        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Compute real-time occupied/remaining beds for dorm rooms from active bookings today
        $today = date('Y-m-d');
        foreach ($rooms as &$room) {
            $isDorm = ($room['type'] === 'Dorm' || intval($room['id']) === 5);
            if ($isDorm) {
                $dormTotalBeds = 8; // Fixed total beds
                $stmtOccupied = $pdo->prepare("
                    SELECT COALESCE(SUM(
                        CASE 
                            WHEN b.beds_count > 0 THEN b.beds_count
                            ELSE GREATEST(1, COALESCE(b.adults + b.children, b.adults, 1))
                        END
                    ), 0) AS occupied_beds
                    FROM bookings b
                    WHERE b.room_id = ?
                      AND b.status IN ('Pending', 'Confirmed')
                      AND b.booking_status NOT IN ('cancelled_expired', 'no_show')
                      AND COALESCE(b.check_in_date, b.check_in) <= ?
                      AND COALESCE(b.check_out_date, b.check_out) > ?
                ");
                $stmtOccupied->execute([$room['id'], $today, $today]);
                $occupiedBeds = (int)$stmtOccupied->fetchColumn();
                $room['dorm_occupied_beds'] = $occupiedBeds;
                $room['dorm_remaining_beds'] = max(0, $dormTotalBeds - $occupiedBeds);
                $room['dorm_total_beds'] = $dormTotalBeds;
            }
        }
        unset($room);
    } catch (PDOException $e) {
        $error = 'Failed to load rooms: ' . $e->getMessage();
    }
} else {
    $error = 'Database connection failed';
}
?>

<!-- Rooms Grid -->
<div id="rooms-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    <?php if ($error): ?>
        <div class="col-span-full text-center text-red-500 py-8">
            <p><?php echo htmlspecialchars($error); ?></p>
        </div>
    <?php elseif (empty($rooms)): ?>
        <div class="col-span-full text-center text-gray-500 py-8">
            <p>No rooms available at the moment.</p>
        </div>
    <?php else: ?>
        <?php foreach ($rooms as $room): 
            $amenities = is_array($room['amenities']) ? $room['amenities'] : json_decode($room['amenities'] ?? '[]', true);
            if (!is_array($amenities)) {
                $amenities = array_map('trim', explode(',', $room['amenities'] ?? ''));
            }
            $rating = floatval($room['avg_rating'] ?? $room['rating'] ?? 5.0);
            $reviews_count = intval($room['total_reviews'] ?? 0);
            $stars = round($rating);
            $room_id = intval($room['id']);
        ?>
            <?php 
                $room_capacity = intval($room['capacity'] ?? 2); 
                $room_max_capacity = intval($room['max_capacity'] ?? $room_capacity);
                $is_dorm = ($room['type'] === 'Dorm' || $room_id === 5);
                $dorm_total_price = 0.00;
                if ($is_dorm) {
                    $room_max_capacity = 8;
                    $dorm_bed_price = 460.00;
                    $dorm_total_price = 3680.00;
                }
            ?>
            <div class="room-card bg-white rounded-xl shadow-sm hover:shadow-lg transition overflow-hidden border border-gray-100 flex flex-col" data-room-id="<?php echo $room_id; ?>" data-capacity="<?php echo $room_max_capacity; ?>" data-max-capacity="<?php echo $room_max_capacity; ?>" data-is-dorm="<?php echo $is_dorm ? '1' : '0'; ?>">
                <div class="h-48 overflow-hidden relative cursor-pointer group" onclick="openRoomInfoModal(<?php echo $room_id; ?>)" title="Click to view full room information">
                    <img src="<?php echo htmlspecialchars($room['image']); ?>" alt="<?php echo htmlspecialchars($room['name']); ?>" class="w-full h-full object-cover transition duration-500 group-hover:scale-110">
                    <div class="absolute inset-0 bg-slate-950/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                        <span class="bg-white/95 backdrop-blur-md text-slate-900 text-xs font-bold px-3.5 py-1.5 rounded-full shadow-md flex items-center gap-1.5 transform translate-y-2 group-hover:translate-y-0 transition-transform">
                            <i class="fas fa-eye text-brand-blue"></i> View Details
                        </span>
                    </div>
                    <div class="absolute top-3 right-3 bg-white/95 backdrop-blur-md px-3 py-1.5 rounded-full text-xs font-bold text-slate-900 shadow-md flex items-center gap-1.5 border border-white/60">
                        <i class="fas fa-star text-amber-400 text-xs"></i>
                        <span class="rating-badge-value" data-room-id="<?php echo $room_id; ?>"><?php echo number_format($rating, 1); ?></span>
                        <span class="text-slate-400 text-[10px] font-medium">/ 5</span>
                    </div>
                </div>
                <div class="p-6 flex-1 flex flex-col">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-xl font-bold text-brand-dark hover:text-brand-blue cursor-pointer transition" onclick="openRoomInfoModal(<?php echo $room_id; ?>)" title="Click to view room details"><?php echo htmlspecialchars($room['name']); ?></h3>
                        <div>
                            <span class="bg-blue-50 text-brand-blue text-xs font-bold px-2 py-1 rounded uppercase"><?php echo htmlspecialchars($room['type']); ?></span>
                        </div>
                    </div>
                    <p class="text-gray-500 text-sm mb-4 line-clamp-2"><?php echo htmlspecialchars($room['description']); ?></p>
                    
                    <div class="flex flex-wrap gap-2 mb-4">
                        <?php if ($is_dorm): 
                            $dorm_avail = intval($room['dorm_remaining_beds'] ?? 8);
                        ?>
                            <span class="text-xs text-brand-blue bg-blue-50 px-2.5 py-1 rounded font-semibold flex items-center gap-1.5 room-capacity-pill" data-room-id="<?php echo $room_id; ?>">
                                <i class="fas fa-bed text-[11px] text-brand-blue"></i> 
                                <span class="room-beds-text"><strong class="room-avail-beds"><?php echo $dorm_avail; ?></strong> Beds Available</span>
                            </span>
                        <?php else: ?>
                            <span class="text-xs text-brand-blue bg-blue-50 px-2 py-1 rounded font-semibold flex items-center gap-1">
                                <i class="fas fa-user-friends text-[10px]"></i> Up to <?php echo $room_max_capacity; ?> Guest<?php echo $room_max_capacity > 1 ? 's' : ''; ?>
                            </span>
                        <?php endif; ?>
                        <?php foreach (array_slice($amenities, 0, 3) as $amenity): ?>
                            <button type="button" onclick="openRoomInfoModal(<?php echo $room_id; ?>, 'amenities')" class="text-xs text-gray-600 bg-gray-100 hover:bg-amber-50 hover:text-amber-700 hover:border-amber-200 border border-transparent px-2 py-1 rounded transition-colors cursor-pointer" title="View all amenities"><?php echo htmlspecialchars($amenity); ?></button>
                        <?php endforeach; ?>
                        <?php if (count($amenities) > 3): ?>
                            <button type="button" onclick="openRoomInfoModal(<?php echo $room_id; ?>, 'amenities')" class="text-xs text-brand-blue bg-blue-50 hover:bg-blue-100 border border-blue-100 px-2 py-1 rounded font-semibold transition-colors cursor-pointer" title="View all amenities">+<?php echo count($amenities) - 3; ?> more</button>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Reviews Quick Action Button (Opens Horizontal Reviews in Modal) -->
                    <div class="mt-3">
                        <button type="button" onclick="openRoomInfoModal(<?php echo $room_id; ?>, 'reviews')" class="w-full text-left py-2 px-3.5 bg-slate-50 hover:bg-amber-50/70 rounded-xl flex items-center justify-between transition-colors border border-slate-200/60 group/rev cursor-pointer" title="View all guest reviews">
                            <div class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                                <i class="fas fa-star text-amber-400"></i>
                                <span>Guest Reviews</span>
                                <span class="reviews-count text-[11px] font-bold bg-white border border-slate-200 text-slate-600 px-2 py-0.5 rounded-full" data-room-id="<?php echo $room_id; ?>">(<?php echo $reviews_count; ?>)</span>
                            </div>
                            <span class="text-xs font-semibold text-brand-blue group-hover/rev:text-amber-600 flex items-center gap-1">
                                Read Reviews <i class="fas fa-arrow-right text-[10px] group-hover/rev:translate-x-0.5 transition-transform"></i>
                            </span>
                        </button>
                    </div>

                    <div class="mt-auto pt-4 border-t flex justify-between items-center gap-2">
                        <?php if ($is_dorm): ?>
                            <div>
                                <div class="flex items-baseline gap-1">
                                    <span class="text-2xl font-bold text-brand-blue">₱<?php echo number_format($dorm_total_price, 2); ?></span>
                                    <span class="text-gray-400 text-xs">/ night (8 Beds)</span>
                                </div>
                                <div class="text-xs font-semibold text-emerald-600 flex items-center gap-1 mt-0.5">
                                    <i class="fas fa-bed text-[10px]"></i> ₱460.00 / bed / night
                                </div>
                            </div>
                        <?php else: ?>
                            <div>
                                <span class="text-2xl font-bold text-brand-blue">₱<?php echo number_format($room['price'], 2); ?></span>
                                <span class="text-gray-400 text-sm">/ night</span>
                            </div>
                        <?php endif; ?>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="openRoomInfoModal(<?php echo $room_id; ?>)" class="bg-slate-100 hover:bg-slate-200 text-slate-700 hover:text-slate-900 px-3 py-2 rounded-lg font-semibold transition text-sm flex items-center gap-1.5 border border-slate-200 shadow-sm" title="View Room Details">
                                <i class="fas fa-info-circle text-brand-blue text-xs"></i>
                                <span>Details</span>
                            </button>
                            <button onclick="initiateBooking(<?php echo $room_id; ?>)" class="bg-brand-dark hover:bg-gray-800 text-white px-4 py-2 rounded-lg font-medium transition text-sm <?php echo $room['status'] === 'Maintenance' ? 'opacity-50 cursor-not-allowed' : ''; ?>" <?php echo $room['status'] === 'Maintenance' ? 'disabled' : ''; ?>>
                                <?php echo $room['status'] === 'Maintenance' ? 'Maintenance' : 'Book Now'; ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- ================= CUSTOMER ROOM INFORMATION MODAL ================= -->
<div id="room-info-modal" class="fixed inset-0 bg-black/75 backdrop-blur-sm z-[100] hidden items-center justify-center p-3 sm:p-6 transition-opacity duration-300 opacity-0" onclick="closeRoomInfoModal(event)">
    <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full overflow-hidden border border-slate-100 my-auto transform transition-all duration-300 scale-95 max-h-[92vh] flex flex-col" onclick="event.stopPropagation()">
        
        <!-- Modal Top Banner & Floating Badges -->
        <div class="relative h-56 sm:h-72 w-full bg-slate-950 overflow-hidden flex-shrink-0">
            <img id="modal-room-image" src="" alt="Room Photo" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/30 to-transparent"></div>
            
            <!-- Close Button (Floating Glass) -->
            <button onclick="closeRoomInfoModal()" type="button" class="absolute top-4 right-4 bg-black/60 hover:bg-black/80 text-white rounded-full w-9 h-9 flex items-center justify-center backdrop-blur-md transition-all duration-200 border border-white/20 shadow-lg cursor-pointer z-20" aria-label="Close modal">
                <i class="fas fa-times text-sm"></i>
            </button>
            
            <!-- Badges Top Left -->
            <div class="absolute top-4 left-4 flex flex-wrap items-center gap-2 z-20">
                <!-- Rating Badge -->
                <div class="bg-white/95 backdrop-blur-md px-3 py-1 rounded-full text-xs font-bold text-slate-900 shadow-md flex items-center gap-1.5 border border-white/60">
                    <i class="fas fa-star text-amber-400 text-xs"></i>
                    <span id="modal-room-rating">5.0</span>
                    <span class="text-slate-400 text-[10px] font-medium">/ 5</span>
                </div>
                <!-- Status Badge -->
                <span id="modal-room-status" class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 shadow-sm border border-emerald-200">
                    Available
                </span>
            </div>
            
            <!-- Room Title & Category Overlay at bottom of banner -->
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
        
        <!-- Modal Scrollable Content -->
        <div class="p-6 sm:p-8 space-y-6 overflow-y-auto flex-1">
            
            <!-- Key Highlights Quick Strip -->
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

            <!-- Full Description -->
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2 flex items-center gap-2">
                    <i class="fas fa-align-left text-amber-500"></i> Room Overview & Details
                </h4>
                <div id="modal-room-description" class="text-slate-600 text-sm leading-relaxed whitespace-pre-line bg-slate-50/70 p-4 rounded-2xl border border-slate-100">
                    Room description...
                </div>
            </div>
            
            <!-- Complete Amenities & Features -->
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-2">
                    <i class="fas fa-concierge-bell text-amber-500"></i> Included Amenities & Features
                </h4>
                <div id="modal-room-amenities" class="flex flex-wrap gap-2">
                    <!-- Dynamic amenity badges with icons -->
                </div>
            </div>

            <!-- Inn Policies & Perks -->
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
        
        <!-- Modal Footer Actions Bar -->
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
</div>

<script>
// Preload server-rendered room info for instant modal access
window.serverRoomsData = window.serverRoomsData || {};
<?php if (!empty($rooms)): ?>
    <?php foreach ($rooms as $r): 
        $r_amenities = is_array($r['amenities']) ? $r['amenities'] : json_decode($r['amenities'] ?? '[]', true);
        if (!is_array($r_amenities)) {
            $r_amenities = array_map('trim', explode(',', $r['amenities'] ?? ''));
        }
    ?>
    <?php 
        $r_is_dorm = (intval($r['id']) === 5 || $r['type'] === 'Dorm');
    ?>
    window.serverRoomsData[<?php echo intval($r['id']); ?>] = <?php echo json_encode([
        'id' => intval($r['id']),
        'name' => $r['name'],
        'type' => $r['type'],
        'price' => $r_is_dorm ? 3680.00 : floatval($r['price']),
        'capacity' => $r_is_dorm ? 8 : intval($r['max_capacity'] ?? $r['capacity'] ?? 2),
        'max_capacity' => $r_is_dorm ? 8 : intval($r['max_capacity'] ?? $r['capacity'] ?? 2),
        'image' => $r['image'] ?? 'assets/image/background.jpg',
        'desc' => $r['description'] ?? '',
        'description' => $r['description'] ?? '',
        'amenities' => $r_amenities,
        'rating' => floatval($r['avg_rating'] ?? $r['rating'] ?? 5.0),
        'reviews_count' => intval($r['total_reviews'] ?? 0),
        'status' => $r['status'] ?? 'Available',
        'is_dorm' => $r_is_dorm,
        'bed_price' => 460.00,
        'total_beds' => 8,
        'occupied_beds' => $r_is_dorm ? intval($r['dorm_occupied_beds'] ?? 0) : 0,
        'remaining_beds' => $r_is_dorm ? intval($r['dorm_remaining_beds'] ?? 8) : intval($r['max_capacity'] ?? $r['capacity'] ?? 2)
    ]); ?>;
    <?php endforeach; ?>
<?php endif; ?>

// Initialize reviews for server-rendered room cards once reviews module is available
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.room-card[data-room-id]').forEach(card => {
        const roomId = card.dataset.roomId;
        if (typeof window.initReviewsSection === 'function') {
            window.initReviewsSection(roomId);
        }
    });
});
</script>
