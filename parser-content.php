<div class="p-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Parser Data Pertandingan</h1>
    
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <label for="inputText" class="block text-sm font-medium text-gray-700 mb-2">
            Masukkan Data Pertandingan:
        </label>
        <textarea 
            id="inputText" 
            class="w-full h-48 p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            placeholder="Paste data pertandingan di sini..."></textarea>
        
        <div class="mt-4 flex items-center space-x-4">
            <div class="flex-1">
                <label for="leagueSelect" class="block text-sm font-medium text-gray-700 mb-1">
                    Liga/Kompetisi:
                </label>
                <div class="flex space-x-2">
                    <select 
                        id="leagueSelect" 
                        class="flex-1 p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Pilih Liga --</option>
                        <!-- Options will be loaded from database -->
                    </select>
                    <input 
                        type="text" 
                        id="newLeagueInput" 
                        class="hidden flex-1 p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Masukkan nama liga baru">
                </div>
                <div id="leagueStatus" class="mt-1 text-xs text-gray-500"></div>
            </div>
        </div>
        
        <button 
            onclick="parseData()" 
            class="mt-4 bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
            Parse Data
        </button>
        <button 
            id="saveBtn"
            onclick="saveToDatabase()" 
            class="mt-4 ml-2 bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 transition-colors hidden">
            Simpan ke Database
        </button>
        <div id="saveStatus" class="mt-4"></div>
    </div>

    <div id="result" class="hidden">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Hasil Parsing:</h2>
            <div id="parsedContent" class="space-y-3"></div>
        </div>
    </div>

    <div id="error" class="hidden">
        <div class="bg-red-50 border border-red-200 rounded-md p-4">
            <p class="text-red-700"></p>
        </div>
    </div>
</div>

<script>
// Load leagues from database
async function loadLeagues() {
    try {
        const response = await fetch('get_leagues.php');
        const data = await response.json();
        
        if (data.success) {
            const leagueSelect = document.getElementById('leagueSelect');
            const leagueStatus = document.getElementById('leagueStatus');
            
            // Clear existing options (except first)
            leagueSelect.innerHTML = '<option value="">-- Pilih Liga --</option>';
            
            if (data.leagues.length > 0) {
                // Add leagues from database
                data.leagues.forEach(league => {
                    const option = document.createElement('option');
                    option.value = league;
                    option.textContent = league;
                    leagueSelect.appendChild(option);
                });
                
                // Add "Tambah Baru" option
                const otherOption = document.createElement('option');
                otherOption.value = 'other';
                otherOption.textContent = '-- Tambah Baru --';
                leagueSelect.appendChild(otherOption);
                
                leagueStatus.textContent = `${data.leagues.length} liga dimuat dari database`;
            } else {
                // Default options if no data
                const defaultLeagues = [
                    'SABA CLUB FRIENDLY Virtual PES 21 - 15 Mins Play',
                    'SABA INTERNATIONAL FRIENDLY Virtual PES 21 - 20 Mins Play',
                    'SABA LEAGUE Virtual PES 21 - 15 Mins Play',
                    'SABA CUP Virtual PES 21 - 15 Mins Play'
                ];
                
                defaultLeagues.forEach(league => {
                    const option = document.createElement('option');
                    option.value = league;
                    option.textContent = league;
                    leagueSelect.appendChild(option);
                });
                
                const otherOption = document.createElement('option');
                otherOption.value = 'other';
                otherOption.textContent = '-- Tambah Baru --';
                leagueSelect.appendChild(otherOption);
                
                leagueStatus.textContent = 'Menggunakan liga default';
            }
        }
    } catch (error) {
        console.error('Error loading leagues:', error);
        document.getElementById('leagueStatus').textContent = 'Gagal memuat liga';
    }
}

// Load leagues when page loads
document.addEventListener('DOMContentLoaded', loadLeagues);

// Handle league dropdown change
document.getElementById('leagueSelect').addEventListener('change', function() {
    const newLeagueInput = document.getElementById('newLeagueInput');
    
    if (this.value === 'other') {
        // Show input for new league
        newLeagueInput.classList.remove('hidden');
        newLeagueInput.focus();
    } else {
        // Hide input
        newLeagueInput.classList.add('hidden');
        newLeagueInput.value = '';
    }
});

