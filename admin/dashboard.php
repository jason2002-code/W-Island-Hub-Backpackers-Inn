<?php
/**
 * ADMIN DASHBOARD
 * Main container for the administration panel.
 * Loads sub-modules via PHP include and handles session security.
 */
// Start session and check if user is logged in
session_start();

// Check if user is logged in and is admin
if (isset($_SESSION['admin_id']) && $_SESSION['admin_role'] === 'admin') {
    // PHP session is valid - allow access
    $isValidSession = true;
    $adminName = $_SESSION['admin_name'] ?? 'Admin User';
    $adminInitial = strtoupper(substr($adminName, 0, 1));
} else {
    // PHP session is not valid. Redirect server-side.
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>W Island Hub - Admin Panel</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Chart.js for Admin Dashboard -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            blue: '#0ea5e9',
                            dark: '#0f172a',
                            green: '#22c55e',
                            purple: '#6C5DD3'
                        }
                    },
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    }
                }
            }
        }

    </script>

    <style>
       /* Admin Section Visibility Logic */
       .page-section { display: none; }
       .page-section.active { display: block; animation: fadeIn 0.3s ease-in-out; }
       @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-gray-50 text-slate-800 font-sans">

    <div class="flex h-screen overflow-hidden relative">
        <!-- Sidebar Overlay for mobile -->
        <div id="admin-sidebar-overlay" class="fixed inset-0 bg-black/50 z-20 hidden md:hidden" onclick="toggleAdminSidebar()"></div>
        
        <!-- Modern Sidebar -->
        <div id="admin-sidebar" class="absolute inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 w-64 bg-white text-gray-600 border-r border-gray-100 transition duration-200 ease-in-out z-30 flex flex-col h-full shadow-lg md:shadow-none">
            <div class="p-6 h-20 flex items-center border-b border-gray-50">
                <span class="text-2xl font-bold text-brand-dark flex items-center gap-2">
                     <div class="w-8 h-8 rounded-lg bg-brand-purple flex items-center justify-center text-white text-lg font-serif">W</div>
                     Admin
                </span>
                <button onclick="toggleAdminSidebar()" class="md:hidden ml-auto text-gray-400 hover:text-brand-dark"><i class="fas fa-times"></i></button>
            </div>
            
            <nav class="mt-6 flex-1 px-4 space-y-2 overflow-y-auto">
                <p class="px-4 text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Main Menu</p>
                
                <a onclick="showAdminSection('admin-overview'); setActiveAdminLink(this)" class="admin-link flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-50 hover:text-brand-purple cursor-pointer transition text-brand-purple bg-blue-50 font-medium">
                    <i class="fas fa-chart-pie w-5"></i> Dashboard
                </a>
                <a onclick="showAdminSection('manage-bookings'); setActiveAdminLink(this)" class="admin-link flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-50 hover:text-brand-purple cursor-pointer transition" id="nav-bookings">
                    <i class="fas fa-calendar-alt w-5"></i> Bookings
                    <span id="booking-count-badge" class="ml-auto bg-yellow-100 text-yellow-700 text-xs font-bold px-2 py-0.5 rounded-full hidden">0</span>
                </a>
                <a onclick="showAdminSection('manage-rooms'); setActiveAdminLink(this)" class="admin-link flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-50 hover:text-brand-purple cursor-pointer transition">
                    <i class="fas fa-door-open w-5"></i> Rooms
                </a>
                
                <p class="px-4 text-xs font-bold text-gray-400 uppercase tracking-wider mt-6 mb-2">Management</p>
                
                <a onclick="showAdminSection('manage-users'); setActiveAdminLink(this)" class="admin-link flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-50 hover:text-brand-purple cursor-pointer transition">
                    <i class="fas fa-users w-5"></i> Users
                </a>
                <a onclick="showAdminSection('manage-reports'); setActiveAdminLink(this)" class="admin-link flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-50 hover:text-brand-purple cursor-pointer transition">
                    <i class="fas fa-comment-dots w-5"></i> Reports/Feedback
                </a>
