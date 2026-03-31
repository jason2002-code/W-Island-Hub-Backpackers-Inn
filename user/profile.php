<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Require user to be logged in as guest
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php#login");
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
    LEFT JOIN rooms r ON b.room_id = r.id 
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
        'roomId' => $b['room_id'] ?? null,
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

// Fetch User Reports safely (handles if admin_response column hasn't been used yet)
$reportStmt = $pdo->prepare("SELECT * FROM reports WHERE user_id = ? ORDER BY created_at DESC");
$reportStmt->execute([$_SESSION['user_id']]);
$userReports = $reportStmt->fetchAll(PDO::FETCH_ASSOC);
?>

 <!-- ================= NEW: CUSTOMER DASHBOARD / PROFILE ================= -->
        <div id="user-profile" class="page-section active">
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
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                            <div class="p-6 border-b border-gray-50 flex justify-between items-center">
                                <h3 class="text-lg font-bold text-brand-dark"><i class="fas fa-history mr-2 text-brand-blue"></i> Booking History</h3>
                                <button onclick="openReportModal()" class="bg-yellow-500 text-white hover:bg-yellow-600 px-4 py-2 rounded-lg text-sm font-bold shadow transition flex items-center">
                                    <i class="fas fa-comment-dots mr-2"></i> Submit Feedback
                                </button>
                            </div>
                        <div class="p-6" id="user-bookings-container">
                            <!-- Bookings populated via JS -->
                        </div>
                        </div>
                        
                        <!-- My Feedback & Reports -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="p-6 border-b border-gray-50 flex justify-between items-center">
                                <h3 class="text-lg font-bold text-brand-dark"><i class="fas fa-comments mr-2 text-brand-blue"></i> My Feedback & Reports</h3>
                            </div>
                            <div class="p-6" id="user-reports-container">
                                <!-- Reports populated via JS -->
                        </div>
                    </div>

                </div>
            </div>
        </div>

<!-- Report/Feedback Modal -->
<div id="report-modal" class="fixed inset-0 bg-black/50 z-[100] hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-brand-dark">Submit Report / Feedback</h3>
            <button onclick="closeReportModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="report-form" onsubmit="submitReport(event)">
            <input type="hidden" id="report-booking-id">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select id="report-type" class="w-full border rounded p-2 focus:ring-2 focus:ring-brand-blue outline-none" required>
                        <option value="feedback">General Feedback</option>
                        <option value="complaint">Complaint</option>
                        <option value="suggestion">Suggestion</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                    <input type="text" id="report-subject" class="w-full border rounded p-2 focus:ring-2 focus:ring-brand-blue outline-none" placeholder="E.g., AC not working, Great service" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                    <textarea id="report-message" rows="4" class="w-full border rounded p-2 focus:ring-2 focus:ring-brand-blue outline-none" placeholder="Please describe the issue or feedback..." required></textarea>
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="w-full bg-brand-blue hover:bg-sky-600 text-white font-bold py-3 rounded-lg transition shadow-md">
                    Submit
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<!-- Hide the loader immediately on profile page -->
<script>
    // Initialize state from PHP for user_profile.js
    window.appState = {
        currentUser: <?php echo json_encode($user); ?>,
        bookings: <?php echo json_encode($jsBookings); ?>,
        reports: <?php echo json_encode($userReports); ?>,
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
            if (window.gsap) {
                gsap.to("#loader", { opacity: 0, duration: 0.5, onComplete: () => {
                    loader.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }});
            } else {
                loader.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }
        
        // Render the profile using the loaded data
        if(typeof renderUserProfile === 'function') {
            renderUserProfile();
        }
    });
</script>

<!-- Profile Management Script -->
<script src="../assets/js/user_profile.js?v=<?php echo time(); ?>"></script>
