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
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>W Island Hub - Admin Panel</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Chart.js for Admin Dashboard -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            blue: '#0284c7',
                            dark: '#0f172a',
                            green: '#10b981',
                            purple: '#6C5DD3',
                            purpleDark: '#5644C4'
                        }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Poppins', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
       /* Admin Section Visibility Logic */
       .page-section { display: none; }
       .page-section.active { display: block; animation: fadeIn 0.25s cubic-bezier(0.16, 1, 0.3, 1); }
       @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
       
       /* Touch friendly tap highlights */
       * { -webkit-tap-highlight-color: transparent; }

       /* Custom sleek scrollbars */
       ::-webkit-scrollbar { width: 6px; height: 6px; }
       ::-webkit-scrollbar-track { background: transparent; }
       ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
       ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
       
       /* Mobile Safe Area Bottom */
       @supports(padding: max(0px)) {
           .safe-bottom { padding-bottom: max(0.5rem, env(safe-area-inset-bottom)); }
       }
    </style>
</head>
<body class="bg-slate-50/60 text-slate-800 font-sans antialiased selection:bg-brand-purple/20 selection:text-brand-purple">

    <div class="flex h-screen overflow-hidden relative">
        <!-- Sidebar Overlay for mobile -->
        <div id="admin-sidebar-overlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-40 hidden md:hidden transition-opacity duration-300 opacity-0" onclick="toggleAdminSidebar()"></div>
        
        <!-- Modern Sidebar -->
        <aside id="admin-sidebar" class="fixed inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 w-72 bg-white text-slate-600 border-r border-slate-200/80 transition-transform duration-300 ease-in-out z-50 flex flex-col h-full shadow-2xl md:shadow-none">
            <!-- Sidebar Header -->
            <div class="px-6 h-20 flex items-center justify-between border-b border-slate-100">
                <a href="../index.php" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-brand-purple to-purple-500 flex items-center justify-center text-white text-lg font-bold shadow-md shadow-brand-purple/20 group-hover:scale-105 transition-transform">
                        W
                    </div>
                    <div>
                        <span class="text-lg font-extrabold text-slate-900 tracking-tight leading-none block">W Island Hub</span>
                        <span class="text-[11px] font-semibold text-brand-purple uppercase tracking-wider">Admin PMS</span>
                    </div>
                </a>
                <button onclick="toggleAdminSidebar()" class="md:hidden w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition active:scale-95">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
            
            <!-- Navigation Links -->
            <nav class="mt-4 flex-1 px-4 space-y-1.5 overflow-y-auto">
                <p class="px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Main Menu</p>
                
                <a onclick="showAdminSection('admin-overview')" id="nav-overview" class="admin-link flex items-center gap-3.5 px-3.5 py-3 rounded-2xl hover:bg-purple-50/70 hover:text-brand-purple cursor-pointer transition text-brand-purple bg-purple-50 font-semibold group active:scale-[0.98]">
                    <div class="w-8 h-8 rounded-xl bg-purple-100/70 text-brand-purple flex items-center justify-center text-sm transition group-hover:scale-110">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <span>Dashboard</span>
                </a>
                <a onclick="showAdminSection('manage-bookings')" id="nav-bookings" class="admin-link flex items-center gap-3.5 px-3.5 py-3 rounded-2xl hover:bg-purple-50/70 hover:text-brand-purple text-slate-600 cursor-pointer transition font-medium group active:scale-[0.98]">
                    <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center text-sm transition group-hover:bg-purple-100/70 group-hover:text-brand-purple">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <span>Bookings</span>
                    <span id="booking-count-badge" class="ml-auto bg-amber-100 text-amber-800 text-[11px] font-bold px-2 py-0.5 rounded-full hidden animate-pulse">0</span>
                </a>

                <!-- Rooms Dropdown -->
                <div class="space-y-1">
                    <button type="button" onclick="toggleRoomsDropdown()" id="rooms-parent-btn" class="w-full flex items-center justify-between px-3.5 py-3 rounded-2xl hover:bg-purple-50/70 hover:text-brand-purple text-slate-600 transition group cursor-pointer font-medium select-none active:scale-[0.98]">
                        <div class="flex items-center gap-3.5">
                            <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center text-sm transition group-hover:bg-purple-100/70 group-hover:text-brand-purple">
                                <i class="fas fa-door-open"></i>
                            </div>
                            <span>Rooms</span>
                        </div>
                        <i id="rooms-dropdown-chevron" class="fas fa-chevron-down text-xs text-slate-400 group-hover:text-brand-purple transition-transform duration-200"></i>
                    </button>
                    <div id="rooms-dropdown-menu" class="hidden pl-4 pr-1 space-y-1 border-l-2 border-purple-100 ml-6 my-1">
                        <a onclick="showAdminSection('manage-rooms')" class="admin-link flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm text-slate-600 hover:bg-purple-50 hover:text-brand-purple cursor-pointer transition font-medium" id="nav-rooms-inventory">
                            <i class="fas fa-bed w-4 text-xs text-slate-400"></i>
                            <span>Manage Rooms</span>
                        </a>
                        <a onclick="showAdminSection('manage-prices')" class="admin-link flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm text-slate-600 hover:bg-purple-50 hover:text-brand-purple cursor-pointer transition font-medium" id="nav-rooms-prices">
                            <i class="fas fa-tags w-4 text-xs text-slate-400"></i>
                            <span>Room Prices</span>
                        </a>
                    </div>
                </div>
                
                <p class="px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider mt-5 mb-2">Management</p>
                
                <a onclick="showAdminSection('manage-users')" id="nav-users" class="admin-link flex items-center gap-3.5 px-3.5 py-3 rounded-2xl hover:bg-purple-50/70 hover:text-brand-purple text-slate-600 cursor-pointer transition font-medium group active:scale-[0.98]">
                    <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center text-sm transition group-hover:bg-purple-100/70 group-hover:text-brand-purple">
                        <i class="fas fa-users"></i>
                    </div>
                    <span>Users</span>
                </a>
                <a onclick="showAdminSection('manage-reports')" id="nav-reports" class="admin-link flex items-center gap-3.5 px-3.5 py-3 rounded-2xl hover:bg-purple-50/70 hover:text-brand-purple text-slate-600 cursor-pointer transition font-medium group active:scale-[0.98]">
                    <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center text-sm transition group-hover:bg-purple-100/70 group-hover:text-brand-purple">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <span>Reports & Analytics</span>
                </a>

                <!-- Carousel / Sliders & Amenities Dropdown -->
                <div class="space-y-1">
                    <button type="button" onclick="toggleSlidersDropdown()" id="sliders-parent-btn" class="w-full flex items-center justify-between px-3.5 py-3 rounded-2xl hover:bg-purple-50/70 hover:text-brand-purple text-slate-600 transition group cursor-pointer font-medium select-none active:scale-[0.98]">
                        <div class="flex items-center gap-3.5">
                            <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center text-sm transition group-hover:bg-purple-100/70 group-hover:text-brand-purple">
                                <i class="fas fa-images"></i>
                            </div>
                            <span>Carousel / Sliders</span>
                        </div>
                        <i id="sliders-dropdown-chevron" class="fas fa-chevron-down text-xs text-slate-400 group-hover:text-brand-purple transition-transform duration-200"></i>
                    </button>
                    <div id="sliders-dropdown-menu" class="hidden pl-4 pr-1 space-y-1 border-l-2 border-purple-100 ml-6 my-1">
                        <a onclick="showAdminSection('manage-sliders')" class="admin-link flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm text-slate-600 hover:bg-purple-50 hover:text-brand-purple cursor-pointer transition font-medium" id="nav-sliders-manage">
                            <i class="fas fa-photo-video w-4 text-xs text-slate-400"></i>
                            <span>Manage Sliders</span>
                        </a>
                        <a onclick="showAdminSection('manage-amenities')" class="admin-link flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm text-slate-600 hover:bg-purple-50 hover:text-brand-purple cursor-pointer transition font-medium" id="nav-amenities-manage">
                            <i class="fas fa-concierge-bell w-4 text-xs text-slate-400"></i>
                            <span>Amenities</span>
                        </a>
                    </div>
                </div>

                <a onclick="showAdminSection('manage-reviews')" id="nav-reviews" class="admin-link flex items-center gap-3.5 px-3.5 py-3 rounded-2xl hover:bg-purple-50/70 hover:text-brand-purple text-slate-600 cursor-pointer transition font-medium group active:scale-[0.98]">
                    <div class="w-8 h-8 rounded-xl bg-slate-100 text-amber-500 flex items-center justify-center text-sm transition group-hover:bg-amber-100/70">
                        <i class="fas fa-star"></i>
                    </div>
                    <span>Reviews & Ratings</span>
                </a>
            </nav>

            <!-- Sidebar Footer with User Card -->
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                <div class="flex items-center gap-3 p-2 bg-white rounded-2xl border border-slate-200/70 shadow-2xs mb-2">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-slate-900 to-indigo-900 text-white flex items-center justify-center font-bold text-sm shadow-xs shrink-0">
                        <?php echo htmlspecialchars($adminInitial); ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-slate-800 truncate"><?php echo htmlspecialchars($adminName); ?></p>
                        <p class="text-[10px] text-emerald-600 font-semibold flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Super Admin
                        </p>
                    </div>
                    <a href="../logout.php?type=admin" title="Logout" class="w-8 h-8 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center transition">
                        <i class="fas fa-sign-out-alt text-xs"></i>
                    </a>
                </div>
                <a href="../index.php" class="flex items-center justify-center gap-2 px-3 py-2 text-xs text-brand-purple hover:bg-purple-50 w-full rounded-xl transition font-semibold">
                    <i class="fas fa-home"></i> View Main Site
                </a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 overflow-auto w-full bg-slate-50/60 flex flex-col min-h-screen">
            <!-- Top Bar -->
            <header class="bg-white/90 backdrop-blur-md sticky top-0 z-20 px-4 sm:px-6 lg:px-8 py-3.5 flex justify-between items-center border-b border-slate-200/70 shadow-2xs">
                <div class="flex items-center gap-3">
                    <button onclick="toggleAdminSidebar()" class="md:hidden w-10 h-10 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition active:scale-95" aria-label="Open navigation menu">
                        <i class="fas fa-bars text-base"></i>
                    </button>
                    <div>
                        <h2 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight leading-tight flex items-center gap-2" id="admin-page-title">
                            Analytics Overview
                        </h2>
                        <p class="text-[11px] text-slate-400 hidden sm:block">W Island Hub Backpackers Inn • Management Console</p>
                    </div>
                </div>

                <div class="flex items-center gap-2.5 sm:gap-4">
                    <!-- Live Site Link -->
                    <a href="../index.php" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition active:scale-95" title="Open Guest Site">
                        <i class="fas fa-external-link-alt text-[10px]"></i>
                        <span>Live Site</span>
                    </a>

                    <!-- Admin Profile Dropdown Button -->
                    <div class="relative">
                        <button onclick="toggleAdminProfileMenu()" class="flex items-center gap-2.5 p-1 sm:px-3 sm:py-1.5 rounded-2xl hover:bg-slate-100 transition active:scale-95 select-none" id="admin-profile-btn">
                            <div class="w-9 h-9 sm:w-8 sm:h-8 rounded-xl bg-gradient-to-tr from-brand-purple to-purple-600 text-white flex items-center justify-center font-bold text-sm shadow-xs" id="admin-avatar">
                                <?php echo htmlspecialchars($adminInitial); ?>
                            </div>
                            <div class="hidden sm:block text-left">
                                <p class="text-xs font-bold text-slate-800 leading-tight"><?php echo htmlspecialchars($adminName); ?></p>
                                <p class="text-[10px] text-slate-400">Super Admin</p>
                            </div>
                            <i class="fas fa-chevron-down text-[10px] text-slate-400 hidden sm:inline ml-0.5"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="admin-profile-dropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 p-2 z-50 animate-in fade-in slide-in-from-top-2 duration-150">
                            <div class="px-3 py-2 border-b border-slate-100 mb-1">
                                <p class="text-xs font-bold text-slate-800"><?php echo htmlspecialchars($adminName); ?></p>
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-purple-50 text-brand-purple font-semibold">Admin Authorized</span>
                            </div>
                            <a href="../index.php" class="flex items-center gap-2.5 px-3 py-2 text-xs text-slate-700 hover:bg-slate-50 rounded-xl font-medium transition">
                                <i class="fas fa-globe text-slate-400"></i> Go to Guest Website
                            </a>
                            <a onclick="showAdminSection('manage-reports'); toggleAdminProfileMenu();" class="flex items-center gap-2.5 px-3 py-2 text-xs text-slate-700 hover:bg-slate-50 rounded-xl font-medium transition cursor-pointer">
                                <i class="fas fa-file-invoice-dollar text-slate-400"></i> Property Reports
                            </a>
                            <div class="border-t border-slate-100 my-1"></div>
                            <a href="../logout.php?type=admin" class="flex items-center gap-2.5 px-3 py-2 text-xs text-rose-600 hover:bg-rose-50 rounded-xl font-semibold transition">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- MAIN CONTENT CONTAINER -->
            <!-- Dynamic Content -->
            <main class="p-3.5 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto pb-24 md:pb-8 flex-1" id="admin-content">
                <!-- Dashboard Overview Section -->
                <div id="admin-overview" class="page-section active">
                    <?php include 'sections/dashboard_stats.php'; ?>
                </div>

                <!-- Include Management Sections -->
                <?php include 'manage_bookings.php'; ?>
                <?php include 'manage_rooms.php'; ?>
                <?php include 'manage_prices.php'; ?>
                <?php include 'manage_users.php'; ?>
                <?php include 'reports.php'; ?>
                <?php include 'manage_sliders.php'; ?>
                <?php include 'manage_amenities.php'; ?>
                <?php include 'manage_reviews.php'; ?>
            </main>
        </div>
    </div>

    <!-- Mobile Bottom Navigation Bar (App-Style UX for Phones) -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-30 bg-white/95 backdrop-blur-md border-t border-slate-200/80 px-2 py-1.5 flex justify-around items-center shadow-lg safe-bottom">
        <button onclick="showAdminSection('admin-overview')" class="mobile-nav-item flex flex-col items-center justify-center p-2 rounded-xl text-brand-purple transition active:scale-95" id="mob-nav-overview">
            <i class="fas fa-chart-pie text-lg mb-0.5"></i>
            <span class="text-[10px] font-semibold">Overview</span>
        </button>
        <button onclick="showAdminSection('manage-bookings')" class="mobile-nav-item relative flex flex-col items-center justify-center p-2 rounded-xl text-slate-400 hover:text-brand-purple transition active:scale-95" id="mob-nav-bookings">
            <i class="fas fa-calendar-alt text-lg mb-0.5"></i>
            <span class="text-[10px] font-semibold">Bookings</span>
            <span id="booking-mobile-badge" class="absolute top-1 right-2 w-2 h-2 rounded-full bg-amber-500 hidden animate-pulse"></span>
        </button>
        <button onclick="showAdminSection('manage-rooms')" class="mobile-nav-item flex flex-col items-center justify-center p-2 rounded-xl text-slate-400 hover:text-brand-purple transition active:scale-95" id="mob-nav-rooms">
            <i class="fas fa-door-open text-lg mb-0.5"></i>
            <span class="text-[10px] font-semibold">Rooms</span>
        </button>
        <button onclick="showAdminSection('manage-reports')" class="mobile-nav-item flex flex-col items-center justify-center p-2 rounded-xl text-slate-400 hover:text-brand-purple transition active:scale-95" id="mob-nav-reports">
            <i class="fas fa-file-invoice-dollar text-lg mb-0.5"></i>
            <span class="text-[10px] font-semibold">Reports</span>
        </button>
        <button onclick="toggleAdminSidebar()" class="mobile-nav-item flex flex-col items-center justify-center p-2 rounded-xl text-slate-400 hover:text-brand-purple transition active:scale-95">
            <i class="fas fa-bars text-lg mb-0.5"></i>
            <span class="text-[10px] font-semibold">Menu</span>
        </button>
    </nav>

    <!-- JAVASCRIPT LOGIC -->
    <script src="../assets/js/admin_chart.js?v=<?php echo time(); ?>"></script>
    <script src="../assets/js/notifications.js?v=<?php echo time(); ?>"></script>
   
    <script>
        // Explicitly initialize admin dashboard after script loads
        if (typeof initAdmin === 'function') {
            initAdmin();
        }
        
        function toggleAdminProfileMenu() {
            const dropdown = document.getElementById('admin-profile-dropdown');
            if (dropdown) dropdown.classList.toggle('hidden');
        }

        // Close profile dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const btn = document.getElementById('admin-profile-btn');
            const dropdown = document.getElementById('admin-profile-dropdown');
            if (dropdown && !dropdown.classList.contains('hidden') && btn && !btn.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });

        function toggleAdminSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            const overlay = document.getElementById('admin-sidebar-overlay');
            if (!sidebar || !overlay) return;
            const isClosed = sidebar.classList.contains('-translate-x-full');
            if (isClosed) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                setTimeout(() => { overlay.classList.remove('opacity-0'); overlay.classList.add('opacity-100'); }, 10);
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.remove('opacity-100');
                overlay.classList.add('opacity-0');
                setTimeout(() => { overlay.classList.add('hidden'); }, 300);
            }
        }

        function toggleRoomsDropdown(forceOpen = null) {
            const menu = document.getElementById('rooms-dropdown-menu');
            const chevron = document.getElementById('rooms-dropdown-chevron');
            if (!menu) return;
            
            const isClosed = menu.classList.contains('hidden');
            const shouldOpen = forceOpen !== null ? forceOpen : isClosed;
            
            if (shouldOpen) {
                menu.classList.remove('hidden');
                if (chevron) chevron.classList.add('rotate-180');
            } else {
                menu.classList.add('hidden');
                if (chevron) chevron.classList.remove('rotate-180');
            }
        }

        function toggleSlidersDropdown(forceOpen = null) {
            const menu = document.getElementById('sliders-dropdown-menu');
            const chevron = document.getElementById('sliders-dropdown-chevron');
            if (!menu) return;
            
            const isClosed = menu.classList.contains('hidden');
            const shouldOpen = forceOpen !== null ? forceOpen : isClosed;
            
            if (shouldOpen) {
                menu.classList.remove('hidden');
                if (chevron) chevron.classList.add('rotate-180');
            } else {
                menu.classList.add('hidden');
                if (chevron) chevron.classList.remove('rotate-180');
            }
        }

        function setActiveAdminLink(element) {
            // Reset all admin links to normal state
            document.querySelectorAll('.admin-link').forEach(link => {
                link.classList.remove('bg-purple-50', 'text-brand-purple', 'font-semibold', 'shadow-xs');
                link.classList.add('text-slate-600', 'font-medium');
                const iconBox = link.querySelector('div');
                if (iconBox) {
                    iconBox.classList.remove('bg-purple-100/70', 'text-brand-purple');
                    iconBox.classList.add('bg-slate-100', 'text-slate-500');
                }
            });

            // Activate target link
            if (element) {
                element.classList.remove('text-slate-600', 'font-medium');
                element.classList.add('bg-purple-50', 'text-brand-purple', 'font-semibold', 'shadow-xs');
                const iconBox = element.querySelector('div');
                if (iconBox) {
                    iconBox.classList.remove('bg-slate-100', 'text-slate-500');
                    iconBox.classList.add('bg-purple-100/70', 'text-brand-purple');
                }
            }

            // Sync parent rooms dropdown state
            const roomsParent = document.getElementById('rooms-parent-btn');
            const roomsDropdown = document.getElementById('rooms-dropdown-menu');
            if (roomsParent && roomsDropdown) {
                if (element && roomsDropdown.contains(element)) {
                    roomsParent.classList.add('text-brand-purple', 'font-semibold', 'bg-purple-50/50');
                    roomsParent.classList.remove('text-slate-600');
                    toggleRoomsDropdown(true);
                } else {
                    roomsParent.classList.remove('text-brand-purple', 'font-semibold', 'bg-purple-50/50');
                    roomsParent.classList.add('text-slate-600');
                }
            }

            // Sync parent sliders dropdown state
            const slidersParent = document.getElementById('sliders-parent-btn');
            const slidersDropdown = document.getElementById('sliders-dropdown-menu');
            if (slidersParent && slidersDropdown) {
                if (element && slidersDropdown.contains(element)) {
                    slidersParent.classList.add('text-brand-purple', 'font-semibold', 'bg-purple-50/50');
                    slidersParent.classList.remove('text-slate-600');
                    toggleSlidersDropdown(true);
                } else {
                    slidersParent.classList.remove('text-brand-purple', 'font-semibold', 'bg-purple-50/50');
                    slidersParent.classList.add('text-slate-600');
                }
            }
        }
        window.setActiveAdminLink = setActiveAdminLink;

        function showAdminSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.page-section').forEach(el => el.classList.remove('active'));
            // Show target section
            const target = document.getElementById(sectionId);
            if(target) target.classList.add('active');
            
            // Scroll to top of content
            const contentArea = document.querySelector('main#admin-content') || document.querySelector('.flex-1.overflow-auto');
            if (contentArea) contentArea.scrollTop = 0;

            // Update title
            const titles = {
                'admin-overview': 'Analytics Overview',
                'manage-bookings': 'Booking Management',
                'manage-rooms': 'Room Inventory',
                'manage-prices': 'Room Price Management',
                'manage-users': 'User Management',
                'manage-reports': 'Hotel Reports & Analytics',
                'manage-sliders': 'Carousel & Sliders',
                'manage-amenities': 'Amenities Management',
                'manage-reviews': 'Reviews & Ratings',
            };
            const titleEl = document.getElementById('admin-page-title');
            if (titleEl) titleEl.innerText = titles[sectionId] || 'Admin Panel';

            // Sync Sidebar Active Link
            const sectionNavMap = {
                'admin-overview': 'nav-overview',
                'manage-bookings': 'nav-bookings',
                'manage-rooms': 'nav-rooms-inventory',
                'manage-prices': 'nav-rooms-prices',
                'manage-users': 'nav-users',
                'manage-reports': 'nav-reports',
                'manage-sliders': 'nav-sliders-manage',
                'manage-amenities': 'nav-amenities-manage',
                'manage-reviews': 'nav-reviews'
            };
            const targetNavId = sectionNavMap[sectionId];
            if (targetNavId) {
                const navLink = document.getElementById(targetNavId);
                if (navLink) setActiveAdminLink(navLink);
            }

            // Sync Mobile Bottom Nav
            const mobileMap = {
                'admin-overview': 'mob-nav-overview',
                'manage-bookings': 'mob-nav-bookings',
                'manage-rooms': 'mob-nav-rooms',
                'manage-prices': 'mob-nav-rooms',
                'manage-reports': 'mob-nav-reports'
            };
            document.querySelectorAll('.mobile-nav-item').forEach(btn => {
                btn.classList.remove('text-brand-purple');
                btn.classList.add('text-slate-400');
            });
            const activeMobileBtnId = mobileMap[sectionId];
            if (activeMobileBtnId) {
                const activeBtn = document.getElementById(activeMobileBtnId);
                if (activeBtn) {
                    activeBtn.classList.remove('text-slate-400');
                    activeBtn.classList.add('text-brand-purple');
                }
            }

            // Close sidebar on mobile ONLY IF it is currently open
            if (window.innerWidth < 768) {
                const sidebar = document.getElementById('admin-sidebar');
                if (sidebar && !sidebar.classList.contains('-translate-x-full')) {
                    toggleAdminSidebar();
                }
            }

            // Section Specific Data Loaders & Refreshers
            if (sectionId === 'admin-overview') {
                if (typeof loadDashboardAll === 'function') loadDashboardAll();
            } else if (sectionId === 'manage-bookings') {
                if (typeof loadBookings === 'function') loadBookings();
            } else if (sectionId === 'manage-rooms') {
                toggleRoomsDropdown(true);
                if (typeof loadRooms === 'function') loadRooms();
            } else if (sectionId === 'manage-prices') {
                toggleRoomsDropdown(true);
                if (typeof loadPriceRooms === 'function') loadPriceRooms();
            } else if (sectionId === 'manage-users') {
                if (typeof loadUsers === 'function') loadUsers();
            } else if (sectionId === 'manage-reports') {
                if (typeof generateHotelReportData === 'function') generateHotelReportData();
            } else if (sectionId === 'manage-sliders') {
                toggleSlidersDropdown(true);
                if (typeof loadSliders === 'function') loadSliders();
            } else if (sectionId === 'manage-amenities') {
                toggleSlidersDropdown(true);
                if (typeof loadAmenities === 'function') loadAmenities();
            } else if (sectionId === 'manage-reviews') {
                if (typeof loadReviews === 'function') loadReviews();
            }
        }
        window.showAdminSection = showAdminSection;

        // Load booking counts for sidebar badge and mobile bottom nav badge
        function loadBookingCounts() {
            fetch('../actions/bookings_action.php?action=count')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.counts.pending > 0) {
                        const badge = document.getElementById('booking-count-badge');
                        if (badge) {
                            badge.textContent = data.counts.pending;
                            badge.classList.remove('hidden');
                        }
                        const mobBadge = document.getElementById('booking-mobile-badge');
                        if (mobBadge) {
                            mobBadge.classList.remove('hidden');
                        }
                    }
                })
                .catch(error => console.error('Error loading booking counts:', error));
        }

        // Load counts on page load
        document.addEventListener('DOMContentLoaded', loadBookingCounts);
    </script>
</body>
</html>
