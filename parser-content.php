<div class="p-8 max-w-5xl mx-auto">
    <div class="mb-10">
        <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight mb-2">Parser Data Pertandingan</h1>
        <p class="text-slate-500 font-medium">Transformasi data mentah menjadi format database yang terstruktur</p>
    </div>
    
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-8 mb-10 transition-all hover:shadow-md">
        <div class="mb-8">
            <label for="inputText" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">
                Input Data Pertandingan
            </label>
            <textarea 
                id="inputText" 
                class="w-full h-64 p-5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm font-mono transition-all resize-none"
                placeholder="Paste data pertandingan di sini (Contoh: 3 baris per pertandingan)..."></textarea>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <div class="flex flex-col gap-3">
                <label for="leagueSelect" class="block text-xs font-bold text-slate-500 uppercase tracking-widest">
                    Pilih Liga/Kompetisi
                </label>
                <div class="flex flex-col gap-2">
                    <select 
                        id="leagueSelect" 
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm appearance-none transition-all">
                        <option value="">-- Pilih Liga --</option>
                    </select>
                    <input 
                        type="text" 
                        id="newLeagueInput" 
                        class="hidden w-full px-4 py-3 bg-blue-50 border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-all"
                        placeholder="Masukkan nama liga baru...">
                    <div id="leagueStatus" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider"></div>
                </div>
            </div>
            
            <div class="flex items-end gap-3">
                <button 
                    onclick="parseData()" 
                    class="flex-1 bg-slate-900 text-white px-6 py-3 rounded-xl font-bold text-sm hover:bg-slate-800 transition-all shadow-lg shadow-slate-900/10 active:scale-95 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                    Proses Data
                </button>
                <button 
                    id="saveBtn"
                    onclick="saveToDatabase()" 
                    class="hidden flex-1 bg-blue-600 text-white px-6 py-3 rounded-xl font-bold text-sm hover:bg-blue-700 transition-all shadow-lg shadow-blue-600/10 active:scale-95 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                    Simpan Database
                </button>
            </div>
        </div>
        
        <div id="saveStatus"></div>
    </div>

    <!-- Results Section -->
    <div id="result" class="hidden animate-in fade-in duration-500">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-8 overflow-hidden">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Preview Hasil Parsing</h2>
                <span id="matchCountBadge" class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-[10px] font-bold uppercase tracking-wider"></span>
            </div>
            <div id="parsedContent" class="space-y-4"></div>
        </div>
    </div>

    <div id="error" class="hidden">
        <div class="bg-red-50 border border-red-100 rounded-2xl p-6 flex items-center gap-4">
            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h4 class="text-sm font-bold text-red-900 uppercase tracking-wide">Error Terdeteksi</h4>
                <p class="text-sm text-red-600 font-medium mt-1"></p>
            </div>
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
            
            leagueSelect.innerHTML = '<option value="">-- Pilih Liga --</option>';
            
            if (data.leagues.length > 0) {
                data.leagues.forEach(league => {
                    const option = document.createElement('option');
                    option.value = league;
                    option.textContent = league;
                    leagueSelect.appendChild(option);
                });
                
                const otherOption = document.createElement('option');
                otherOption.value = 'other';
                otherOption.textContent = '-- Tambah Baru --';
                leagueSelect.appendChild(otherOption);
                
                leagueStatus.textContent = `${data.leagues.length} liga tersimpan`;
            } else {
                const defaultLeagues = [
                    'SABA CLUB FRIENDLY Virtual PES 21 - 15 Mins Play',
                    'SABA INTERNATIONAL FRIENDLY Virtual PES 21 - 20 Mins Play'
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
                
                leagueStatus.textContent = 'Mode Default';
            }
        }
    } catch (error) {
        console.error('Error loading leagues:', error);
        document.getElementById('leagueStatus').textContent = 'Koneksi database bermasalah';
    }
}

document.addEventListener('DOMContentLoaded', loadLeagues);

document.getElementById('leagueSelect').addEventListener('change', function() {
    const newLeagueInput = document.getElementById('newLeagueInput');
    if (this.value === 'other') {
        newLeagueInput.classList.remove('hidden');
        newLeagueInput.focus();
    } else {
        newLeagueInput.classList.add('hidden');
        newLeagueInput.value = '';
    }
});

