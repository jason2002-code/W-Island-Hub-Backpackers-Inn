# TODO - Replace External Image URLs with Local Images

## Tasks Completed
- [x] Replace external image URL in user/about.php
- [x] Replace external image URLs in assets/js/script.js (defaultRooms, marqueeImages, and heroSlides)
- [x] Add carousel_sliders table to schema.sql
- [x] Create actions/sliders_action.php (backend API)
- [x] Create admin/manage_sliders.php (admin UI)
- [x] Update admin/dashboard.php with slider management section

## Summary
1. **Image Replacement**: All external image URLs have been replaced with local images from `assets/image/background.jpg`
2. **Admin Slider Management**: Added new "Carousel/Sliders" section in the admin dashboard that allows:
   - Managing Hero Carousel images
   - Managing Marquee Gallery images
   - Adding, editing, deleting, and toggling slider visibility
   - Using local images from assets/image/ folder
