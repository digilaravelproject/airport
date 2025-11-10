sudo tee /var/www/html/device_status.php > /dev/null <<'PHP'
<?php
declare(strict_types=1);
date_default_timezone_set('Asia/Kolkata');
set_time_limit(0);

// Linux-compatible IPTV status monitor
$adbPath = '/usr/bin/adb';
$baseIP = '192.168.1.';   // subnet base
$start = 1;
$end = 254;
$batchSize = 10;
$lastFile = __DIR__ . '/last_report.json';

if (isset($_GET['action']) && $_GET['action'] === 'check' && isset($_GET['ip'])) {
    header('Content-Type: application/json');
    $ip = $_GET['ip'];
    $alive = false;
    $app = '-';
    $udp = '-';

    // Ping
    $pingCmd = "ping -c 1 -W 1 " . escapeshellarg($ip);
    @exec($pingCmd . ' 2>&1', $outPing, $pingStatus);
    if (isset($pingStatus) && $pingStatus === 0) $alive = true;

    if ($alive) {
        $deviceID = null;
        @exec($adbPath . ' devices', $devicesOutput);
        foreach ($devicesOutput as $line) {
            if (preg_match('/^' . preg_quote($ip, '/') . ':5555\s+device$/', trim($line))) {
                $deviceID = $ip . ':5555';
                break;
            }
        }

        if ($deviceID) {
            $cmdApp = "$adbPath -s $deviceID shell dumpsys window | grep mCurrentFocus";
            @exec($cmdApp . ' 2>&1', $outApp);
            $app = 'Unknown';
            $udp = '-';
            if (!empty($outApp)) {
                $joined = implode("\n", $outApp);
                if (preg_match('/\s([a-zA-Z0-9._]+)\/[a-zA-Z0-9._\/$]+/', $joined, $m)) {
                    $pkg = $m[1];
                    if ($pkg === 'com.aminocom.browser') $app = 'Amino Zapper';
                    elseif ($pkg === 'com.aminocom.settingsmenu') { $app = 'Amino Settings'; $udp = '-'; }
                    elseif ($pkg === 'com.aminocom.aosplauncher') { $app = 'Booting'; $udp = '-'; }
                    else $app = $pkg;
                }
            }
            if ($app !== 'Amino Settings' && $app !== 'Booting') {
                $cmdUdp = "$adbPath -s $deviceID shell netstat -anu";
                @exec($cmdUdp . ' 2>&1', $outUdp);
                $found = [];
                foreach ($outUdp as $line) {
                    $line = trim($line);
                    if ($line === '') continue;
                    if (preg_match('/\b(\d{1,3}(?:\.\d{1,3}){3}:\d+)\b/', $line, $mu)) $found[] = $mu[1];
                }
                if (!empty($found)) {
                    $multicast = array_values(array_filter($found, fn($v) => strpos($v, '239.') === 0));
                    $udp = !empty($multicast) ? implode(', ', $multicast) : implode(', ', array_unique($found));
                }
            }
        } else { $app = 'Device Not Found'; $udp = '-'; }
    }

    // Append to last_report.json for PDF/export if desired (keeps history)
    $results = @json_decode(@file_get_contents($lastFile), true) ?? [];
    $results[] = ['ip' => $ip, 'online' => $alive, 'app' => $app, 'udp' => $udp, 'time' => date('Y-m-d H:i:s')];
    @file_put_contents($lastFile, json_encode($results, JSON_PRETTY_PRINT));

    echo json_encode(['ip' => $ip, 'online' => $alive, 'app' => $app, 'udp' => $udp]);
    exit;
}

