<?php include 'koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sabaraja - Management Pertandingan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900">
    <div class="flex h-screen overflow-hidden">
        <!-- Mobile Menu Button -->
        <button id="mobileMenuBtn" class="lg:hidden fixed top-4 left-4 z-50 p-3 bg-slate-900 text-white rounded-xl shadow-lg transition-all duration-300">
            <!-- Hamburger Icon -->
            <svg id="hamburgerIcon" class="w-6 h-6 transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            <!-- Close Icon -->
            <svg id="closeIcon" class="w-6 h-6 hidden transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <!-- Sidebar Overlay -->
        <div id="sidebarOverlay" class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-30 hidden"></div>

        <!-- Sidebar -->
        <aside id="sidebar" class="fixed lg:static inset-y-0 left-0 z-40 w-72 bg-slate-900 text-white flex flex-col shadow-2xl transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
            <div class="p-8">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/30">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight">SABARAJA</h1>
                        <p class="text-slate-400 text-xs font-medium uppercase tracking-widest">Dashboard</p>
                    </div>
                </div>
            </div>
            
            <nav class="flex-1 px-4 space-y-2 overflow-y-auto">
                <a href="index.php?page=parser" class="group flex items-center px-4 py-3.5 rounded-xl transition-all duration-200 <?php echo (!isset($_GET['page']) || $_GET['page'] == 'parser') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'; ?>">
                    <svg class="w-5 h-5 mr-3 <?php echo (!isset($_GET['page']) || $_GET['page'] == 'parser') ? 'text-white' : 'text-slate-500 group-hover:text-blue-400'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="font-medium">Parsing Data</span>
                </a>
                
                <a href="index.php?page=matches" class="group flex items-center px-4 py-3.5 rounded-xl transition-all duration-200 <?php echo (isset($_GET['page']) && $_GET['page'] == 'matches') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'; ?>">
                    <svg class="w-5 h-5 mr-3 <?php echo (isset($_GET['page']) && $_GET['page'] == 'matches') ? 'text-white' : 'text-slate-500 group-hover:text-blue-400'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span class="font-medium">Semua Pertandingan</span>
                </a>
                
                <a href="index.php?page=clubs" class="group flex items-center px-4 py-3.5 rounded-xl transition-all duration-200 <?php echo (isset($_GET['page']) && $_GET['page'] == 'clubs') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'; ?>">
                    <svg class="w-5 h-5 mr-3 <?php echo (isset($_GET['page']) && $_GET['page'] == 'clubs') ? 'text-white' : 'text-slate-500 group-hover:text-blue-400'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span class="font-medium">Club Record</span>
                </a>
            </nav>

            <div class="p-6 border-t border-slate-800">
                <div class="bg-slate-800/50 rounded-2xl p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center">
                            <span class="text-xs font-bold text-slate-300">AD</span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white leading-tight">Admin Sabaraja</p>
                            <p class="text-[10px] text-slate-500 font-medium uppercase">Management</p>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0 bg-slate-50">
            <!-- Top Navbar -->
            <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-4 lg:px-8 sticky top-0 z-10">
                <div class="flex items-center gap-2">
                    <span class="text-slate-400 text-sm font-medium hidden lg:block">Pages</span>
                    <span class="text-slate-300 hidden lg:block">/</span>
                    <span class="text-slate-900 text-sm font-semibold">
                        <?php 
                        $page = $_GET['page'] ?? 'parser';
                        if ($page == 'parser') {
                            echo 'Parsing Data';
                        } elseif ($page == 'matches') {
                            echo 'Semua Pertandingan';
                        } elseif ($page == 'clubs') {
                            echo 'Club Record';
                        }
                        ?>
                    </span>
                </div>
                
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                </div>
            </header>

            <div class="flex-1 overflow-auto">
                <div class="max-w-7xl mx-auto">
                    <?php
                    if ($page == 'parser') {
                        include 'parser-content.php';
                    } elseif ($page == 'matches') {
                        include 'matches-list.php';
                    } elseif ($page == 'clubs') {
                        include 'clubs-record-simple.php';
                    }
                    ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Mobile Sidebar Script -->
<script>
$(document).ready(function() {
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const hamburgerIcon = document.getElementById('hamburgerIcon');
    const closeIcon = document.getElementById('closeIcon');
    
    // Open sidebar
    mobileMenuBtn.addEventListener('click', function() {
        const isOpen = !sidebar.classList.contains('-translate-x-full');
        
        if (!isOpen) {
            // Open sidebar
            sidebar.classList.remove('-translate-x-full');
            sidebarOverlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Change hamburger to X
            hamburgerIcon.classList.add('hidden');
            closeIcon.classList.remove('hidden');
            mobileMenuBtn.classList.add('bg-red-600');
            mobileMenuBtn.classList.remove('bg-slate-900');
        } else {
            // Close sidebar
            closeSidebar();
        }
    });
    
    // Close sidebar
    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        sidebarOverlay.classList.add('hidden');
        document.body.style.overflow = 'auto';
        
        // Change X back to hamburger
        hamburgerIcon.classList.remove('hidden');
        closeIcon.classList.add('hidden');
        mobileMenuBtn.classList.remove('bg-red-600');
        mobileMenuBtn.classList.add('bg-slate-900');
    }
    
    sidebarOverlay.addEventListener('click', closeSidebar);
    
    // Close sidebar when clicking a link on mobile
    const sidebarLinks = sidebar.querySelectorAll('a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 1024) {
                closeSidebar();
            }
        });
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024) {
            sidebar.classList.remove('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
            document.body.style.overflow = 'auto';
            
            // Reset button state on desktop
            hamburgerIcon.classList.remove('hidden');
            closeIcon.classList.add('hidden');
            mobileMenuBtn.classList.remove('bg-red-600');
            mobileMenuBtn.classList.add('bg-slate-900');
        } else {
            sidebar.classList.add('-translate-x-full');
        }
    });
});
</script>

</body>
</html>