<a onclick="showAdminSection('manage-sliders'); setActiveAdminLink(this)" class="admin-link flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-50 hover:text-brand-purple cursor-pointer transition">
                    <i class="fas fa-images w-5"></i> Carousel/Sliders
                </a>
                <a onclick="showAdminSection('manage-reviews'); setActiveAdminLink(this)" class="admin-link flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-50 hover:text-brand-purple cursor-pointer transition">
                    <i class="fas fa-star w-5 text-yellow-400"></i> Reviews & Ratings
                </a>
            </nav>

            <div class="p-4 border-t border-gray-100">
                <a href="../index.php" class="flex items-center gap-3 px-4 py-3 text-blue-500 hover:bg-blue-50 w-full rounded-xl transition font-medium cursor-pointer">
                    <i class="fas fa-home"></i> Back to Main Site
                </a>
            </div>
          
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 overflow-auto w-full bg-gray-50/50">
            <!-- Top Bar -->
            <header class="bg-white/80 backdrop-blur sticky top-0 z-10 px-8 py-4 flex justify-between items-center border-b border-gray-100">
                <div class="flex items-center gap-4">
                    <button onclick="toggleAdminSidebar()" class="md:hidden text-gray-600 hover:text-brand-dark">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h2 class="text-xl font-bold text-brand-dark" id="admin-page-title">Analytics Overview</h2>
                </div>
                <div class="flex items-center gap-6">

                    <div class="relative hidden sm:block">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" placeholder="Search..." class="pl-10 pr-4 py-2 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-purple/20 w-48 transition">
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-brand-dark text-white flex items-center justify-center font-bold" id="admin-avatar"><?php echo htmlspecialchars($adminInitial); ?></div>
                        <div class="hidden md:block">
                            <p class="text-sm font-bold text-gray-800" id="admin-name"><?php echo htmlspecialchars($adminName); ?></p>
                            <p class="text-xs text-gray-500">Super Admin</p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- MAIN CONTENT CONTAINER -->
            <!-- Dynamic Content -->
            <div class="p-4 sm:p-8 max-w-7xl mx-auto" id="admin-content">
                <!-- Dashboard Overview Section -->
                <div id="admin-overview" class="page-section active">
                    <div class="flex items-center justify-center h-64">
                        <div class="text-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-brand-purple mx-auto mb-4"></div>
                            <p class="text-gray-500">Loading analytics...</p>
                        </div>
                    </div>
                </div>

                <!-- Include Management Sections -->
                <?php include 'manage_bookings.php'; ?>
                <?php include 'manage_rooms.php'; ?>
                <?php include 'manage_users.php'; ?>
                <?php include 'reports.php'; ?>
                <?php include 'manage_sliders.php'; ?>
                <?php include 'manage_reviews.php'; ?>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT LOGIC -->
    <script src="../assets/js/admin_chart.js?v=<?php echo time(); ?>"></script>
    <script src="../assets/js/notifications.js?v=<?php echo time(); ?>"></script>
   
    <script>
        // Explicitly initialize admin dashboard after script loads
        if (typeof initAdmin === 'function') {
            initAdmin();
        }
        
        function showAdminSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.page-section').forEach(el => el.classList.remove('active'));
            // Show target section
            const target = document.getElementById(sectionId);
            if(target) target.classList.add('active');
            
            // Update title
            const titles = {
                'admin-overview': 'Analytics Overview',
                'manage-bookings': 'Booking Management',
                'manage-rooms': 'Room Inventory',
                'manage-users': 'User Management',
                'manage-reports': 'Guest Feedback',
                'manage-sliders': 'Carousel & Sliders',
                'manage-reviews': 'Reviews & Ratings',
            };
            document.getElementById('admin-page-title').innerText = titles[sectionId] || 'Admin Panel';
        }

        // Load booking counts for sidebar badge
        function loadBookingCounts() {
            fetch('../actions/bookings_action.php?action=count')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.counts.pending > 0) {
                        const badge = document.getElementById('booking-count-badge');
                        badge.textContent = data.counts.pending;
                        badge.classList.remove('hidden');
                    }
                })
                .catch(error => console.error('Error loading booking counts:', error));
        }

        // Load counts on page load
        document.addEventListener('DOMContentLoaded', loadBookingCounts);
    </script>
   
  
</body>
</html>
