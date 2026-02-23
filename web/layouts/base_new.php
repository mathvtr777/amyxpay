<?php
require_once __DIR__ . '/../app/Services/PlanService.php';
$planService = new \App\Services\PlanService($conn, $_SESSION['user_id'] ?? 0);
?>
<!DOCTYPE html>
<html class="dark" lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Zyro V2 - Premium Smart Checkout Platform</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet" />
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#a855f7", // Vibrant Purple
                        "background-light": "#F9FAFB",
                        "background-dark": "#0F0F0F",
                        "surface-dark": "#1A1A1A",
                        "border-dark": "#2E2E2E"
                    },
                    fontFamily: {
                        display: ["Inter", "sans-serif"],
                    },
                    borderRadius: {
                        DEFAULT: "12px",
                    },
                },
            },
        };
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass {
            background: rgba(26, 26, 26, 0.6);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .purple-glow {
            box-shadow: 0 0 20px rgba(168, 85, 247, 0.15);
        }
        .chart-gradient {
            background: linear-gradient(180deg, rgba(168, 85, 247, 0.1) 0%, rgba(168, 85, 247, 0) 100%);
        }
    </style>
</head>

<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 min-h-screen flex">
    
    <!-- SIDEBAR -->
    <?php include __DIR__ . '/components/sidebar_new.php'; ?>

    <main class="ml-64 flex-1 flex flex-col">
        <!-- HEADER -->
        <?php include __DIR__ . '/components/header_new.php'; ?>

        <!-- MAIN CONTENT -->
        <div class="p-8 space-y-8">
            <?php echo $content; ?>
        </div>
    </main>

    <div class="fixed inset-0 bg-black/60 z-40 hidden md:hidden" id="mobile-overlay"></div>

    <!-- SCRIPTS -->
    <?php echo $scripts ?? ''; ?>

</body>

</html>
