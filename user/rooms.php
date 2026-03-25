<?php
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
            $rating = floatval($room['rating'] ?? 5.0);
            $stars = round($rating);
        ?>
            <div class="room-card bg-white rounded-xl shadow-sm hover:shadow-lg transition overflow-hidden border border-gray-100 flex flex-col" data-room-id="<?php echo intval($room['id']); ?>">
                <div class="h-48 overflow-hidden relative">
                    <img src="<?php echo htmlspecialchars($room['image']); ?>" alt="<?php echo htmlspecialchars($room['name']); ?>" class="w-full h-full object-cover transition duration-500 hover:scale-110">
                    <div class="absolute top-2 right-2 bg-white/90 backdrop-blur px-2 py-1 rounded text-xs font-bold text-gray-800">
                        <?php for ($i = 0; $i < $stars; $i++): ?>
                            <i class="fas fa-star text-yellow-400 text-xs"></i>
                        <?php endfor; ?>
                    </div>
                </div>
                <div class="p-6 flex-1 flex flex-col">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-xl font-bold text-brand-dark"><?php echo htmlspecialchars($room['name']); ?></h3>
                        <div>
                            <span class="room-status-badge bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded uppercase mr-1">Available</span>
                            <span class="bg-blue-50 text-brand-blue text-xs font-bold px-2 py-1 rounded uppercase"><?php echo htmlspecialchars($room['type']); ?></span>
                        </div>
                    </div>
                    <p class="text-gray-500 text-sm mb-4 line-clamp-2"><?php echo htmlspecialchars($room['description']); ?></p>
                    
                    <div class="flex flex-wrap gap-2 mb-6">
                        <?php foreach (array_slice($amenities, 0, 3) as $amenity): ?>
                            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($amenity); ?></span>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="mt-auto pt-4 border-t flex justify-between items-center">
                        <div>
                            <span class="text-2xl font-bold text-brand-blue">$<?php echo number_format($room['price'], 2); ?></span>
                            <span class="text-gray-400 text-sm">/ night</span>
                        </div>
                        <button onclick="initiateBooking(<?php echo intval($room['id']); ?>)" class="bg-brand-dark hover:bg-gray-800 text-white px-4 py-2 rounded-lg font-medium transition text-sm">
                            Book Now
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
// Load initial room statuses on page load
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.room-card').forEach(card => {
        const roomId = card.dataset.roomId;
        updateRoomStatus(roomId);
    });
});

async function updateRoomStatus(roomId) {
    try {
        const res = await fetch('actions/book_action.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'get_room_availability', room_id: roomId})
        });
        const data = await res.json();
        const badge = document.querySelector(`[data-room-id="${roomId}"] .room-status-badge`);
        if (badge && data.success) {
            if (data.is_currently_available) {
                badge.textContent = 'Available';
                badge.className = 'room-status-badge bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded uppercase mr-1';
            } else {
                badge.textContent = data.next_vacant ? `Occupied until ${data.next_vacant}` : 'Occupied';
                badge.className = 'room-status-badge bg-orange-100 text-orange-800 text-xs font-bold px-2 py-1 rounded uppercase mr-1';
            }
        }
    } catch (e) {
        console.error('Status update failed:', e);
    }
}
</script>
