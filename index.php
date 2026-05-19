<?php
require_once "api/get_crypto.php";

$error = "";
$cryptoData = [];
$dataSource = "Live CoinGecko API";
$apiStatus = "online";
$errorMessage = "";

try {
    $cryptoData = getCryptoData($dataSource, $apiStatus, $errorMessage);
} catch (Exception $e) {
    $error = $e->getMessage();
    $apiStatus = "error_system";
    $errorMessage = "Terjadi kesalahan sistem: " . $error;
}

$totalCoin = count($cryptoData);

// Hitung statistik tren pasar dari top koin
$bullish = 0;
$bearish = 0;
if (!empty($cryptoData)) {
    foreach ($cryptoData as $coin) {
        if (isset($coin['price_change_percentage_24h'])) {
            if ($coin['price_change_percentage_24h'] >= 0) {
                $bullish++;
            } else {
                $bearish++;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CryptoPulse - Live Cryptocurrency Dashboard</title>

    <!-- AUTO REFRESH 30 DETIK -->
    <meta http-equiv="refresh" content="30">

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- Toast Notification Container -->
<div id="toastContainer" class="toast-container"></div>

<!-- Ambient Glowing Background Orbs -->
<div class="bg-orb orb-1"></div>
<div class="bg-orb orb-2"></div>

<div class="container">

    <!-- Header -->
    <header>
        <div class="header-title">
            <div class="logo-icon">
                <i class="fa-solid fa-cube"></i>
            </div>
            <div>
                <h1>CryptoPulse</h1>
                <div class="subtitle">
                    <span>Sumber: <strong style="color: var(--accent-glow);"><?= htmlspecialchars($dataSource); ?></strong></span>
                    
                    <?php if ($apiStatus === "online"): ?>
                        <span class="status-badge online" title="Sistem terhubung langsung ke server API CoinGecko">
                            <span class="pulse-dot"></span> 
                            <i class="fa-solid fa-cloud-bolt"></i> Terhubung (Live API)
                        </span>
                    <?php elseif ($apiStatus === "cached"): ?>
                        <span class="status-badge cached" title="Menggunakan cache lokal (Pembaruan tiap 60 detik)">
                            <span class="pulse-dot"></span> 
                            <i class="fa-solid fa-database"></i> Terhubung (Cache)
                        </span>
                    <?php else: ?>
                        <span class="status-badge error" title="<?= htmlspecialchars($errorMessage); ?>">
                            <span class="pulse-dot"></span> 
                            <i class="fa-solid fa-triangle-exclamation"></i> API Terputus
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <button onclick="location.reload()" class="btn-refresh" title="Segarkan Data Secara Manual">
            <i class="fa-solid fa-rotate-right"></i> Segarkan Data
        </button>
    </header>

    <!-- Alert Banner Ketika API Error / Terputus -->
    <?php if (strpos($apiStatus, 'error') !== false || $errorMessage !== "") : ?>
        <div class="api-alert-banner">
            <div class="api-alert-content">
                <div class="api-alert-icon">
                    <i class="fa-solid fa-wifi-slash"></i>
                </div>
                <div class="api-alert-text">
                    <h4>Koneksi API Mengalami Kendala <span style="font-size: 12px; font-weight: normal; background: rgba(244, 63, 94, 0.3); padding: 2px 8px; border-radius: 10px; color: #fff;">Status: Offline / Error</span></h4>
                    <p><?= htmlspecialchars($errorMessage !== "" ? $errorMessage : "Gagal mengambil data langsung dari API CoinGecko."); ?></p>
                    <div class="fallback-note">
                        <i class="fa-solid fa-shield-halved" style="color: var(--emerald);"></i>
                        <span>Sistem otomatis beralih ke <strong><?= htmlspecialchars($dataSource); ?></strong> agar aplikasi tetap berjalan.</span>
                    </div>
                </div>
            </div>
            <button onclick="location.reload()" class="btn-retry" title="Mencoba menghubungkan ulang ke server API">
                <i class="fa-solid fa-rotate-right"></i> Hubungkan Ulang
            </button>
        </div>
    <?php endif; ?>

    <!-- Error Banner Umum -->
    <?php if ($error != "") : ?>
        <div class="error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <div>
                <strong>Perhatian:</strong> <?= htmlspecialchars($error); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Top Summary Cards -->
    <div class="top-card">
        <div class="card card-1">
            <div class="card-content">
                <h3>Total Koin Teratas</h3>
                <p><?= $totalCoin ?> Aset</p>
            </div>
            <div class="card-icon">
                <i class="fa-solid fa-coins"></i>
            </div>
        </div>

        <div class="card card-2">
            <div class="card-content">
                <h3>Sentimen Pasar (24h)</h3>
                <p><?= $bullish ?> Naik / <?= $bearish ?> Turun</p>
            </div>
            <div class="card-icon">
                <i class="fa-solid fa-chart-line"></i>
            </div>
        </div>

        <div class="card card-3">
            <div class="card-content">
                <h3>Pembaruan Terakhir</h3>
                <p><?= date("H:i:s") ?></p>
            </div>
            <div class="card-icon">
                <i class="fa-solid fa-clock"></i>
            </div>
        </div>
    </div>

    <!-- Search / Controls Bar -->
    <div class="controls-bar">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Cari koin berdasarkan nama atau simbol...">
            <i class="fa-solid fa-magnifying-glass"></i>
        </div>
    </div>

    <!-- Table Content -->
    <?php if (!empty($cryptoData)) : ?>
        <div class="table-wrapper">
            <table id="cryptoTable">
                <thead>
                    <tr>
                        <th style="width: 70px; text-align: center;">#</th>
                        <th>Aset Kripto</th>
                        <th style="text-align: right;">Harga (USD)</th>
                        <th style="text-align: right;" class="mkt-cap-col">Kapitalisasi Pasar</th>
                        <th style="text-align: center; width: 180px;">Perubahan (24 Jam)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    <?php foreach ($cryptoData as $coin) : ?>
                        <?php 
                            $isUp = ($coin['price_change_percentage_24h'] >= 0); 
                        ?>
                        <tr>
                            <td style="text-align: center; color: var(--text-secondary); font-weight: 700;">
                                <?= $no++; ?>
                            </td>

                            <td>
                                <div class="coin-col">
                                    <img src="<?= htmlspecialchars($coin['image']); ?>" alt="<?= htmlspecialchars($coin['name']); ?>" class="coin-logo">
                                    <div>
                                        <span class="coin-name"><?= htmlspecialchars($coin['name']); ?></span>
                                        <span class="coin-symbol"><?= htmlspecialchars($coin['symbol']); ?></span>
                                    </div>
                                </div>
                            </td>

                            <td style="text-align: right;" class="price">
                                $<?= number_format($coin['current_price'], 2) ?>
                            </td>

                            <td style="text-align: right;" class="mkt-cap mkt-cap-col">
                                $<?= number_format($coin['market_cap']) ?>
                            </td>

                            <td style="text-align: center;">
                                <span class="badge-change <?= $isUp ? 'up' : 'down'; ?>">
                                    <i class="fa-solid <?= $isUp ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down'; ?>"></i>
                                    <?= number_format($coin['price_change_percentage_24h'], 2) ?>%
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div id="noResult" class="no-results" style="display: none;">
                <i class="fa-solid fa-box-open"></i>
                <p>Tidak ada aset mata uang kripto yang cocok dengan pencarian Anda.</p>
            </div>
        </div>
    <?php endif; ?>

</div>

<script>
// Toast Notification Function
function showToast(message, type = 'error', duration = 5000) {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    let icon = 'fa-circle-info';
    if (type === 'error') icon = 'fa-triangle-exclamation';
    else if (type === 'success') icon = 'fa-circle-check';
    else if (type === 'warning') icon = 'fa-circle-exclamation';

    toast.innerHTML = `
        <div class="toast-icon">
            <i class="fa-solid ${icon}"></i>
        </div>
        <div class="toast-content">
            <div class="toast-title">${type === 'error' ? 'Koneksi API Terganggu' : 'Pemberitahuan'}</div>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" aria-label="Tutup">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <div class="toast-progress" style="animation-duration: ${duration}ms"></div>
    `;

    // Close button event
    const closeBtn = toast.querySelector('.toast-close');
    closeBtn.addEventListener('click', () => {
        toast.classList.add('toast-fade-out');
        toast.addEventListener('animationend', () => {
            toast.remove();
        });
    });

    container.appendChild(toast);

    // Auto-remove after duration
    setTimeout(() => {
        if (toast.parentNode) {
            toast.classList.add('toast-fade-out');
            toast.addEventListener('animationend', () => {
                toast.remove();
            });
        }
    }, duration);
}

// Browser Push Notification Function
function showBrowserNotification(title, body) {
    if (!("Notification" in window)) return;
    
    if (Notification.permission === "granted") {
        new Notification(title, { 
            body: body, 
            icon: 'https://assets.coingecko.com/coins/images/1/large/bitcoin.png' 
        });
    } else if (Notification.permission !== "denied") {
        Notification.requestPermission().then(permission => {
            if (permission === "granted") {
                new Notification(title, { 
                    body: body, 
                    icon: 'https://assets.coingecko.com/coins/images/1/large/bitcoin.png' 
                });
            }
        });
    }
}

document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById("searchInput");
    const noResult = document.getElementById("noResult");
    const table = document.getElementById("cryptoTable");

    if (searchInput && table) {
        searchInput.addEventListener("keyup", function() {
            const filter = searchInput.value.toLowerCase();
            const rows = table.querySelectorAll("tbody tr");
            let hasVisibleRow = false;

            rows.forEach(row => {
                const coinName = row.querySelector(".coin-name").textContent.toLowerCase();
                const coinSymbol = row.querySelector(".coin-symbol").textContent.toLowerCase();

                if (coinName.includes(filter) || coinSymbol.includes(filter)) {
                    row.style.display = "";
                    hasVisibleRow = true;
                } else {
                    row.style.display = "none";
                }
            });

            // Show or hide empty state
            if (noResult) {
                noResult.style.display = hasVisibleRow ? "none" : "block";
                table.querySelector("thead").style.display = hasVisibleRow ? "" : "none";
            }
        });
    }

    // Check for API errors or general errors from PHP
    <?php
    $hasError = (strpos($apiStatus, 'error') !== false || $errorMessage !== "" || $error !== "");
    if ($hasError) {
        $jsErrorMessage = "";
        if ($error !== "") {
            $jsErrorMessage = "Kesalahan Sistem: " . $error;
        } elseif ($errorMessage !== "") {
            $jsErrorMessage = $errorMessage;
        } else {
            $jsErrorMessage = "Gagal mengambil data langsung dari API CoinGecko.";
        }
    ?>
        // Trigger Toast Notification on Page Load
        showToast(<?= json_encode($jsErrorMessage); ?>, 'error', 10000);
        
        // Trigger Browser Push Notification
        showBrowserNotification("CryptoPulse: Masalah Koneksi API", <?= json_encode($jsErrorMessage); ?>);
    <?php } ?>
});
</script>

</body>
</html>