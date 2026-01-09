<?php include 'koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sabaraja - Management Pertandingan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-gray-800 text-white">
            <div class="p-4">
                <h1 class="text-2xl font-bold">SABARAJA</h1>
                <p class="text-gray-400 text-sm">Management System</p>
            </div>
            <nav class="mt-8">
                <a href="index.php?page=parser" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white <?php echo $_GET['page'] == 'parser' ? 'bg-gray-700 text-white' : ''; ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Parsing Pertandingan
                </a>
                <a href="index.php?page=matches" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white <?php echo $_GET['page'] == 'matches' ? 'bg-gray-700 text-white' : ''; ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Semua Matches
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <?php
            $page = $_GET['page'] ?? 'parser';
            
            if ($page == 'parser') {
                include 'parser-content.php';
            } elseif ($page == 'matches') {
                include 'matches-list.php';
            }
            ?>
        </div>
    </div>
</body>
</html>