// Serve the HTML UI
$nowDate = date('Y-m-d');
$nowTime = date('H:i:s');
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>IPTV Status Monitor</title>
<style>
body { font-family: Arial, Helvetica, sans-serif; margin: 20px; }
.header { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; }
.header .left { line-height:1.1; }
.controls { margin-bottom:12px; display:flex; gap:8px; align-items:center; }
input[type="text"] { padding:6px; font-size:14px; }
.btn { padding:8px 12px; background:#3498db; color:#fff; border:none; border-radius:4px; cursor:pointer; }
table { width:90%; border-collapse:collapse; margin-top:12px; margin-left:auto; margin-right:auto; }
th, td { border:1px solid #ccc; padding:6px; text-align:center; font-size:13px; }
th { background:#f2f2f2; }
.alive-zapper { background:#d4edda; }
.alive-other { background:#fff3cd; }
.alive-settings { background:#cce5ff; }
.alive-booting { background:#ffd699; }
.dead { background:#f8d7da; }
.search { margin-left:8px; padding:6px; }
.footer { margin-top:12px; font-size:12px; color:#555; text-align:center; }
</style>
</head>
<body>
<div class="header">
  <div class="left">
    <h2 style="margin:0">IPTV Status Monitor</h2>
    <div style="font-size:13px;color:#333">Date: <?=$nowDate?> &nbsp;&nbsp; Time: <?=$nowTime?></div>
  </div>
  <div>
    <button class="btn" id="exportPdf">📄 Export to PDF</button>
  </div>
</div>

<div class="controls">
  <label>Auto-refresh (seconds): <input id="refreshSec" type="text" value="60" size="4"></label>
  <label style="margin-left:12px">Search: <input id="searchBox" class="search" type="text" placeholder="IP, status or app"></label>
  <button class="btn" id="startBtn">Start Scan</button>
  <button class="btn" id="stopBtn">Stop</button>
</div>

<table id="resultsTable">
<tr><th>IP</th><th>Online</th><th>App Status</th><th>UDP Stream</th><th>Checked</th></tr>
</table>

<div class="footer">Scan range: <?=$baseIP?>1 — <?=$baseIP?>254 (batches of <?=$batchSize?>)</div>

<script>
const baseIP = '<?=$baseIP?>', start = <?=$start?>, end = <?=$end?>, batchSize = <?=$batchSize?>;
let running = false;
let refreshInterval = null;

function buildIPList() {
  const ips = [];
  for (let i = start; i <= end; i++) ips.push(baseIP + i);
  return ips;
}

async function scanBatch(batch) {
  const table = document.getElementById('resultsTable');
  const rows = {};
  for (let ip of batch) {
    let row = document.createElement('tr');
    row.innerHTML = `<td>${ip}</td><td>...</td><td>...</td><td>...</td><td>...</td>`;
    table.appendChild(row);
    rows[ip] = row;
  }

  await Promise.all(batch.map(async ip => {
    try {
      let resp = await fetch(`?action=check&ip=${ip}`);
      let data = await resp.json();
      let cls = 'dead';
      if (data.online) {
        if (data.app === 'Amino Zapper') cls = 'alive-zapper';
        else if (data.app === 'Amino Settings') cls = 'alive-settings';
        else if (data.app === 'Booting') cls = 'alive-booting';
        else cls = 'alive-other';
      }
      rows[ip].className = cls;
      rows[ip].innerHTML = `<td>${data.ip}</td><td>${data.online ? 'Yes' : 'No'}</td><td>${data.app}</td><td>${data.udp}</td><td>${new Date().toLocaleString()}</td>`;
    } catch (e) {
      rows[ip].className = 'dead';
      rows[ip].innerHTML = `<td>${ip}</td><td>Error</td><td>-</td><td>-</td><td>${new Date().toLocaleString()}</td>`;
    }
  }));
}

async function fullScanOnce() {
  const ipList = buildIPList();
  const table = document.getElementById('resultsTable');
  table.querySelectorAll('tr:not(:first-child)').forEach(n => n.remove());
  while (ipList.length > 0 && running) {
    const batch = ipList.splice(0, batchSize);
    await scanBatch(batch);
    // short pause between batches
    await new Promise(r => setTimeout(r, 500));
  }
}

function startScanning() {
  if (running) return;
  running = true;
  document.getElementById('startBtn').disabled = true;
  document.getElementById('stopBtn').disabled = false;
  fullScanOnce();
  // set auto refresh
  const sec = parseInt(document.getElementById('refreshSec').value, 10) || 60;
  if (refreshInterval) clearInterval(refreshInterval);
  refreshInterval = setInterval(() => { if (!running) return; fullScanOnce(); }, sec * 1000);
}

function stopScanning() {
  running = false;
  document.getElementById('startBtn').disabled = false;
  document.getElementById('stopBtn').disabled = true;
  if (refreshInterval) clearInterval(refreshInterval);
}

document.getElementById('startBtn').addEventListener('click', startScanning);
document.getElementById('stopBtn').addEventListener('click', stopScanning);
document.getElementById('stopBtn').disabled = true;

// Search/filter
document.getElementById('searchBox').addEventListener('input', function() {
  const q = this.value.trim().toLowerCase();
  document.querySelectorAll('#resultsTable tr:not(:first-child)').forEach(row => {
    const txt = row.innerText.toLowerCase();
    row.style.display = txt.includes(q) ? '' : 'none';
  });
});

// Export to PDF (client-side printable snapshot)
document.getElementById('exportPdf').addEventListener('click', function() {
  // Build printable HTML from table
  const tableHtml = document.getElementById('resultsTable').outerHTML;
  const title = '<h2 style="text-align:center">IPTV Status Report</h2>';
  const dateHtml = '<div style="text-align:center">Generated: ' + new Date().toLocaleString() + '</div>';
  const win = window.open('', '_blank');
  win.document.write('<html><head><title>IPTV Status</title>');
  win.document.write('<style>table{width:100%;border-collapse:collapse}th,td{border:1px solid #ccc;padding:6px;text-align:center}</style>');
  win.document.write('</head><body>');
  win.document.write(title + dateHtml + tableHtml);
  win.document.write('</body></html>');
  win.document.close();
  win.focus();
  setTimeout(()=>{ win.print(); }, 500);
});
</script>
</body>
</html>
PHP