document.getElementById('newLeagueInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const leagueSelect = document.getElementById('leagueSelect');
        const newLeagueValue = this.value.trim();
        
        if (newLeagueValue) {
            const newOption = document.createElement('option');
            newOption.value = newLeagueValue;
            newOption.textContent = newLeagueValue;
            newOption.selected = true;
            const otherOption = leagueSelect.querySelector('option[value="other"]');
            leagueSelect.insertBefore(newOption, otherOption);
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
    const badge = document.getElementById('matchCountBadge');
    
    resultDiv.classList.add('hidden');
    errorDiv.classList.add('hidden');
    
    try {
        const lines = input.trim().split('\n');
        const matches = [];
        
        for (let i = 0; i < lines.length; i += 3) {
            if (i + 2 < lines.length) {
                matches.push({
                    datetimeLine: lines[i],
                    matchLine: lines[i + 1],
                    infoLine: lines[i + 2]
                });
            }
        }
        
        if (matches.length === 0) throw new Error('Input tidak valid atau kosong');
        
        let htmlOutput = ``;
        let allMatchesData = [];
        const selectedLeague = getSelectedLeague();
        
        matches.forEach((match, index) => {
            try {
                const datetimeMatch = match.datetimeLine.match(/(\d{4}-\d{2}-\d{2}\s+\d{1,2}:\d{2}\s+(AM|PM))/i);
                if (!datetimeMatch) throw new Error(`Format waktu tidak valid`);
                
                const datetime = datetimeMatch[1];
                const matchPattern = /^(.+?)\s+v\s+(.+?)\s+(\d+|\:-)\s*-\s*(\d+|\:-)\s+(\d+|\:-)\s*-\s*(\d+|\:-)$/;
                const matchMatch = match.matchLine.match(matchPattern);
                
                if (!matchMatch) throw new Error(`Format tim/skor tidak valid`);
                
                const [, homeClub, awayClub, fhHome, fhAway, ftHome, ftAway] = matchMatch;
                const finalFtHome = fhHome;
                const finalFtAway = fhAway;
                const finalHtHome = ftHome;
                const finalHtAway = ftAway;
                
                const dateObj = new Date(datetime);
                const formattedDate = dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
                const formattedTime = dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                
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
                
                htmlOutput += `
                    <div class="flex flex-col md:flex-row md:items-center gap-6 p-6 bg-slate-50 border border-slate-100 rounded-2xl hover:border-blue-200 transition-colors group">
                        <div class="md:w-32 shrink-0">
                            <span class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">${formattedTime}</span>
                            <span class="block text-sm font-black text-slate-800">${formattedDate}</span>
                        </div>
                        
                        <div class="flex-1 flex items-center justify-between gap-4">
                            <div class="flex-1 text-right">
                                <span class="text-sm font-bold text-slate-800 group-hover:text-blue-600 transition-colors">${homeClub.trim()}</span>
                            </div>
                            
                            <div class="shrink-0 flex flex-col items-center gap-1 px-4">
                                <div class="flex items-center gap-2 bg-white px-3 py-1 rounded-xl shadow-sm border border-slate-100">
                                    <span class="text-lg font-black text-slate-900">${finalFtHome}</span>
                                    <span class="text-slate-300 font-bold">:</span>
                                    <span class="text-lg font-black text-slate-900">${finalFtAway}</span>
                                </div>
                                <span class="text-[10px] font-bold text-slate-400">HT ${finalHtHome}-${finalHtAway}</span>
                            </div>
                            
                            <div class="flex-1 text-left">
                                <span class="text-sm font-bold text-slate-800 group-hover:text-blue-600 transition-colors">${awayClub.trim()}</span>
                            </div>
                        </div>
                    </div>
                `;
            } catch (e) {
                htmlOutput += `
                    <div class="p-4 bg-red-50 border border-red-100 rounded-xl text-xs text-red-600 font-medium">
                        #${index + 1}: ${e.message}
                    </div>
                `;
            }
        });
        
        window.parsedMatchesData = allMatchesData;
        parsedContent.innerHTML = htmlOutput;
        badge.textContent = `${allMatchesData.length} Pertandingan`;
        resultDiv.classList.remove('hidden');
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
    saveBtn.innerHTML = '<svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Menyimpan...';
    
    fetch('save_matches_simple.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ matches: window.parsedMatchesData })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            saveStatus.innerHTML = `
                <div class="mt-6 p-6 bg-blue-600 rounded-2xl text-white flex items-center justify-between animate-in slide-in-from-bottom duration-500">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <p class="font-bold tracking-tight">${data.message}</p>
                            <p class="text-xs text-blue-100 font-medium opacity-80">Database telah diperbarui secara real-time</p>
                        </div>
                    </div>
                    <button onclick="location.reload()" class="bg-white text-blue-600 px-4 py-2 rounded-xl font-bold text-xs hover:bg-blue-50 transition-colors">Input Lagi</button>
                </div>
            `;
            document.getElementById('inputText').value = '';
            document.getElementById('saveBtn').classList.add('hidden');
            document.getElementById('result').classList.add('hidden');
        } else {
            saveStatus.innerHTML = `<div class="mt-4 p-4 bg-red-50 text-red-600 rounded-xl text-sm font-bold">Error: ${data.error}</div>`;
        }
    })
    .catch(error => {
        saveStatus.innerHTML = `<div class="mt-4 p-4 bg-red-50 text-red-600 rounded-xl text-sm font-bold">Error: ${error.message}</div>`;
    })
    .finally(() => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = 'Simpan Database';
    });
}
</script>