// Add new league when Enter is pressed
document.getElementById('newLeagueInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const leagueSelect = document.getElementById('leagueSelect');
        const newLeagueValue = this.value.trim();
        
        if (newLeagueValue) {
            // Add new option to select
            const newOption = document.createElement('option');
            newOption.value = newLeagueValue;
            newOption.textContent = newLeagueValue;
            newOption.selected = true;
            
            // Insert before "other" option
            const otherOption = leagueSelect.querySelector('option[value="other"]');
            leagueSelect.insertBefore(newOption, otherOption);
            
            // Hide input
            this.classList.add('hidden');
            this.value = '';
        }
    }
});

function getSelectedLeague() {
    const leagueSelect = document.getElementById('leagueSelect');
    const newLeagueInput = document.getElementById('newLeagueInput');
    
    if (leagueSelect.value === 'other' && newLeagueInput.value.trim()) {
        return newLeagueInput.value.trim();
    }
    
    return leagueSelect.value;
}

function parseData() {
    const input = document.getElementById('inputText').value;
    const resultDiv = document.getElementById('result');
    const errorDiv = document.getElementById('error');
    const parsedContent = document.getElementById('parsedContent');
    
    // Hide previous results/errors
    resultDiv.classList.add('hidden');
    errorDiv.classList.add('hidden');
    
    try {
        // Split input into blocks (each match has 3 lines)
        const lines = input.trim().split('\n');
        const matches = [];
        
        // Group lines into matches (3 lines per match)
        for (let i = 0; i < lines.length; i += 3) {
            if (i + 2 < lines.length) {
                matches.push({
                    datetimeLine: lines[i],
                    matchLine: lines[i + 1],
                    infoLine: lines[i + 2]
                });
            }
        }
        
        if (matches.length === 0) {
            throw new Error('Tidak ada data pertandingan yang valid ditemukan');
        }
        
        let htmlOutput = `<div class="space-y-6">`;
        let allMatchesData = [];
        
        // Parse each match
        matches.forEach((match, index) => {
            try {
                // Extract datetime
                const datetimeMatch = match.datetimeLine.match(/(\d{4}-\d{2}-\d{2}\s+\d{1,2}:\d{2}\s+(AM|PM))/i);
                
                if (!datetimeMatch) {
                    throw new Error(`Format datetime tidak valid di match ${index + 1}`);
                }
                
                const datetime = datetimeMatch[1];
                
                // Extract match info - Handle both formats
                // Format 1: FT FT (default) - Format 2: FH FT (your data)
                let matchPattern;
                let matchMatch;
                
                // Try FH FT format first (your data): 0 - 0 0 - 0
                matchPattern = /^(.+?)\s+v\s+(.+?)\s+(\d+|\:-)\s*-\s*(\d+|\:-)\s+(\d+|\:-)\s*-\s*(\d+|\:-)$/;
                matchMatch = match.matchLine.match(matchPattern);
                
                if (!matchMatch) {
                    throw new Error(`Format pertandingan tidak valid di match ${index + 1}: ${match.matchLine}`);
                }
                
                // For FH FT format, swap the scores
                const [, homeClub, awayClub, fhHome, fhAway, ftHome, ftAway] = matchMatch;
                
                // Convert to proper format (FT FT)
                const finalFtHome = fhHome;
                const finalFtAway = fhAway;
                const finalHtHome = ftHome;
                const finalHtAway = ftAway;
                
                // Format datetime - treat input as local time
                const dateObj = new Date(datetime);
                const formattedDate = dateObj.toLocaleDateString('id-ID', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                const formattedTime = dateObj.toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                });
                
                // Store match data untuk database
                const selectedLeague = getSelectedLeague();
                
                // Convert -:- to NULL for database
                const convertScore = (score) => score === ':-' ? null : parseInt(score);
                
                const matchData = {
                    match_time: datetime,
                    home_team: homeClub.trim(),
                    away_team: awayClub.trim(),
                    league: selectedLeague,
                    fh_home: convertScore(finalHtHome),
                    fh_away: convertScore(finalHtAway),
                    ft_home: convertScore(finalFtHome),
                    ft_away: convertScore(finalFtAway)
                };
                
                allMatchesData.push(matchData);
                
                // Generate HTML for this match
                htmlOutput += `
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h3 class="text-lg font-semibold mb-3 text-gray-800">Pertandingan ${index + 1}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-3 rounded">
                                <h4 class="font-medium text-gray-700 mb-1">Tanggal & Waktu (WIB)</h4>
                                <p class="text-sm text-gray-600">${formattedDate}, ${formattedTime} WIB</p>
                            </div>
                            
                            <div class="bg-gray-50 p-3 rounded">
                                <h4 class="font-medium text-gray-700 mb-1">Klub</h4>
                                <p class="text-sm text-gray-600">${homeClub.trim()} vs ${awayClub.trim()}</p>
                            </div>
                            
                            <div class="bg-blue-50 p-3 rounded">
                                <h4 class="font-medium text-gray-700 mb-1">Full Time</h4>
                                <p class="text-2xl font-bold text-center text-blue-600">${finalFtHome} - ${finalFtAway}</p>
                            </div>
                            
                            <div class="bg-green-50 p-3 rounded">
                                <h4 class="font-medium text-gray-700 mb-1">Half Time</h4>
                                <p class="text-2xl font-bold text-center text-green-600">${finalHtHome} - ${finalHtAway}</p>
                            </div>
                        </div>
                    </div>
                `;
                
            } catch (matchError) {
                htmlOutput += `
                    <div class="border border-red-200 rounded-lg p-4 bg-red-50">
                        <h3 class="text-lg font-semibold mb-2 text-red-700">Error di Pertandingan ${index + 1}</h3>
                        <p class="text-sm text-red-600">${matchError.message}</p>
                    </div>
                `;
            }
        });
        
        // Add JSON output untuk database
        htmlOutput += `
            <div class="mt-6 p-4 bg-gray-900 rounded-md">
                <h3 class="font-semibold text-white mb-2">Data untuk Database:</h3>
                <pre class="text-xs text-green-400 overflow-x-auto">${JSON.stringify(allMatchesData, null, 2)}</pre>
            </div>
        </div>`;
        
        // Store data globally untuk save function
        window.parsedMatchesData = allMatchesData;
        
        parsedContent.innerHTML = htmlOutput;
        resultDiv.classList.remove('hidden');
        
        // Show save button
        document.getElementById('saveBtn').classList.remove('hidden');
        
    } catch (error) {
        errorDiv.querySelector('p').textContent = error.message;
        errorDiv.classList.remove('hidden');
    }
}

