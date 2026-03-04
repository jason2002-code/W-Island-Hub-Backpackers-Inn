
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
         <!-- ================= ROOMS PAGE================= -->
          

 

         <?php include "user/rooms.php"; ?>

       <!-- ================= CONTACT PAGE================= -->
         <?php include "user/contact.php"; ?>
       <!-- ================= ABOUT PAGE================= -->
         <?php include "user/about.php"; ?>

         <!-- ================= REGISTER AND LOGIN PAGE================= -->   
        <?php include "user/register.php"; ?>
        <?php include "user/login.php"; ?>

      

 
       </main>
     
        <!-- ================= FOOTER ================= -->
         
        <?php include 'includes/footer.php'; ?>

        <!-- JAVASCRIPT LOGIC -->
      
       <script src="assets/js/script.js"></script>
       <script src="assets/js/admin_dash.js"></script>
    </body>
</html>
