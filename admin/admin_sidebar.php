<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div id="admin-sidebar" class="w-64 bg-white text-gray-600 border-r border-gray-100 flex flex-col h-full shadow-lg md:shadow-none hidden md:flex">
    <div class="p-6 h-20 flex items-center border-b border-gray-50">
        <span class="text-2xl font-bold text-gray-800 flex items-center gap-2">
             <div class="w-8 h-8 rounded-lg bg-red-500 flex items-center justify-center text-white text-lg font-serif">W</div>
             Admin
        </span>
    </div>
    
    <nav class="mt-6 flex-1 px-4 space-y-2">
        <p class="px-4 text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Main Menu</p>
        
        <a href="dashboard.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-pie w-5"></i> Dashboard
        </a>
        <a href="manage_reservations.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl <?php echo $current_page == 'manage_reservations.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt w-5"></i> Bookings
        </a>
        <a href="manage_rooms.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl <?php echo $current_page == 'manage_rooms.php' ? 'active' : ''; ?>">
            <i class="fas fa-door-open w-5"></i> Rooms
        </a>
        
        <p class="px-4 text-xs font-bold text-gray-400 uppercase tracking-wider mt-6 mb-2">Management</p>
        
        <a href="manage_users.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl <?php echo $current_page == 'manage_users.php' ? 'active' : ''; ?>">
            <i class="fas fa-users w-5"></i> Users
        </a>
        <a href="reports.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>">
            <i class="fas fa-comment-dots w-5"></i> Feedback
        </a>
    </nav>

    <div class="p-4 border-t border-gray-100">
        <a href="../logout.php" class="flex items-center gap-3 px-4 py-3 text-red-500 hover:bg-red-50 w-full rounded-xl transition font-medium">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>