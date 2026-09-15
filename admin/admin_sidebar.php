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
        <?php $is_rooms_open = in_array($current_page, ['manage_rooms.php', 'manage_prices.php']); ?>
        <!-- Rooms Dropdown -->
        <div class="space-y-1">
            <button type="button" onclick="toggleSidebarRoomsDropdown()" class="w-full flex items-center justify-between px-4 py-3 rounded-xl hover:bg-blue-50 hover:text-brand-purple text-gray-600 transition group cursor-pointer font-medium <?php echo $is_rooms_open ? 'text-brand-purple bg-blue-50/50' : ''; ?>">
                <div class="flex items-center gap-3">
                    <i class="fas fa-door-open w-5"></i>
                    <span>Rooms</span>
                </div>
                <i id="sidebar-rooms-chevron" class="fas fa-chevron-down text-xs transition-transform duration-200 <?php echo $is_rooms_open ? 'rotate-180 text-brand-purple' : 'text-gray-400 group-hover:text-brand-purple'; ?>"></i>
            </button>
            <div id="sidebar-rooms-dropdown" class="<?php echo $is_rooms_open ? '' : 'hidden'; ?> pl-4 pr-1 space-y-1 border-l-2 border-gray-100 ml-6 my-1">
                <a href="manage_rooms.php" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-sm <?php echo $current_page == 'manage_rooms.php' ? 'active' : ''; ?> hover:bg-blue-50 hover:text-brand-purple transition">
                    <i class="fas fa-bed w-4 text-xs"></i> Manage Rooms
                </a>
                <a href="manage_prices.php" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-sm <?php echo $current_page == 'manage_prices.php' ? 'active' : ''; ?> hover:bg-blue-50 hover:text-brand-purple transition">
                    <i class="fas fa-tags w-4 text-xs"></i> Room Prices
                </a>
            </div>
        </div>
        
        <p class="px-4 text-xs font-bold text-gray-400 uppercase tracking-wider mt-6 mb-2">Management</p>
        
        <a href="manage_users.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl <?php echo $current_page == 'manage_users.php' ? 'active' : ''; ?>">
            <i class="fas fa-users w-5"></i> Users
        </a>
        <a href="reports.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>">
            <i class="fas fa-file-invoice-dollar w-5"></i> Reports & Analytics
        </a>

        <?php $is_sliders_open = in_array($current_page, ['manage_sliders.php', 'manage_amenities.php']); ?>
        <!-- Carousel & Amenities Dropdown -->
        <div class="space-y-1">
            <button type="button" onclick="toggleSidebarSlidersDropdown()" class="w-full flex items-center justify-between px-4 py-3 rounded-xl hover:bg-blue-50 hover:text-brand-purple text-gray-600 transition group cursor-pointer font-medium <?php echo $is_sliders_open ? 'text-brand-purple bg-blue-50/50' : ''; ?>">
                <div class="flex items-center gap-3">
                    <i class="fas fa-images w-5"></i>
                    <span>Carousel/Sliders</span>
                </div>
                <i id="sidebar-sliders-chevron" class="fas fa-chevron-down text-xs transition-transform duration-200 <?php echo $is_sliders_open ? 'rotate-180 text-brand-purple' : 'text-gray-400 group-hover:text-brand-purple'; ?>"></i>
            </button>
            <div id="sidebar-sliders-dropdown" class="<?php echo $is_sliders_open ? '' : 'hidden'; ?> pl-4 pr-1 space-y-1 border-l-2 border-gray-100 ml-6 my-1">
                <a href="dashboard.php" onclick="sessionStorage.setItem('admin_target_section', 'manage-sliders')" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-sm <?php echo $current_page == 'manage_sliders.php' ? 'active' : ''; ?> hover:bg-blue-50 hover:text-brand-purple transition">
                    <i class="fas fa-photo-video w-4 text-xs"></i> Manage Sliders
                </a>
                <a href="dashboard.php" onclick="sessionStorage.setItem('admin_target_section', 'manage-amenities')" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-sm hover:bg-blue-50 hover:text-brand-purple transition">
                    <i class="fas fa-concierge-bell w-4 text-xs"></i> Amenities
                </a>
            </div>
        </div>
    </nav>

    <div class="p-4 border-t border-gray-100">
        <a href="../logout.php" class="flex items-center gap-3 px-4 py-3 text-red-500 hover:bg-red-50 w-full rounded-xl transition font-medium">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>
<script>
function toggleSidebarRoomsDropdown() {
    const menu = document.getElementById('sidebar-rooms-dropdown');
    const chevron = document.getElementById('sidebar-rooms-chevron');
    if (menu) {
        menu.classList.toggle('hidden');
        if (chevron) {
            chevron.classList.toggle('rotate-180');
        }
    }
}
function toggleSidebarSlidersDropdown() {
    const menu = document.getElementById('sidebar-sliders-dropdown');
    const chevron = document.getElementById('sidebar-sliders-chevron');
    if (menu) {
        menu.classList.toggle('hidden');
        if (chevron) {
            chevron.classList.toggle('rotate-180');
        }
    }
}
</script>