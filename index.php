<?php include 'koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sabaraja - Management Pertandingan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-72 bg-slate-900 text-white flex flex-col shadow-2xl z-20">
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
            <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-8 sticky top-0 z-10">
                <div class="flex items-center gap-2">
                    <span class="text-slate-400 text-sm font-medium">Pages</span>
                    <span class="text-slate-300">/</span>
                    <span class="text-slate-900 text-sm font-semibold">
                        <?php 
                        $page = $_GET['page'] ?? 'parser';
                        echo $page == 'parser' ? 'Parsing Data' : 'Semua Pertandingan';
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
                    }
                    ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
