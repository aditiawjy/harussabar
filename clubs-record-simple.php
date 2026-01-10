<?php
require_once 'koneksi.php';

// Get sorting parameters
$sort = $_GET['sort'] ?? 'team';
$order = $_GET['order'] ?? 'asc';

// Toggle order for next click
$nextOrder = $order == 'asc' ? 'desc' : 'asc';

// Build WHERE clause for time filter
$where = "";
$params = [];
$types = "";

if (!empty($_GET['time_from'])) {
    $where .= " WHERE TIME(match_time) >= ?";
    $params[] = $_GET['time_from'] . ':00';
    $types .= "s";
}

if (!empty($_GET['time_to'])) {
    if (empty($where)) {
        $where .= " WHERE TIME(match_time) <= ?";
    } else {
        $where .= " AND TIME(match_time) <= ?";
    }
    $params[] = $_GET['time_to'] . ':59';
    $types .= "s";
}

// Get today's date
$today = date('Y-m-d');

// Validate sort column
$allowedSort = ['team', 'under_05_today', 'max_under_05_alltime', 'max_under_date'];
if (!in_array($sort, $allowedSort)) {
    $sort = 'team';
}

// Query to get all teams with their under 0.5 stats
$teamsQuery = "
    SELECT 
        t.team,
        COALESCE(today.under_count, 0) as under_05_today,
        COALESCE(max_all.max_under, 0) as max_under_05_alltime,
        COALESCE(max_all.max_date, '-') as max_under_date
    FROM (
        SELECT DISTINCT home_team as team FROM matches
        UNION 
        SELECT DISTINCT away_team as team FROM matches
    ) t
    LEFT JOIN (
        SELECT 
            team,
            SUM(CASE WHEN DATE(match_time) = '$today' AND (ft_home + ft_away) < 0.5 THEN 1 ELSE 0 END) as under_count
        FROM (
            SELECT home_team as team, ft_home, ft_away, match_time FROM matches
            UNION ALL
            SELECT away_team as team, ft_home, ft_away, match_time FROM matches
        ) all_matches
        GROUP BY team
    ) today ON t.team = today.team
    LEFT JOIN (
        SELECT 
            team,
            MAX(daily_count) as max_under,
            MAX(CASE WHEN daily_count = max_daily THEN match_date END) as max_date
        FROM (
            SELECT 
                team,
                DATE(match_time) as match_date,
                SUM(CASE WHEN (ft_home + ft_away) < 0.5 THEN 1 ELSE 0 END) as daily_count,
                MAX(SUM(CASE WHEN (ft_home + ft_away) < 0.5 THEN 1 ELSE 0 END)) OVER (PARTITION BY team) as max_daily
            FROM (
                SELECT home_team as team, ft_home, ft_away, match_time FROM matches
                UNION ALL
                SELECT away_team as team, ft_home, ft_away, match_time FROM matches
            ) all_matches
            GROUP BY team, DATE(match_time)
        ) daily
        GROUP BY team
    ) max_all ON t.team = max_all.team
    ORDER BY $sort $order
";

$teamsResult = $conn->query($teamsQuery);

$teams = [];
while ($row = $teamsResult->fetch_assoc()) {
    $teams[] = $row;
}

$totalTeams = count($teams);
?>

