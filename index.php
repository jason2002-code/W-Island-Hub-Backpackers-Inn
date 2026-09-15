
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/db.php';
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

    <!-- MAIN CONTENT CONTAINER -->
    <main class="pt-20 min-h-screen">

       <?php include "user/dashboard.php"; ?>

   
       
       
      

        <!-- ================= BOOKING SUMMARY AND ROOMS PAGE================= -->
         <?php include "user/book_room.php"; ?>
      

       <!-- ================= CONTACT PAGE================= -->
         <?php include "user/contact.php"; ?>
       <!-- ================= ABOUT PAGE================= -->
         <?php include "user/about.php"; ?>

         <!-- ================= REGISTER AND LOGIN PAGE================= -->   
        <?php include "user/register.php"; ?>
        <?php include "user/login.php"; ?>
        <?php include "user/rules.php"; ?>

       </main>
     
        <!-- ================= FOOTER ================= -->
         
        <?php include 'includes/footer.php'; ?>

        <!-- JAVASCRIPT MODULAR LOGIC -->
        <script src="assets/js/modules/state.js?v=<?= time() ?>"></script>
        <script src="assets/js/modules/ui.js?v=<?= time() ?>"></script>
        <script src="assets/js/modules/router.js?v=<?= time() ?>"></script>
        <script src="assets/js/modules/carousel.js?v=<?= time() ?>"></script>
        <script src="assets/js/modules/auth.js?v=<?= time() ?>"></script>
        <script src="assets/js/modules/rooms.js?v=<?= time() ?>"></script>
        <script src="assets/js/modules/booking.js?v=<?= time() ?>"></script>
        <script src="assets/js/modules/reviews.js?v=<?= time() ?>"></script>
        <script src="assets/js/modules/invoice.js?v=<?= time() ?>"></script>
        <script src="assets/js/homepage_reviews.js?v=<?= time() ?>"></script>
        <script src="assets/js/script.js?v=<?= time() ?>"></script>
        <script src="https://accounts.google.com/gsi/client" async defer></script>
       
     
    </body>
</html>
