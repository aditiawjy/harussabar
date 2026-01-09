<div class="p-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Bulk Import Matches</h1>
        <p class="text-gray-600 mt-2">Import data pertandingan dari file CSV</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Upload CSV -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Upload CSV File</h2>
            
            <form id="uploadForm" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Pilih File CSV:
                    </label>
                    <input type="file" name="csvFile" accept=".csv" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Format: id, match_time, home_team, away_team, league, fh_home, fh_away, ft_home, ft_away</p>
                </div>
                
                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    Upload & Import
                </button>
            </form>
            
            <div id="uploadStatus" class="mt-4"></div>
        </div>

        <!-- Quick Import -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Quick Import Options</h2>
            
            <div class="space-y-3">
                <a href="ultra_fast_bulk.php" target="_blank" 
                   class="block w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-center">
                    🚀 Ultra Fast Bulk Import
                </a>
                
                <button onclick="checkDatabase()" class="w-full bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                    📊 Check Database Status
                </button>
                
                <button onclick="truncateTable()" class="w-full bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                    🗑️ Clear All Data
                </button>
            </div>
            
            <div id="quickStatus" class="mt-4"></div>
        </div>
    </div>

    <!-- Database Status -->
    <div class="bg-white rounded-lg shadow-md p-6 mt-6">
        <h2 class="text-xl font-semibold mb-4">Database Status</h2>
        <div id="dbStatus" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Will be loaded by JavaScript -->
        </div>
    </div>
</div>

<script>
    // Upload form handler
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const statusDiv = document.getElementById('uploadStatus');
        
        statusDiv.innerHTML = '<div class="text-blue-600">Mengupload...</div>';
        
        fetch('upload_csv.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                statusDiv.innerHTML = `
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        ✅ ${data.message}
                    </div>
                `;
                checkDatabase(); // Refresh status
            } else {
                statusDiv.innerHTML = `
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        ❌ ${data.error}
                    </div>
                `;
            }
        })
        .catch(error => {
            statusDiv.innerHTML = `
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    ❌ Error: ${error.message}
                </div>
            `;
        });
    });
    
    // Check database status
    function checkDatabase() {
        fetch('get_db_status.php')
            .then(response => response.json())
            .then(data => {
                const statusDiv = document.getElementById('dbStatus');
                statusDiv.innerHTML = `
                    <div class="bg-blue-50 p-4 rounded">
                        <h3 class="font-semibold text-blue-900">Total Matches</h3>
                        <p class="text-2xl font-bold text-blue-600">${data.totalMatches.toLocaleString()}</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded">
                        <h3 class="font-semibold text-green-900">Unique Leagues</h3>
                        <p class="text-2xl font-bold text-green-600">${data.totalLeagues}</p>
                    </div>
                    <div class="bg-purple-50 p-4 rounded">
                        <h3 class="font-semibold text-purple-900">Last Match</h3>
                        <p class="text-sm text-purple-600">${data.lastMatch || 'No data'}</p>
                    </div>
                `;
            });
    }
    
    // Truncate table
    function truncateTable() {
        if (confirm('⚠️ Apakah Anda yakin ingin menghapus SEMUA data pertandingan?')) {
            if (confirm('⚠️ Ini tidak bisa dibatalkan! Yakin?')) {
                fetch('truncate_matches.php')
                    .then(response => response.json())
                    .then(data => {
                        const statusDiv = document.getElementById('quickStatus');
                        if (data.success) {
                            statusDiv.innerHTML = `
                                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                                    ✅ ${data.message}
                                </div>
                            `;
                            checkDatabase();
                        } else {
                            statusDiv.innerHTML = `
                                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                                    ❌ ${data.error}
                                </div>
                            `;
                        }
                    });
            }
        }
    }
    
    // Load status on page load
    checkDatabase();
</script>