<div class="p-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
        <div>
            <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight mb-2">Club Record</h1>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                    <?php echo number_format($totalTeams); ?> CLUBS
                </span>
                <p class="text-slate-500 text-sm font-medium">Daftar semua klub yang terdaftar</p>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            <a href="index.php?page=parser" class="inline-flex items-center px-5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl font-semibold text-sm hover:bg-slate-50 transition-all shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Data
            </a>
        </div>
    </div>

    <!-- Time Filter -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-8">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <input type="hidden" name="page" value="clubs">
            
            <div class="flex flex-col gap-2">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Dari Jam</label>
                <input type="text" 
                       id="time_from" 
                       name="time_from" 
                       value="<?php echo htmlspecialchars($_GET['time_from'] ?? '00:00'); ?>" 
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-all"
                       placeholder="00:00"
                       readonly>
            </div>
            
            <div class="flex flex-col gap-2">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Sampai Jam</label>
                <input type="text" 
                       id="time_to" 
                       name="time_to" 
                       value="<?php echo htmlspecialchars($_GET['time_to'] ?? '23:59'); ?>" 
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-all"
                       placeholder="23:59"
                       readonly>
            </div>
            
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-slate-900 text-white px-4 py-2.5 rounded-xl font-bold text-sm hover:bg-slate-800 transition-all shadow-lg shadow-slate-900/10 active:scale-95">
                    Filter
                </button>
                <a href="index.php?page=clubs" class="p-2.5 bg-slate-100 text-slate-500 rounded-xl hover:bg-slate-200 hover:text-slate-700 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </a>
            </div>
        </form>
    </div>

    <!-- Clubs Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em] w-12">No</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em]">
                            <a href="?page=clubs&sort=team&order=<?php echo $sort == 'team' && $order == 'asc' ? 'desc' : 'asc'; ?><?php echo !empty($_GET['time_from']) ? '&time_from='.$_GET['time_from'] : ''; ?><?php echo !empty($_GET['time_to']) ? '&time_to='.$_GET['time_to'] : ''; ?>" 
                               class="flex items-center gap-1 hover:text-blue-600 transition-colors">
                                Club Name
                                <?php if ($sort == 'team'): ?>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <?php if ($order == 'asc'): ?>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                        <?php else: ?>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        <?php endif; ?>
                                    </svg>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em] w-32">
                            <a href="?page=clubs&sort=under_05_today&order=<?php echo $sort == 'under_05_today' && $order == 'asc' ? 'desc' : 'asc'; ?><?php echo !empty($_GET['time_from']) ? '&time_from='.$_GET['time_from'] : ''; ?><?php echo !empty($_GET['time_to']) ? '&time_to='.$_GET['time_to'] : ''; ?>" 
                               class="flex items-center justify-center gap-1 hover:text-blue-600 transition-colors">
                                Under 0.5 Today
                                <?php if ($sort == 'under_05_today'): ?>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <?php if ($order == 'asc'): ?>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                        <?php else: ?>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        <?php endif; ?>
                                    </svg>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em] w-32">
                            <a href="?page=clubs&sort=max_under_05_alltime&order=<?php echo $sort == 'max_under_05_alltime' && $order == 'asc' ? 'desc' : 'asc'; ?><?php echo !empty($_GET['time_from']) ? '&time_from='.$_GET['time_from'] : ''; ?><?php echo !empty($_GET['time_to']) ? '&time_to='.$_GET['time_to'] : ''; ?>" 
                               class="flex items-center justify-center gap-1 hover:text-blue-600 transition-colors">
                                Max Under 0.5
                                <?php if ($sort == 'max_under_05_alltime'): ?>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <?php if ($order == 'asc'): ?>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                        <?php else: ?>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        <?php endif; ?>
                                    </svg>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em] w-36">
                            <a href="?page=clubs&sort=max_under_date&order=<?php echo $sort == 'max_under_date' && $order == 'asc' ? 'desc' : 'asc'; ?><?php echo !empty($_GET['time_from']) ? '&time_from='.$_GET['time_from'] : ''; ?><?php echo !empty($_GET['time_to']) ? '&time_to='.$_GET['time_to'] : ''; ?>" 
                               class="flex items-center justify-center gap-1 hover:text-blue-600 transition-colors">
                                Tanggal
                                <?php if ($sort == 'max_under_date'): ?>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <?php if ($order == 'asc'): ?>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                        <?php else: ?>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        <?php endif; ?>
                                    </svg>
                                <?php endif; ?>
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (!empty($teams)): ?>
                        <?php foreach ($teams as $index => $team): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm font-bold text-slate-600"><?php echo $index + 1; ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm font-medium text-slate-900"><?php echo htmlspecialchars($team['team']); ?></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center px-3 py-1 bg-green-100 text-green-700 rounded-lg text-sm font-bold">
                                        <?php echo $team['under_05_today']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center px-3 py-1 bg-purple-100 text-purple-700 rounded-lg text-sm font-bold">
                                        <?php echo $team['max_under_05_alltime']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if ($team['max_under_date'] != '-'): ?>
                                        <?php 
                                        $date = new DateTime($team['max_under_date']);
                                        $formattedDate = $date->format('d M Y');
                                        ?>
                                        <span class="inline-flex items-center justify-center px-3 py-1 bg-orange-100 text-orange-700 rounded-lg text-xs font-bold">
                                            <?php echo $formattedDate; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-400 text-sm">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <p class="text-slate-400 text-sm font-medium">Belum ada data klub</p>
                                    <a href="index.php?page=parser" class="text-xs font-bold text-blue-600 hover:text-blue-700">Input Data Pertandingan</a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Custom time picker
    $('#time_from, #time_to').on('click', function(e) {
        e.preventDefault();
        const $input = $(this);
        
        // Remove existing dropdown
        $('.time-dropdown').remove();
        
        // Create dropdown
        const $dropdown = $('<div class="time-dropdown" style="position: absolute; background: white; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); z-index: 9999; max-height: 200px; overflow-y: auto; font-size: 14px;"></div>');
        
        // Add time options (every hour)
        for (let hour = 0; hour <= 23; hour++) {
            const hourStr = hour.toString().padStart(2, '0');
            const $option = $(`<div style="padding: 8px 16px; cursor: pointer;" data-time="${hourStr}:00">${hourStr}:00</div>`);
            $dropdown.append($option);
        }
        
        // Position dropdown
        const offset = $input.offset();
        $dropdown.css({
            top: offset.top + $input.outerHeight(),
            left: offset.left,
            width: $input.outerWidth()
        }).appendTo('body');
        
        // Handle selection
        $dropdown.find('div').on('click', function() {
            $input.val($(this).data('time'));
            $dropdown.remove();
        });
        
        // Hover effects
        $dropdown.find('div').hover(
            function() { $(this).css('background', '#f8fafc'); },
            function() { $(this).css('background', 'white'); }
        );
        
        // Close on outside click
        setTimeout(() => {
            $(document).one('click', function() {
                $dropdown.remove();
            });
        }, 100);
    });
});
</script>
