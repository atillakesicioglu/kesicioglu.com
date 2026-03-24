<?php
require_once __DIR__ . '/../bootstrap.php';
admin_guard();

$pageTitle = 'Jarvis Control Center';

// Initial stats (Mocked if restricted)
$mem = ['percent' => 25, 'used' => 1024, 'total' => 4096, 'restricted' => true];
$disk = ['percent' => 45, 'used' => 45, 'total' => 100, 'restricted' => true];
$load = '0.12 0.08 0.05';
$uptime = 'up 4 hours, 12 minutes';

include 'includes/header.php';
?>

<!-- Extra scripts for Charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 style="color: #22d3ee; font-weight: 800;">JARVIS CONTROL CENTER</h1>
        <p>OpenClaw Sistem Verileri ve Operasyonel Durum</p>
    </div>
    <button onclick="updateStats()" class="btn btn-primary" id="refresh-btn">
        <i class="fas fa-sync-alt" id="refresh-icon"></i> Verileri Yenile
    </button>
</div>

<!-- OpenClaw Specific Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
            <i class="fas fa-coins"></i>
        </div>
        <div class="stat-details">
            <h3 id="oc-tokens-in">168k</h3>
            <p>Tokens In</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
            <i class="fas fa-paper-plane"></i>
        </div>
        <div class="stat-details">
            <h3 id="oc-tokens-out">1.9k</h3>
            <p>Tokens Out</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <i class="fas fa-bolt"></i>
        </div>
        <div class="stat-details">
            <h3 id="oc-cache-hit">48%</h3>
            <p>Cache Hit</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
            <i class="fas fa-brain"></i>
        </div>
        <div class="stat-details">
            <h3 id="oc-context">68k/1m</h3>
            <p>Context Usage</p>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Charts Card -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-chart-line"></i> Kaynak Analitiği (RAM & Disk)</h3>
        </div>
        <div class="card-body">
            <div style="height: 300px;">
                <canvas id="resourceChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Quick Commands -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-terminal"></i> Hızlı Komutlar</h3>
        </div>
        <div class="card-body">
            <div class="quick-actions">
                <button onclick="runCommand('status')" class="quick-action-btn" style="border: none; cursor: pointer;">
                    <i class="fas fa-info-circle"></i>
                    <span>OC Status</span>
                </button>
                <button onclick="runCommand('restart')" class="quick-action-btn" style="border: none; cursor: pointer;">
                    <i class="fas fa-sync"></i>
                    <span>Gateway Restart</span>
                </button>
                <button onclick="runCommand('clean_logs')" class="quick-action-btn" style="border: none; cursor: pointer;">
                    <i class="fas fa-broom"></i>
                    <span>Clean Logs</span>
                </button>
                <button onclick="window.location.reload()" class="quick-action-btn" style="border: none; cursor: pointer;">
                    <i class="fas fa-redo"></i>
                    <span>Sayfayı Yenile</span>
                </button>
            </div>
            
            <div id="terminal-box" style="margin-top: 24px; background: #000; border-radius: 8px; padding: 16px; border-left: 4px solid #22d3ee; display: none;">
                <pre id="terminal-output" style="color: #10b981; font-family: 'Fira Code', monospace; font-size: 13px; white-space: pre-wrap; margin: 0;"></pre>
            </div>
        </div>
    </div>
</div>

<script>
    // Chart Setup
    const ctx = document.getElementById('resourceChart').getContext('2d');
    const resourceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['-8m', '-7m', '-6m', '-5m', '-4m', '-3m', '-2m', '-1m', 'Şimdi'],
            datasets: [{
                label: 'RAM %',
                data: [20, 22, 21, 25, 24, 26, 25, 24, 25],
                borderColor: '#22d3ee',
                backgroundColor: 'rgba(34, 211, 238, 0.1)',
                fill: true,
                tension: 0.4
            }, {
                label: 'Disk %',
                data: [45, 45, 45, 45, 45, 45, 45, 45, 45],
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, max: 100, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } },
                x: { grid: { display: false }, ticks: { color: '#94a3b8' } }
            },
            plugins: {
                legend: { labels: { color: '#94a3b8' } }
            }
        }
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function jarvisFetch(action) {
        const formData = new FormData();
        formData.append('action', action);
        formData.append('_csrf', csrfToken);
        return fetch('api.php', { method: 'POST', body: formData }).then(r => r.json());
    }

    function updateStats() {
        const icon = document.getElementById('refresh-icon');
        icon.classList.add('fa-spin');

        jarvisFetch('get_stats')
        .then(res => {
            if(res.status === 'success') {
                const data = res.data;
                // Update OpenClaw Numbers
                document.getElementById('oc-tokens-in').innerText = data.oc_tokens_in;
                document.getElementById('oc-tokens-out').innerText = data.oc_tokens_out;
                document.getElementById('oc-cache-hit').innerText = data.oc_cache_hit;
                document.getElementById('oc-context').innerText = data.oc_context;

                // Update Chart
                resourceChart.data.datasets[0].data.shift();
                resourceChart.data.datasets[0].data.push(data.ram_percent);
                resourceChart.data.datasets[1].data.shift();
                resourceChart.data.datasets[1].data.push(data.disk_percent);
                resourceChart.update();
            }
            setTimeout(() => icon.classList.remove('fa-spin'), 500);
        })
        .catch(err => {
            console.error(err);
            icon.classList.remove('fa-spin');
        });
    }

    function runCommand(cmd) {
        const box = document.getElementById('terminal-box');
        const output = document.getElementById('terminal-output');
        box.style.display = 'block';
        output.innerText = '> ' + cmd + ' ...\n';

        jarvisFetch(cmd)
        .then(data => {
            if(data.status === 'success') {
                output.innerText += '> [OK]: ' + data.output;
            } else {
                output.innerText += '> [ERROR]: ' + data.message;
            }
        })
        .catch(() => {
            output.innerText += '> [ERROR]: Connection error.';
        });
    }

    // Load stats on page ready
    window.onload = updateStats;
</script>

<?php include 'includes/footer.php'; ?>
