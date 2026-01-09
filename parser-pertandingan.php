<?php include 'koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parser Data Pertandingan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="container mx-auto px-4 max-w-4xl">
        <h1 class="text-3xl font-bold text-center mb-8 text-gray-800">Parser Data Pertandingan</h1>
        
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <label for="inputText" class="block text-sm font-medium text-gray-700 mb-2">
                Masukkan Data Pertandingan:
            </label>
            <textarea 
                id="inputText" 
                class="w-full h-48 p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Paste data pertandingan di sini...">2026-01-09 12:00 PMCOMPLETED	FH	FT
Sevilla (V)  v Real Madrid (V)	2 - 1	2 - 1
More Info 
2026-01-09 12:00 PMCOMPLETED	FH	FT
Bayer 04 Leverkusen (V)  v Juventus (V)	1 - 1	1 - 1
More Info 
2026-01-09 12:00 PMCOMPLETED	FH	FT
Everton (V)  v AC Milan (V)	0 - 3	0 - 3
More Info</textarea>
            
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
                        
                        // Extract match info
                        const matchPattern = /^(.+?)\s+v\s+(.+?)\s+(\d+)\s*-\s*(\d+)\s+(\d+)\s*-\s*(\d+)$/;
                        const matchMatch = match.matchLine.match(matchPattern);
                        
                        if (!matchMatch) {
                            throw new Error(`Format pertandingan tidak valid di match ${index + 1}`);
                        }
                        
                        const [, homeClub, awayClub, ftHome, ftAway, htHome, htAway] = matchMatch;
                        
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
                        const matchData = {
                            match_time: datetime,
                            home_team: homeClub.trim(),
                            away_team: awayClub.trim(),
                            league: 'SABA CLUB FRIENDLY', // Default league
                            fh_home: parseInt(htHome),
                            fh_away: parseInt(htAway),
                            ft_home: parseInt(ftHome),
                            ft_away: parseInt(ftAway)
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
                                        <p class="text-2xl font-bold text-center text-blue-600">${ftHome} - ${ftAway}</p>
                                    </div>
                                    
                                    <div class="bg-green-50 p-3 rounded">
                                        <h4 class="font-medium text-gray-700 mb-1">Half Time</h4>
                                        <p class="text-2xl font-bold text-center text-green-600">${htHome} - ${htAway}</p>
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
            
            fetch('save_matches.php', {
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
                    saveStatus.innerHTML = `<div class="text-green-600 font-semibold">✅ Berhasil menyimpan ${data.count} pertandingan!</div>`;
                } else {
                    saveStatus.innerHTML = `<div class="text-red-600">❌ Error: ${data.error}</div>`;
                }
            })
            .catch(error => {
                saveStatus.innerHTML = `<div class="text-red-600">❌ Error: ${error.message}</div>`;
            })
            .finally(() => {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Simpan ke Database';
            });
        }
        
        // Auto-parse on page load with sample data
        window.addEventListener('load', () => {
            setTimeout(() => parseData(), 500);
        });
    </script>
</body>
</html>
