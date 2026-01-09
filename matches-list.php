<?php
require_once 'koneksi.php';

// Pagination setup
$p = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$perPage = 15;
$offset = ($p - 1) * $perPage;

// Base query for counts and filtering
$where = "WHERE 1=1";
$params = [];
$types = "";

if (!empty($_GET['league'])) {
    $where .= " AND league = ?";
    $params[] = $_GET['league'];
    $types .= "s";
}

if (!empty($_GET['search'])) {
    $where .= " AND (home_team LIKE ? OR away_team LIKE ?)";
    $searchTerm = "%" . $_GET['search'] . "%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

if (!empty($_GET['date_from'])) {
    $where .= " AND DATE(match_time) >= ?";
    $params[] = $_GET['date_from'];
    $types .= "s";
}

if (!empty($_GET['date_to'])) {
    $where .= " AND DATE(match_time) <= ?";
    $params[] = $_GET['date_to'];
    $types .= "s";
}

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total FROM matches $where";
if (!empty($params)) {
    $stmt = $conn->prepare($countQuery);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];
} else {
    $total = $conn->query($countQuery)->fetch_assoc()['total'];
}
$totalPages = ceil($total / $perPage);

// Get filtered matches
$sql = "SELECT * FROM matches $where ORDER BY match_time DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types . "ii", ...array_merge($params, [$perPage, $offset]));
$stmt->execute();
$result = $stmt->get_result();

// Get unique leagues for filter
$leaguesResult = $conn->query("SELECT DISTINCT league FROM matches WHERE league IS NOT NULL ORDER BY league");
$leagues = [];
while ($row = $leaguesResult->fetch_assoc()) {
    $leagues[] = $row['league'];
}
?>

