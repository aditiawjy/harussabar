<?php
require_once 'koneksi.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get all unique teams with their statistics
$teamsQuery = "
    SELECT 
        team,
        COUNT(*) as total_matches,
        SUM(CASE WHEN ft_home > ft_away THEN 1 ELSE 0 END) as wins,
        SUM(CASE WHEN ft_home = ft_away THEN 1 ELSE 0 END) as draws,
        SUM(CASE WHEN ft_home < ft_away THEN 1 ELSE 0 END) as losses,
        SUM(CASE WHEN ft_home IS NOT NULL THEN ft_home ELSE 0 END) as goals_for,
        SUM(CASE WHEN ft_away IS NOT NULL THEN ft_away ELSE 0 END) as goals_against,
        (SUM(CASE WHEN ft_home > ft_away THEN 3 WHEN ft_home = ft_away THEN 1 ELSE 0 END)) as points
    FROM (
        SELECT home_team as team, ft_home, ft_away FROM matches
        UNION ALL
        SELECT away_team as team, ft_away as ft_home, ft_home as ft_away FROM matches
    ) as all_matches
    GROUP BY team
    ORDER BY points DESC, wins DESC, (goals_for - goals_against) DESC
";

// Check if query executes successfully
if (!$teamsResult = $conn->query($teamsQuery)) {
    die("Query error: " . $conn->error);
}

$teams = [];
while ($row = $teamsResult->fetch_assoc()) {
    $row['goal_difference'] = $row['goals_for'] - $row['goals_against'];
    $teams[] = $row;
}

// Get total teams
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

    <!-- Clubs Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em] w-12">No</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em]">Club Name</th>
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em] w-20">Matches</th>
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em] w-16">Won</th>
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em] w-16">Draw</th>
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em] w-16">Lost</th>
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em] w-16">GF</th>
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em] w-16">GA</th>
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em] w-16">GD</th>
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em] w-16">Points</th>
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
                                    <span class="text-sm text-slate-700"><?php echo $team['total_matches']; ?></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm font-medium text-green-600"><?php echo $team['wins']; ?></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm font-medium text-yellow-600"><?php echo $team['draws']; ?></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm font-medium text-red-600"><?php echo $team['losses']; ?></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm text-slate-700"><?php echo $team['goals_for']; ?></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm text-slate-700"><?php echo $team['goals_against']; ?></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm font-bold <?php echo $team['goal_difference'] > 0 ? 'text-green-600' : ($team['goal_difference'] < 0 ? 'text-red-600' : 'text-slate-600'); ?>">
                                        <?php echo $team['goal_difference'] > 0 ? '+' : ''; ?><?php echo $team['goal_difference']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center px-2 py-1 bg-slate-900 text-white rounded text-xs font-bold">
                                        <?php echo $team['points']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center border border-slate-100">
                                        <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    </div>
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
