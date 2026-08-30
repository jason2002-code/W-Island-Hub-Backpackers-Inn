<?php
/**
 * Admin Manage Reservations Page
 */
session_start();

// Authentication guard
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$adminName = $_SESSION['admin_name'] ?? 'Admin User';
$adminInitial = strtoupper(substr($adminName, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Management - W Island Hub Admin</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
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
</head>
<body class="bg-gray-50 text-slate-800 font-sans">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <?php include 'admin_sidebar.php'; ?>

        <!-- Main Content Area -->
        <div class="flex-1 overflow-auto w-full">
            <!-- Top Bar -->
            <header class="bg-white/80 backdrop-blur sticky top-0 z-10 px-8 py-4 flex justify-between items-center border-b border-gray-100">
                <div class="flex items-center gap-4">
                    <h2 class="text-xl font-bold text-brand-dark">Booking Management & Payment Engine</h2>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-brand-dark text-white flex items-center justify-center font-bold">
                        <?php echo htmlspecialchars($adminInitial); ?>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-800"><?php echo htmlspecialchars($adminName); ?></p>
                        <p class="text-xs text-gray-500">Super Admin</p>
                    </div>
                </div>
            </header>

            <div class="p-6">
                <?php include 'manage_bookings.php'; ?>
            </div>
        </div>
    </div>
</body>
</html>