<div class="p-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
        <div>
            <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight mb-2">Semua Pertandingan</h1>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                    <?php echo number_format($total); ?> TOTAL
                </span>
                <p class="text-slate-500 text-sm font-medium">Monitoring data pertandingan secara real-time</p>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            <a href="index.php?page=parser" class="inline-flex items-center px-5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl font-semibold text-sm hover:bg-slate-50 transition-all shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Data
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-8">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
            <input type="hidden" name="page" value="matches">
            
            <div class="flex flex-col gap-2">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Cari Tim</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" 
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-all"
                           placeholder="Nama tim...">
                </div>
            </div>
            
            <div class="flex flex-col gap-2">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Filter Liga</label>
                <select name="league" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm appearance-none transition-all">
                    <option value="">Semua Liga</option>
                    <?php foreach ($leagues as $league): ?>
                        <option value="<?php echo htmlspecialchars($league); ?>" <?php echo ($_GET['league'] ?? '') == $league ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($league); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="flex flex-col gap-2">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Mulai Tanggal</label>
                <input type="date" name="date_from" value="<?php echo htmlspecialchars($_GET['date_from'] ?? ''); ?>" 
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-all">
            </div>
            
            <div class="flex flex-col gap-2">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Sampai Tanggal</label>
                <input type="date" name="date_to" value="<?php echo htmlspecialchars($_GET['date_to'] ?? ''); ?>" 
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-all">
            </div>
            
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-slate-900 text-white px-4 py-2.5 rounded-xl font-bold text-sm hover:bg-slate-800 transition-all shadow-lg shadow-slate-900/10 active:scale-95">
                    Terapkan
                </button>
                <a href="index.php?page=matches" class="p-2.5 bg-slate-100 text-slate-500 rounded-xl hover:bg-slate-200 hover:text-slate-700 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </a>
            </div>
        </form>
    </div>

    <!-- Matches Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-8 py-5 text-left text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em]">Waktu & Jadwal</th>
                        <th class="px-8 py-5 text-center text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em]">Pertandingan</th>
                        <th class="px-8 py-5 text-center text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em]">Hasil Akhir</th>
                        <th class="px-8 py-5 text-left text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em]">Kompetisi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($match = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-slate-50 transition-all duration-200 group">
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 flex flex-col items-center justify-center text-slate-500 group-hover:bg-white group-hover:shadow-sm transition-all border border-transparent group-hover:border-slate-100">
                                            <span class="text-[10px] font-bold uppercase leading-none mb-0.5"><?php echo (new DateTime($match['match_time']))->format('M'); ?></span>
                                            <span class="text-sm font-black text-slate-900 leading-none"><?php echo (new DateTime($match['match_time']))->format('d'); ?></span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1"><?php echo (new DateTime($match['match_time']))->format('Y'); ?></span>
                                            <span class="text-xs font-bold text-slate-700 leading-none"><?php echo (new DateTime($match['match_time']))->format('H:i'); ?> WIB</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center justify-center gap-4">
                                        <div class="flex-1 text-right">
                                            <span class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition-colors">
                                                <?php echo htmlspecialchars($match['home_team']); ?>
                                            </span>
                                        </div>
                                        <div class="w-8 h-8 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center">
                                            <span class="text-[10px] font-bold text-slate-400">VS</span>
                                        </div>
                                        <div class="flex-1 text-left">
                                            <span class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition-colors">
                                                <?php echo htmlspecialchars($match['away_team']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col items-center justify-center gap-1.5">
                                        <div class="flex items-center gap-2 bg-slate-900 px-4 py-1.5 rounded-xl shadow-sm">
                                            <span class="text-sm font-black text-white"><?php echo $match['ft_home'] ?? '0'; ?></span>
                                            <span class="text-slate-500 font-bold">-</span>
                                            <span class="text-sm font-black text-white"><?php echo $match['ft_away'] ?? '0'; ?></span>
                                        </div>
                                        <?php if ($match['fh_home'] !== null): ?>
                                            <div class="flex items-center gap-1">
                                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">Half Time</span>
                                                <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded">
                                                    <?php echo $match['fh_home']; ?>-<?php echo $match['fh_away']; ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="max-w-[220px]">
                                        <span class="inline-block text-[10px] font-bold text-blue-600 bg-blue-50/50 px-3 py-1.5 rounded-lg border border-blue-100/50 uppercase tracking-tight truncate w-full text-center">
                                            <?php echo htmlspecialchars($match['league']); ?>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-8 py-24 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center border border-slate-100">
                                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                    </div>
                                    <p class="text-slate-400 font-semibold tracking-tight">Tidak ada pertandingan ditemukan</p>
                                    <a href="index.php?page=matches" class="text-sm font-bold text-blue-600 hover:text-blue-700">Reset Filter</a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="bg-slate-50/50 px-8 py-6 flex items-center justify-between border-t border-slate-100">
                <div class="text-xs font-bold text-slate-500 uppercase tracking-widest">
                    Halaman <span class="text-slate-900"><?php echo $p; ?></span> dari <span class="text-slate-900"><?php echo $totalPages; ?></span>
                </div>
                
                <div class="flex items-center gap-1">
                    <?php 
                    $queryString = '';
                    foreach ($_GET as $key => $val) {
                        if ($key != 'p') $queryString .= '&' . urlencode($key) . '=' . urlencode($val);
                    }
                    ?>
                    
                    <?php if ($p > 1): ?>
                        <a href="?p=<?php echo $p - 1; ?><?php echo $queryString; ?>" 
                           class="p-2 text-slate-400 hover:text-slate-900 hover:bg-slate-200/50 rounded-lg transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                    <?php endif; ?>
                    
                    <div class="flex items-center px-2 gap-1">
                        <?php 
                        $start = max(1, $p - 1);
                        $end = min($totalPages, $p + 1);
                        if ($start > 1) echo '<span class="text-slate-300 px-1">...</span>';
                        for ($i = $start; $i <= $end; $i++): 
                        ?>
                            <a href="?p=<?php echo $i; ?><?php echo $queryString; ?>" 
                               class="min-w-[32px] h-8 flex items-center justify-center rounded-lg text-xs font-bold transition-all <?php echo $i == $p ? 'bg-slate-900 text-white shadow-lg shadow-slate-900/10' : 'text-slate-500 hover:bg-slate-200/50 hover:text-slate-900'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        <?php if ($end < $totalPages) echo '<span class="text-slate-300 px-1">...</span>'; ?>
                    </div>
                    
                    <?php if ($p < $totalPages): ?>
                        <a href="?p=<?php echo $p + 1; ?><?php echo $queryString; ?>" 
                           class="p-2 text-slate-400 hover:text-slate-900 hover:bg-slate-200/50 rounded-lg transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
