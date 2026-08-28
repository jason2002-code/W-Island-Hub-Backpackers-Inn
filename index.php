
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
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

        <!-- JAVASCRIPT LOGIC -->
      
       <script src="assets/js/script.js"></script>
       <script src="assets/js/homepage_reviews.js"></script>
       <script src="https://accounts.google.com/gsi/client" async defer></script>
       
     
    </body>
</html>
