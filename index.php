
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/db.php';

// Archived accounts are locked out even when they still hold a live session.
if (!empty($_SESSION['user_id'])) {
    $sessionGuardDb = getDB();

    if ($sessionGuardDb) {
        try {
            $guardStmt = $sessionGuardDb->prepare("SELECT is_archived FROM users WHERE id = ? LIMIT 1");
            $guardStmt->execute([$_SESSION['user_id']]);
            $archivedFlag = $guardStmt->fetchColumn();

            // No row (account deleted) or is_archived = 1 means the session is no longer valid.
            if ($archivedFlag === false || (int) $archivedFlag === 1) {
                $_SESSION = [];
                session_destroy();
            }
        } catch (PDOException $e) {
            // The public site must keep rendering even if this check cannot run.
        }
    }
}
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
