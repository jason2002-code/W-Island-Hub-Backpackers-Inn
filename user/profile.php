<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Require user to be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user/login.php");
    exit();
}

require_once '../config/db.php';
include '../includes/header.php'; 
//include '../includes/navbar.php'; 

$pdo = getDB();

// Fetch User Details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch Booking History
$bookingStmt = $pdo->prepare("
    SELECT b.*, r.name as room_name 
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.id 
    WHERE b.user_id = ? 
    ORDER BY b.created_at DESC
");
$bookingStmt->execute([$_SESSION['user_id']]);
$bookings = $bookingStmt->fetchAll(PDO::FETCH_ASSOC);

// Map DB columns to JS expected format (camelCase)
$jsBookings = array_map(function($b) {
    return [
        'id' => $b['id'],
        'roomName' => $b['room_name'] ?? 'Room',
        'checkIn' => $b['check_in'] ?? $b['checkIn'] ?? '',
        'checkOut' => $b['check_out'] ?? $b['checkOut'] ?? '',
        'nights' => $b['nights'] ?? 1,
        'dateBooked' => $b['created_at'] ?? '',
        'total' => $b['total_price'] ?? $b['total'] ?? 0,
        'status' => $b['status'],
        'paymentMethod' => $b['payment_method'] ?? 'Online',
        'userId' => $b['user_id']
    ];
}, $bookings);
?>

 <!-- ================= NEW: CUSTOMER DASHBOARD / PROFILE ================= -->
        <div id="user-profile" class="page-section">
            <div class="bg-brand-dark text-white py-12 text-center">
                <h1 class="text-4xl font-serif font-bold">My Guest Profile</h1>
                <p class="mt-2 text-brand-sand">Manage your details and view your booking history</p>
            </div>
            <div class="max-w-6xl mx-auto px-4 py-12">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Profile Card -->
                    <div class="md:col-span-1">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center" id="profile-card-container">
                            <!-- Injected via JS -->
                        </div>
                    </div>
                    
                    <!-- Booking History -->
                    <div class="md:col-span-2">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="p-6 border-b border-gray-50 flex justify-between items-center">
                                <h3 class="text-lg font-bold text-brand-dark"><i class="fas fa-history mr-2 text-brand-blue"></i> Booking History</h3>
                            </div>
                        <div class="p-6" id="user-bookings-container">
                            <!-- Bookings populated via JS -->
                        </div>
                    </div>

                </div>
            </div>
        </div>

<?php include '../includes/footer.php'; ?>

<!-- Hide the loader immediately on profile page -->
<script>
    // Initialize state from PHP for user_profile.js
    window.appState = {
        currentUser: <?php echo json_encode($user); ?>,
        bookings: <?php echo json_encode($jsBookings); ?>,
        users: [] // Empty for security
    };

    // Mock functions required by user_profile.js
    window.saveState = function() {
        if(appState.currentUser) sessionStorage.setItem('w_currentUser', JSON.stringify(appState.currentUser));
    };
    
    window.updateAuthUI = function() {
        // Reload to reflect profile changes
        location.reload();
    };
    
    window.router = function(page) {
        window.location.href = '../index.php#' + page;
    };

    document.addEventListener('DOMContentLoaded', function() {
        const loader = document.getElementById('loader');
        if(loader) {
            loader.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        // Render the profile using the loaded data
        if(typeof renderUserProfile === 'function') {
            renderUserProfile();
        }
    });
</script>

<!-- Profile Management Script -->
<script src="../assets/js/user_profile.js"> </script>
