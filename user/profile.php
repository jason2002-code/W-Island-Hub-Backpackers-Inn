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
include '../includes/navbar.php'; 

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
?>

<!-- Profile Header -->
<div class="bg-brand-dark text-white py-12 text-center mt-20"> <!-- mt-20 offsets the fixed navbar -->
    <h1 class="text-4xl font-serif font-bold">My Guest Profile</h1>
    <p class="mt-2 text-brand-sand">Manage your details and view your booking history</p>
</div>

<div class="max-w-6xl mx-auto px-4 py-12 min-h-[50vh]">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <!-- Profile Card Container -->
        <div class="md:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center" id="profile-card-container">
                <!-- This content is managed by the JavaScript below to switch between View and Edit mode -->
            </div>
        </div>
        
        <!-- Booking History Container -->
        <div class="md:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-50 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-brand-dark"><i class="fas fa-history mr-2 text-brand-blue"></i> Booking History</h3>
                </div>
                <div class="p-6" id="user-bookings-container">
                    
                    <?php if (count($bookings) > 0): ?>
                        <?php foreach ($bookings as $booking): 
                            $statusColors = [
                                'Confirmed' => 'bg-green-100 text-green-700',
                                'Cancelled' => 'bg-red-100 text-red-700',
                                'Pending'   => 'bg-yellow-100 text-yellow-700'
                            ];
                            $statusClass = $statusColors[$booking['status']] ?? 'bg-gray-100 text-gray-700';
                        ?>
                    <div class="border border-gray-100 p-4 rounded-xl flex flex-col md:flex-row justify-between md:items-center gap-4 hover:bg-gray-50 transition mb-4">
                        <div>
                            <div class="flex items-center gap-3 mb-1">
                                <h4 class="font-bold text-brand-dark text-lg"><?php echo htmlspecialchars($booking['room_name']); ?></h4>
                                <span class="px-2 py-1 rounded text-xs font-bold <?php echo $statusClass; ?>"><?php echo htmlspecialchars($booking['status']); ?></span>
                            </div>
                            <p class="text-sm text-gray-500"><i class="far fa-calendar-alt mr-1"></i> <?php echo $booking['check_in']; ?> to <?php echo $booking['check_out']; ?> (<?php echo $booking['nights']; ?> nights)</p>
                            <p class="text-xs text-gray-400 mt-1">Ref: <?php echo htmlspecialchars($booking['ref_id']); ?> • Booked on <?php echo date('Y-m-d', strtotime($booking['created_at'])); ?></p>
                        </div>
                        <div class="text-left md:text-right">
                            <p class="text-xs text-gray-500">Total Amount</p>
                            <p class="font-bold text-xl text-brand-blue">$<?php echo number_format($booking['total_price'], 2); ?></p>
                            <p class="text-xs text-gray-400">Via <?php echo htmlspecialchars($booking['payment_method']); ?></p>
                        </div>
                    </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-4">No bookings found.</p>
                    <?php endif; ?>

                </div>
            </div>
        </div>
        
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<!-- Profile Management Script -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Initialize with PHP data
    let userData = { name: "<?php echo htmlspecialchars($user['name']); ?>", email: "<?php echo htmlspecialchars($user['email']); ?>" };
    
    renderProfileView(false);

    // Function to render View or Edit mode
    function renderProfileView(isEditing) {
        const container = document.getElementById('profile-card-container');
        
        if(isEditing) {
            container.innerHTML = `
                <form onsubmit="handleProfileSave(event)" class="text-left space-y-3" action="../actions/profile_action.php" method="POST">
                    <h3 class="text-lg font-bold text-brand-dark mb-4 text-center">Edit Profile</h3>
                    
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Full Name</label>
                        <input type="text" name="full_name" id="edit-name" value="${userData.name}" class="w-full border rounded p-2 text-sm focus:ring-2 focus:ring-brand-blue outline-none" required>
                    </div>
                    
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Email Address</label>
                        <input type="email" name="email" id="edit-email" value="${userData.email}" class="w-full border rounded p-2 text-sm focus:ring-2 focus:ring-brand-blue outline-none" required>
                    </div>
                    
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">New Password <span class="text-gray-400 text-[10px]">(leave blank to keep)</span></label>
                        <input type="password" name="password" id="edit-pass" class="w-full border rounded p-2 text-sm focus:ring-2 focus:ring-brand-blue outline-none" placeholder="••••••••">
                    </div>
                    
                    <div class="flex gap-2 mt-6">
                        <button type="submit" class="flex-1 bg-brand-blue text-white py-2 rounded-lg text-sm font-bold hover:bg-sky-600 transition shadow-md">Save</button>
                        <button type="button" id="cancel-edit-btn" class="flex-1 border border-gray-300 text-gray-600 py-2 rounded-lg text-sm font-bold hover:bg-gray-50 transition">Cancel</button>
                    </div>
                </form>
            `;
            
            // Attach cancel event listener
            document.getElementById('cancel-edit-btn').addEventListener('click', () => renderProfileView(false));
            
        } else {
            container.innerHTML = `
                <div class="w-24 h-24 rounded-full bg-brand-blue text-white flex items-center justify-center text-3xl font-bold mx-auto mb-4">
                    ${userData.name.charAt(0).toUpperCase()}
                </div>
                <h3 class="text-xl font-bold text-brand-dark">${userData.name}</h3>
                <p class="text-gray-500 text-sm mb-4">${userData.email}</p>
                <span class="px-3 py-1 bg-blue-50 text-brand-blue rounded-full text-xs font-bold uppercase tracking-wider">Guest Member</span>
                
                <button id="edit-profile-btn" class="mt-6 w-full border border-brand-blue text-brand-blue hover:bg-blue-50 py-2 rounded-lg transition font-medium text-sm">
                    <i class="fas fa-user-edit mr-2"></i> Edit Profile
                </button>
                
                <hr class="my-6 border-gray-100">
                <a href="../logout.php" class="block w-full border border-red-500 text-red-500 hover:bg-red-50 py-2 rounded-lg transition font-medium text-sm">
                    <i class="fas fa-sign-out-alt mr-2"></i> Sign Out
                </a>
            `;
            
            // Attach edit event listener
            document.getElementById('edit-profile-btn').addEventListener('click', () => renderProfileView(true));
        }
    }

    // Attach to window so form onsubmit can reach it
    window.handleProfileSave = function(e) {
        e.preventDefault();

        const formData = new FormData(e.target);
        const newName = formData.get('full_name').trim();
        const newEmail = formData.get('email').trim();

        fetch('../actions/profile_action.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Update local data
                userData.name = newName;
                userData.email = newEmail;
                // Return to view mode
                renderProfileView(false);
                alert(data.message);
            } else {
                alert(data.message);
            }
        })
        .catch(err => {
            console.error('Profile update error:', err);
            alert('An error occurred while updating your profile.');
        });
    }
});
</script>