<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Price Prediction Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts: Orbitron & IBM Plex Sans -->
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;600&family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'IBM Plex Sans', Arial, sans-serif;
            background: linear-gradient(135deg, #1a1f2c, #2d3436);
            color: #ffffff;
            min-height: 100vh;
        }

        h1,
        h4,
        .btn-model,
        .card-title,
        .accuracy-score,
        .trend-indicator,
        .confidence-score {
            font-family: 'Orbitron', 'IBM Plex Sans', Arial, sans-serif;
            letter-spacing: 1px;
        }

        h1,
        h4 {
            text-shadow: 0 0 12px #00ffe7, 0 0 2px #fff;
        }

        .card {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(12px);
            border: 1.5px solid rgba(0, 255, 255, 0.15);
            border-radius: 18px;
            box-shadow: 0 4px 32px 0 rgba(0, 255, 255, 0.08), 0 1.5px 8px 0 rgba(0, 0, 0, 0.12);
            transition: box-shadow 0.3s;
        }

        .card:hover {
            box-shadow: 0 0 32px 0 #00ffe7, 0 1.5px 8px 0 rgba(0, 0, 0, 0.18);
        }

        .model-selector {
            background: rgba(0, 255, 255, 0.10);
            border-radius: 14px;
            padding: 24px;
            margin-bottom: 28px;
            box-shadow: 0 0 18px 0 #00ffe7;
        }

        .chart-container {
            position: relative;
            height: 60vh;
            border-radius: 18px;
            box-shadow: 0 0 32px 0 #00ffe7, 0 0 8px 0 #fff;
            border: 2px solid #00ffe7;
            background: rgba(20, 30, 40, 0.25);
            transition: all 0.3s ease;
        }

        .btn-model {
            font-family: 'Orbitron', 'IBM Plex Sans', Arial, sans-serif;
            background: linear-gradient(90deg, #00ffe7 0%, #007cf0 100%);
            color: #1a1f2c;
            border: none;
            margin: 5px;
            font-weight: 700;
            box-shadow: 0 0 8px #00ffe7;
            text-shadow: 0 0 4px #fff;
            transition: all 0.3s cubic-bezier(.4, 2, .3, 1);
        }

        .btn-model:hover,
        .btn-model.active {
            background: linear-gradient(90deg, #007cf0 0%, #00ffe7 100%);
            color: #fff;
            box-shadow: 0 0 24px #00ffe7, 0 0 8px #fff;
            transform: scale(1.07) translateY(-2px);
        }

        .prediction-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 10px 22px;
            border-radius: 24px;
            background: linear-gradient(90deg, #00ffe7 0%, #007cf0 100%);
            color: #1a1f2c;
            font-family: 'Orbitron', 'IBM Plex Sans', Arial, sans-serif;
            font-weight: 700;
            font-size: 1.1rem;
            box-shadow: 0 0 18px #00ffe7, 0 0 4px #fff;
            animation: glow 2s infinite alternate;
        }

        @keyframes glow {
            0% {
                box-shadow: 0 0 8px #00ffe7, 0 0 2px #fff;
            }

            100% {
                box-shadow: 0 0 32px #00ffe7, 0 0 8px #fff;
            }
        }

        .accuracy-score,
        .trend-indicator,
        .confidence-score {
            font-size: 2.1rem;
            font-weight: 700;
            text-shadow: 0 0 8px #00ffe7, 0 0 2px #fff;
            color: #00ffe7;
        }

        .trend-indicator {
            color: #ffb300;
            text-shadow: 0 0 8px #ffb300, 0 0 2px #fff;
        }

        .confidence-score {
            color: #00ff7f;
            text-shadow: 0 0 8px #00ff7f, 0 0 2px #fff;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <h1 class="text-center mb-5">Stock Price Prediction Dashboard</h1>

        <div class="model-selector text-center">
            <h4 class="mb-3">Select Prediction Model</h4>
            <div class="mb-3">
                <input type="text" id="stockCode" class="form-control d-inline-block w-auto" placeholder="Kode Saham (misal: BBCA)" style="font-family: 'Orbitron', 'IBM Plex Sans', Arial, sans-serif; font-size:1.1rem; text-align:center; max-width:180px;">
                <button id="predictBtn" class="btn btn-model ms-2">Prediksi</button>
            </div>
            <div class="btn-group" role="group">
                <button class="btn btn-model active" data-model="1">LSTM</button>
                <button class="btn btn-model" data-model="2">Model 2</button>
                <button class="btn btn-model" data-model="3">Model 3</button>
            </div>
            <div id="loadingBar" class="mt-3" style="display:none;">
                <div class="progress" style="height: 24px; background:rgba(0,255,255,0.08);">
                    <div id="loadingProgress" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%; font-weight:700; font-size:1.1rem; background:linear-gradient(90deg,#00ffe7,#007cf0); color:#1a1f2c; text-shadow:0 0 4px #fff;">0%</div>
                </div>
                <div class="mt-2" style="color:#00ffe7; font-family:'Orbitron',sans-serif;">Sedang memproses prediksi...</div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="stockChart"></canvas>
                    <div class="prediction-badge">Prediction Zone</div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Model Accuracy</h5>
                        <div class="accuracy-score">95.8%</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Predicted Trend</h5>
                        <div class="trend-indicator">Bullish ↗</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Confidence Score</h5>
                        <div class="confidence-score">High</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Initialize chart with animation
        const ctx = document.getElementById('stockChart').getContext('2d');
        let stockChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Predicted Price',
                    data: [],
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.4,
                    pointRadius: 2,
                    pointHoverRadius: 6,
                    segment: {
                        borderColor: ctx => ctx.p0.parsed.x < 50 ? 'rgba(75, 192, 192, 1)' : 'rgba(255, 99, 132, 1)',
                    }
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                },
                scales: {
                    y: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: '#ffffff'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: '#ffffff'
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: '#ffffff'
                        }
                    }
                }
            }
        });

        let lastStockCode = 'BBCA';
        let lastModelId = 1;

        // Loading progress bar
        function showLoadingBar() {
            document.getElementById('loadingBar').style.display = '';
            updateLoadingBar(0);
        }

        function hideLoadingBar() {
            document.getElementById('loadingBar').style.display = 'none';
        }

        function updateLoadingBar(percent) {
            const bar = document.getElementById('loadingProgress');
            bar.style.width = percent + '%';
            bar.innerText = percent + '%';
        }
        // Simulasi progress loading
        function simulateLoading(duration, onDone) {
            let percent = 0;
            showLoadingBar();
            const interval = setInterval(() => {
                percent += Math.floor(Math.random() * 10) + 5;
                if (percent >= 95) percent = 95;
                updateLoadingBar(percent);
            }, duration / 10);
            return () => {
                clearInterval(interval);
                updateLoadingBar(100);
                setTimeout(hideLoadingBar, 500);
                if (onDone) onDone();
            };
        }

        // Fetch prediction from Flask API for LSTM
        async function fetchLSTMData(stockCode) {
            try {
                const response = await fetch(`http://localhost:5000/predict?ticker=${stockCode}&days=100`);
                const data = await response.json();
                if (data.error) throw new Error(data.error);
                return {
                    dates: data.dates,
                    prices: data.prices
                };
            } catch (e) {
                alert('Gagal mengambil prediksi: ' + e.message);
                // Fallback dummy data
                const dates = [];
                const prices = [];
                const startDate = new Date('2025-01-01');
                for (let i = 0; i < 100; i++) {
                    const date = new Date(startDate);
                    date.setDate(startDate.getDate() + i);
                    dates.push(date.toLocaleDateString());
                    prices.push(100 + Math.sin(i / 10) * 20 + Math.random() * 10);
                }
                return {
                    dates,
                    prices
                };
            }
        }

        // Function to update chart data
        async function updateChartData(modelId, stockCode) {
            let dates = [];
            let prices = [];
            if (modelId == 1) {
                // LSTM
                const stopLoading = simulateLoading(2000);
                const result = await fetchLSTMData(stockCode);
                stopLoading();
                dates = result.dates;
                prices = result.prices;
                stockChart.data.datasets[0].label = 'LSTM Prediction';
            } else {
                // Dummy for Model 2 & 3
                const startDate = new Date('2025-01-01');
                for (let i = 0; i < 100; i++) {
                    const date = new Date(startDate);
                    date.setDate(startDate.getDate() + i);
                    dates.push(date.toLocaleDateString());
                    prices.push(100 + Math.cos(i / 8) * 15 + Math.random() * 8 + modelId * 10);
                }
                stockChart.data.datasets[0].label = 'Model ' + modelId + ' Prediction';
            }
            stockChart.data.labels = dates;
            stockChart.data.datasets[0].data = prices;
            stockChart.update('active');
        }

        // Event listeners for model selection
        document.querySelectorAll('.btn-model').forEach(button => {
            button.addEventListener('click', function() {
                document.querySelectorAll('.btn-model').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                lastModelId = parseInt(this.dataset.model);
                updateChartData(lastModelId, lastStockCode);
            });
        });

        // Event listener for predict button
        document.getElementById('predictBtn').addEventListener('click', function() {
            const code = document.getElementById('stockCode').value.trim().toUpperCase() || 'BBCA';
            lastStockCode = code;
            updateChartData(lastModelId, lastStockCode);
        });

        // Initial chart update (LSTM, BBCA)
        updateChartData(1, 'BBCA');
    </script>
</body>

</html>