<?php
require_once 'koneksi.php';

// Pagination
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Get total matches
$totalResult = $conn->query("SELECT COUNT(*) as total FROM matches");
$total = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($total / $perPage);

// Get matches with pagination
$matches = $conn->query("SELECT * FROM matches ORDER BY match_time DESC LIMIT $perPage OFFSET $offset");

// Get unique leagues
$leaguesResult = $conn->query("SELECT DISTINCT league FROM matches WHERE league IS NOT NULL ORDER BY league");
$leagues = [];
while ($row = $leaguesResult->fetch_assoc()) {
    $leagues[] = $row['league'];
}

// Filter
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

// Order by match_time DESC (newest first)
$orderClause = "ORDER BY match_time DESC";

// Re-run query with filters
if (!empty($params)) {
    $stmt = $conn->prepare("SELECT * FROM matches $where $orderClause LIMIT $perPage OFFSET $offset");
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $matches;
}
?>

<div class="p-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Semua Pertandingan</h1>
        <p class="text-gray-600 mt-2">Total: <?php echo number_format($total); ?> pertandingan</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4">
            <input type="hidden" name="page" value="matches">
            
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari Tim:</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Nama tim...">
            </div>
            
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Liga:</label>
                <select name="league" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Liga</option>
                    <?php foreach ($leagues as $league): ?>
                        <option value="<?php echo htmlspecialchars($league); ?>" <?php echo ($_GET['league'] ?? '') == $league ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($league); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal:</label>
                <input type="date" name="date_from" value="<?php echo htmlspecialchars($_GET['date_from'] ?? ''); ?>" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal:</label>
                <input type="date" name="date_to" value="<?php echo htmlspecialchars($_GET['date_to'] ?? ''); ?>" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    Filter
                </button>
                <a href="index.php?page=matches" class="ml-2 bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Matches Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Home</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Skor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Away</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Liga</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($match = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php 
                                    $date = new DateTime($match['match_time']);
                                    echo $date->format('d/m/Y H:i');
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($match['home_team']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm">
                                        <span class="font-bold text-blue-600">
                                            <?php echo $match['ft_home'] !== null ? $match['ft_home'] : '-'; ?>
                                        </span>
                                        <span class="mx-1">-</span>
                                        <span class="font-bold text-blue-600">
                                            <?php echo $match['ft_away'] !== null ? $match['ft_away'] : '-'; ?>
                                        </span>
                                    </div>
                                    <?php if ($match['fh_home'] !== null): ?>
                                        <div class="text-xs text-gray-500">
                                            (<?php echo $match['fh_home']; ?>-<?php echo $match['fh_away']; ?>)
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($match['away_team']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($match['league']); ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                Tidak ada data pertandingan
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="bg-gray-50 px-6 py-3 flex items-center justify-between border-t border-gray-200">
                <div class="text-sm text-gray-700">
                    Menampilkan <?php echo $offset + 1; ?> sampai <?php echo min($offset + $perPage, $total); ?> dari <?php echo number_format($total); ?> pertandingan
                </div>
                <div class="flex space-x-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=matches&p=<?php echo $page - 1; ?><?php echo !empty($_GET['league']) ? '&league=' . urlencode($_GET['league']) : ''; ?><?php echo !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo !empty($_GET['date_from']) ? '&date_from=' . urlencode($_GET['date_from']) : ''; ?><?php echo !empty($_GET['date_to']) ? '&date_to=' . urlencode($_GET['date_to']) : ''; ?>" 
                           class="px-3 py-1 bg-white border border-gray-300 rounded-md text-sm hover:bg-gray-50">
                            Previous
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="?page=matches&p=<?php echo $i; ?><?php echo !empty($_GET['league']) ? '&league=' . urlencode($_GET['league']) : ''; ?><?php echo !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo !empty($_GET['date_from']) ? '&date_from=' . urlencode($_GET['date_from']) : ''; ?><?php echo !empty($_GET['date_to']) ? '&date_to=' . urlencode($_GET['date_to']) : ''; ?>" 
                           class="px-3 py-1 <?php echo $i == $page ? 'bg-blue-600 text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'; ?> rounded-md text-sm">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=matches&p=<?php echo $page + 1; ?><?php echo !empty($_GET['league']) ? '&league=' . urlencode($_GET['league']) : ''; ?><?php echo !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo !empty($_GET['date_from']) ? '&date_from=' . urlencode($_GET['date_from']) : ''; ?><?php echo !empty($_GET['date_to']) ? '&date_to=' . urlencode($_GET['date_to']) : ''; ?>" 
                           class="px-3 py-1 bg-white border border-gray-300 rounded-md text-sm hover:bg-gray-50">
                            Next
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