function saveToDatabase() {
    const saveBtn = document.getElementById('saveBtn');
    const saveStatus = document.getElementById('saveStatus');
    
    saveBtn.disabled = true;
    saveBtn.textContent = 'Menyimpan...';
    saveStatus.innerHTML = '<div class="text-blue-600">Menyimpan data ke database...</div>';
    
    fetch('save_matches_simple.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            matches: window.parsedMatchesData
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // SUCCESS - Show big green notification
            saveStatus.innerHTML = `
                <div class="bg-green-100 border-2 border-green-400 text-green-700 px-6 py-4 rounded-lg mt-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-8 h-8 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p class="font-bold text-lg">${data.message}</p>
                                <p class="text-sm">Data pertandingan berhasil disimpan ke database!</p>
                                <p class="text-xs mt-1">✓ Textarea telah dibersihkan</p>
                            </div>
                        </div>
                        <button onclick="location.reload()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">
                            Input Lagi
                        </button>
                    </div>
                </div>
            `;
            
            // Refresh leagues if new data was added
            if (data.refreshLeagues) {
                loadLeagues();
            }
            
            // Clear textarea after successful save
            document.getElementById('inputText').value = '';
            
            // Hide save button and result after clear
            document.getElementById('saveBtn').classList.add('hidden');
            document.getElementById('result').classList.add('hidden');
            
            // Don't disable save button since it will be hidden
            saveBtn.disabled = false;
            saveBtn.textContent = 'Simpan ke Database';
            saveBtn.classList.remove('bg-gray-400');
            saveBtn.classList.add('bg-green-600', 'hover:bg-green-700');
            
        } else {
            saveStatus.innerHTML = `<div class="text-red-600">❌ Error: ${data.error}</div>`;
        }
    })
    .catch(error => {
        saveStatus.innerHTML = `<div class="text-red-600">❌ Error: ${error.message}</div>`;
    })
    .finally(() => {
        // Don't re-enable if success
        if (saveBtn.textContent !== '✅ Tersimpan') {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Simpan ke Database';
        }
    });
}
</script>
