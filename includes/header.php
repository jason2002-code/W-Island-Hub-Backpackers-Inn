<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>W Island Hub Backpackers Inn</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- GSAP for Animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">


    <script>
         tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            blue: '#0ea5e9',
                            sand: '#fef3c7',
                            dark: '#0f172a',
                            green: '#22c55e',
                            accent: '#f59e0b',
                            purple: '#6C5DD3',
                            cyan: '#1db3e8' /* Added for the new login design */
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

    <style>
       @import url('assets/css/Gsap.css');
      /* @import url('assets/css/adcss.css');*/
    </style>
</head>
<body class="bg-slate-50 text-slate-800">

    <!-- Loading Screen -->
    <div id="loader">
        <div class="w-logo">W</div>
        <p class="mt-4 text-brand-blue font-bold tracking-widest text-sm uppercase">Island Hub</p>
    </div>
