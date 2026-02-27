# Task: Separate admin functions into separate PHP action files

## Plan:
- [ ] Create actions/bookings_action.php - PHP functions for booking operations
- [ ] Create actions/rooms_action.php - PHP functions for room operations  
- [ ] Create actions/reports_action.php - PHP functions for reports/feedback operations
- [ ] Update admin/dashboard.php to include all admin sections
- [ ] Update admin/manage_bookings.php with booking management
- [ ] Update admin/manage_rooms.php with room management
- [ ] Update admin/reports.php with reports management
- [ ] Update JavaScript to call new PHP endpoints

## Files to Create:
1. actions/bookings_action.php - getBookings, updateBookingStatus, deleteBooking
2. actions/rooms_action.php - getRooms, addRoom, updateRoom, deleteRoom
3. actions/reports_action.php - getReports, deleteReport

## Files to Update:
1. admin/dashboard.php - Include all admin section files
2. admin/manage_bookings.php - Booking management UI
3. admin/manage_rooms.php - Room management UI
4. admin/reports.php - Reports management UI

## Notes:
- Follow the existing pattern from actions/users_action.php
- Use database queries with PDO
- Return JSON responses for AJAX calls
