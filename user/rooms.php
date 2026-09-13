<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

$pdo = getDB();
$rooms = [];
$error = '';

if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM rooms ORDER BY created_at DESC");
        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            $rating = floatval($room['rating'] ?? 5.0);
            $stars = round($rating);
            $room_id = intval($room['id']);
        ?>
            <?php 
                $room_capacity = intval($room['capacity'] ?? 2); 
                $room_max_capacity = intval($room['max_capacity'] ?? $room_capacity);
            ?>
            <div class="room-card bg-white rounded-xl shadow-sm hover:shadow-lg transition overflow-hidden border border-gray-100 flex flex-col" data-room-id="<?php echo $room_id; ?>" data-capacity="<?php echo $room_capacity; ?>" data-max-capacity="<?php echo $room_max_capacity; ?>">
                <div class="h-48 overflow-hidden relative cursor-pointer group" onclick="openRoomInfoModal(<?php echo $room_id; ?>)" title="Click to view full room information">
                    <img src="<?php echo htmlspecialchars($room['image']); ?>" alt="<?php echo htmlspecialchars($room['name']); ?>" class="w-full h-full object-cover transition duration-500 group-hover:scale-110">
                    <div class="absolute inset-0 bg-slate-950/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                        <span class="bg-white/95 backdrop-blur-md text-slate-900 text-xs font-bold px-3.5 py-1.5 rounded-full shadow-md flex items-center gap-1.5 transform translate-y-2 group-hover:translate-y-0 transition-transform">
                            <i class="fas fa-eye text-brand-blue"></i> View Details
                        </span>
                    </div>
                    <div class="absolute top-3 right-3 bg-white/95 backdrop-blur-md px-3 py-1.5 rounded-full text-xs font-bold text-slate-900 shadow-md flex items-center gap-1.5 border border-white/60">
                        <i class="fas fa-star text-amber-400 text-xs"></i>
                        <span><?php echo number_format($rating, 1); ?></span>
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
                        <span class="text-xs text-brand-blue bg-blue-50 px-2 py-1 rounded font-semibold flex items-center gap-1"><i class="fas fa-user-friends text-[10px]"></i> Up to <?php echo $room_max_capacity; ?> Guest<?php echo $room_max_capacity > 1 ? 's' : ''; ?></span>
                        <?php foreach (array_slice($amenities, 0, 3) as $amenity): ?>
                            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($amenity); ?></span>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Reviews Section -->
                    <details class="reviews-section group mt-4 border-t border-slate-100 pt-4">
                        <summary class="cursor-pointer font-semibold text-slate-700 py-2 px-3.5 bg-slate-50 hover:bg-slate-100 rounded-2xl flex items-center justify-between transition-all duration-200 border border-slate-200/60 select-none">
                            <div class="flex items-center gap-2 text-xs font-bold">
                                <i class="fas fa-star text-amber-400"></i>
                                <span>Guest Reviews</span>
                                <span class="reviews-count text-[11px] font-bold bg-white border border-slate-200 text-slate-600 px-2 py-0.5 rounded-full">(Loading...)</span>
                            </div>
                            <i class="fas fa-chevron-down text-xs text-slate-400 transition-transform duration-300 group-open:rotate-180"></i>
                        </summary>
                        
                        <div class="reviews-content space-y-3 mt-3" id="reviews-<?php echo $room_id; ?>">
                            <!-- Dynamic content loaded by JS -->
                        </div>

                        <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="submit-review-section mt-4 p-4 bg-slate-50/80 rounded-2xl border border-slate-200/70" data-room-id="<?php echo $room_id; ?>">
                            <h4 class="font-bold text-slate-900 text-xs mb-3 flex items-center gap-1.5">
                                <i class="fas fa-pen-fancy text-amber-500"></i>
                                Write Your Review
                            </h4>
                            <div class="star-picker mb-3 flex items-center gap-1" data-room-id="<?php echo $room_id; ?>">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="review-star far fa-star text-xl text-slate-300 hover:text-amber-300 cursor-pointer transition-all duration-200" data-rating="<?php echo $i; ?>" title="Rate <?php echo $i; ?> star<?php echo $i > 1 ? 's' : ''; ?>"></i>
                                <?php endfor; ?>
                                <span class="ml-2 font-medium text-xs text-slate-500 selected-rating">Select rating</span>
                            </div>
                            <textarea id="review-comment-<?php echo $room_id; ?>" placeholder="Share your experience with this room... What did you like? Any suggestions?" class="w-full p-3 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none resize-vertical min-h-[80px]" maxlength="500"></textarea>
                            <div class="text-right mt-1 text-[11px] text-slate-400">Max 500 characters</div>
                            <button class="submit-review-btn mt-3 bg-slate-900 hover:bg-amber-500 hover:text-slate-950 text-white px-5 py-2 rounded-xl text-xs font-bold transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed w-full sm:w-auto shadow-sm" disabled>
                                <span class="btn-text">Submit Review</span>
                                <i class="fas fa-spinner fa-spin hidden ml-2"></i>
                            </button>
                            <div class="submit-message mt-2 hidden text-xs"></div>
                        </div>
                        <?php endif; ?>
                    </details>

                    <div class="mt-auto pt-4 border-t flex justify-between items-center gap-2">
                        <div>
                            <span class="text-2xl font-bold text-brand-blue">₱<?php echo number_format($room['price'], 2); ?></span>
                            <span class="text-gray-400 text-sm">/ night</span>
                        </div>
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
    window.serverRoomsData[<?php echo intval($r['id']); ?>] = <?php echo json_encode([
        'id' => intval($r['id']),
        'name' => $r['name'],
        'type' => $r['type'],
        'price' => floatval($r['price']),
        'capacity' => intval($r['capacity'] ?? 2),
        'image' => $r['image'] ?? 'assets/image/background.jpg',
        'desc' => $r['description'] ?? '',
        'description' => $r['description'] ?? '',
        'amenities' => $r_amenities,
        'rating' => floatval($r['rating'] ?? 5.0),
        'status' => $r['status'] ?? 'Available'
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
