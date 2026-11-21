<?php
// =========================================================================
// DISPLAY KETERSEDIAAN TEMPAT TIDUR RAWAT INAP - RSUD dr. MOHAMAD SALEH
// Versi PHP Native Standalone - Kompatibel Penuh untuk Smart TV Jadul
// =========================================================================

// Konfigurasi Server API SIMRS RSUD
$apiUrl = 'http://192.168.150.112:3501/api/v4/cektt/ranap';

// Inisialisasi variabel
$rawList = [];
$isError = false;
$errorMessage = '';
$lastUpdated = date('H:i:s');

// Ambil data dari API SIMRS
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 6);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($response !== false && $httpCode === 200) {
    $json = json_decode($response, true);
    if (isset($json['tempat_tidur']) && is_array($json['tempat_tidur'])) {
        $rawList = $json['tempat_tidur'];
    } else {
        $isError = true;
        $errorMessage = 'Format data dari server SIMRS tidak sesuai.';
    }
} else {
    // Coba fallback dengan file_get_contents bila curl gagal
    $ctx = stream_context_create(['http' => ['timeout' => 5]]);
    $altResponse = @file_get_contents($apiUrl, false, $ctx);
    if ($altResponse !== false) {
        $json = json_decode($altResponse, true);
        if (isset($json['tempat_tidur']) && is_array($json['tempat_tidur'])) {
            $rawList = $json['tempat_tidur'];
        } else {
            $isError = true;
            $errorMessage = 'Format data dari server SIMRS tidak sesuai.';
        }
    } else {
        $isError = true;
        $errorMessage = 'Gagal terhubung ke server SIMRS (' . $apiUrl . ')';
    }
}

// Normalisasi dan Pengurutan Ruangan
$dataRuang = [];
$totalBed = 0;
$totalTerisi = 0;
$totalKosong = 0;
$totalRusak = 0;
$kelasList = [];

foreach ($rawList as $item) {
    $namaRuang = preg_replace('/\s+/', ' ', trim($item['ruang'] ?? ''));
    if (preg_match('/RUANG\s+WIJAYA\s+KUSUMA\s+PRESIDENTIAL\s+SUITE/i', $namaRuang)) {
        $namaRuang = 'WK PRESIDENTIAL SUITE';
    }

    $total = intval($item['total'] ?? 0);
    $terisi = intval($item['terisi'] ?? 0);
    $kosong = intval($item['sisa'] ?? $item['kosong'] ?? 0);
    $rusak = intval($item['rusak'] ?? 0);
    $jenis = trim($item['jenis'] ?? $item['kelas'] ?? '-');

    $totalBed += $total;
    $totalTerisi += $terisi;
    $totalKosong += $kosong;
    $totalRusak += $rusak;

    if ($jenis !== '-' && !in_array($jenis, $kelasList)) {
        $kelasList[] = $jenis;
    }

    $persen = $total > 0 ? min(100, round(($terisi / $total) * 100)) : 0;

    $dataRuang[] = [
        'ruang' => $namaRuang,
        'jenis' => $jenis,
        'total' => $total,
        'terisi' => $terisi,
        'kosong' => $kosong,
        'rusak' => $rusak,
        'persen' => $persen
    ];
}

// Urutkan kelas: 1, 2, 3, HCU, ICCU, ICU, ISO, NICU, PS, VIP
sort($kelasList);

// Urutkan Ruangan secara alfabetis, mempertahankan kelompok Wijaya Kusuma
usort($dataRuang, function ($a, $b) {
    $keyA = strpos($a['ruang'], 'WK ') === 0 ? str_replace('WK ', 'RUANG WIJAYA KUSUMA ', $a['ruang']) : $a['ruang'];
    $keyB = strpos($b['ruang'], 'WK ') === 0 ? str_replace('WK ', 'RUANG WIJAYA KUSUMA ', $b['ruang']) : $b['ruang'];
    return strcasecmp($keyA, $keyB);
});

// Hitung persentase BOR
$borPersen = $totalBed > 0 ? round(($totalTerisi / $totalBed) * 100) : 0;

// =========================================================================
// ENDPOINT AJAX UNTUK UPDATE DATA SENYAP (TANPA RELOAD / FULLSCREEN TIDAK LEPAS)
// =========================================================================
if (isset($_GET['action']) && $_GET['action'] === 'data') {
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    echo json_encode([
        'success' => !$isError,
        'errorMessage' => $errorMessage,
        'totalBed' => $totalBed,
        'totalTerisi' => $totalTerisi,
        'totalKosong' => $totalKosong,
        'borPersen' => $borPersen,
        'lastUpdated' => date('H:i:s'),
        'dataRuang' => $dataRuang
    ]);
    exit;
}

// Format Tanggal Indonesia
$hariList = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$bulanList = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$namaHari = $hariList[date('w')];
$tglFormatted = $namaHari . ', ' . date('j') . ' ' . $bulanList[date('n')] . ' ' . date('Y');

// Logo RSUD Base64 embedded jika berkas ada, atau path relatif
$logoSrc = 'assets/images/logo-rsud.png';
if (file_exists(__DIR__ . '/assets/images/logo-rsud.png')) {
    $logoData = base64_encode(file_get_contents(__DIR__ . '/assets/images/logo-rsud.png'));
    $logoSrc = 'data:image/png;base64,' . $logoData;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Display Ketersediaan Tempat Tidur - RSUD dr. Mohamad Saleh</title>
  
  <style>
    /* ==========================================================================
       RESET & BASE THEME (HITAM SOLID & TEAL FUTURISTIK)
       ========================================================================== */
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      -webkit-tap-highlight-color: transparent;
    }
    
    html, body {
      width: 100%;
      height: 100%;
      overflow: hidden;
      background: #000000;
      color: #ffffff;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
      font-size: 14px;
      line-height: 1.3;
    }

    .font-mono {
      font-family: "Courier New", Courier, monospace, monospace;
    }

    /* Container Layar Utama */
    #display-app {
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      width: 100vw;
      height: 100vh;
      overflow: hidden;
      background: #000000;
    }

    /* ==========================================================================
       1. HEADER DISPLAY (TEAL FUTURISTIK SOLID)
       ========================================================================== */
    .tt-header-container {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 8px 16px;
      background: #102d38;
      background: -webkit-linear-gradient(left, #0e2730 0%, #153744 50%, #0e2730 100%);
      background: linear-gradient(90deg, #0e2730 0%, #153744 50%, #0e2730 100%);
      border-bottom: 1px solid rgba(0, 229, 255, 0.25);
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.7);
      flex-shrink: 0;
      position: relative;
      z-index: 100;
      width: 100%;
    }

    .header-left {
      display: flex;
      align-items: center;
      min-width: 0;
    }

    .tt-logo {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      border: 2px solid #00e5ff;
      background: #ffffff;
      margin-right: 10px;
      flex-shrink: 0;
    }

    .header-title-box {
      display: flex;
      flex-direction: column;
      justify-content: center;
      overflow: hidden;
    }

    .title-row {
      display: flex;
      align-items: center;
      white-space: nowrap;
    }

    .hospital-title {
      font-size: 15px;
      font-weight: 700;
      color: #ffffff;
      letter-spacing: 0.5px;
    }

    .badge-igd {
      background: #c62828;
      color: #ffffff;
      font-size: 10px;
      font-weight: 800;
      padding: 1px 5px;
      border-radius: 4px;
      margin-left: 6px;
      letter-spacing: 0.5px;
    }

    .hospital-subtitle {
      font-size: 11px;
      color: #80deea;
      margin-top: 2px;
      white-space: nowrap;
    }

    .header-right {
      display: flex;
      align-items: center;
      flex-shrink: 0;
    }

    .header-btn {
      background: rgba(0, 229, 255, 0.12);
      border: 1px solid rgba(0, 229, 255, 0.3);
      color: #00e5ff;
      border-radius: 20px;
      padding: 5px 10px;
      margin-left: 8px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      font-size: 12px;
      font-weight: 700;
      outline: none;
      transition: all 0.2s;
    }

    .header-btn:hover {
      background: rgba(0, 229, 255, 0.25);
      border-color: #00e5ff;
    }

    .timer-badge-btn {
      background: rgba(0, 229, 255, 0.18);
      border: 1.5px solid #00e5ff;
      color: #e0f7fa;
      padding: 5px 12px;
    }

    .header-divider {
      width: 1px;
      height: 26px;
      background: rgba(0, 229, 255, 0.25);
      margin: 0 12px;
    }

    /* Jam Digital */
    .digital-clock-box {
      text-align: right;
      min-width: 140px;
    }

    .clock-digits {
      font-size: 22px;
      font-weight: 800;
      color: #80deea;
      line-height: 1;
    }

    .clock-colon {
      color: #26c6da;
      margin: 0 1px;
      animation: blink 1s infinite;
    }

    .clock-seconds {
      font-size: 17px;
      color: #26c6da;
    }

    .clock-date {
      font-size: 11px;
      color: #b0bec5;
      margin-top: 2px;
    }

    @keyframes blink {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.2; }
    }

    /* Popover Menu Durasi Timer */
    #timer-popover {
      display: none;
      position: absolute;
      top: 54px;
      right: 170px;
      background: #0d2732;
      border: 2px solid #00e5ff;
      border-radius: 14px;
      padding: 14px;
      width: 320px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.8), 0 0 20px rgba(0, 229, 255, 0.4);
      z-index: 500;
    }

    .popover-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 8px;
    }

    .popover-header span {
      font-size: 12px;
      font-weight: 700;
      color: #ffffff;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .popover-badge {
      background: #00897b;
      color: #ffffff;
      padding: 2px 8px;
      border-radius: 10px;
      font-size: 11px;
      font-weight: 700;
    }

    .popover-grid {
      display: flex;
      flex-wrap: wrap;
      margin: -3px;
    }

    .popover-opt-btn {
      flex: 0 0 calc(33.333% - 6px);
      margin: 3px;
      padding: 6px 0;
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(0, 229, 255, 0.2);
      color: #b2ebf2;
      border-radius: 6px;
      font-size: 11px;
      font-weight: 700;
      cursor: pointer;
      text-align: center;
      outline: none;
    }

    .popover-opt-btn.active {
      background: #00838f;
      color: #ffffff;
      border: 1.5px solid #00e5ff;
      box-shadow: 0 0 10px rgba(0, 229, 255, 0.5);
    }

    /* ==========================================================================
       2. KPI STATS SUMMARY (4 KARTU RINGKAS ATAS)
       ========================================================================== */
    .tt-stats-grid {
      display: flex;
      padding: 8px 16px 4px 16px;
      gap: 12px;
      flex-shrink: 0;
    }

    .stat-card {
      flex: 1;
      background: #13313d;
      border-radius: 12px;
      border: 1px solid rgba(0, 229, 255, 0.25);
      padding: 8px 12px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.4);
    }

    .border-cyan { border-left: 5px solid #00e5ff; }
    .border-red { border-left: 5px solid #ff5252; }
    .border-emerald {
      border-left: 5px solid #00e676;
      animation: pulse-border 3s infinite;
    }
    .border-amber { border-left: 5px solid #ffd600; }

    @keyframes pulse-border {
      0%, 100% { box-shadow: 0 0 10px rgba(0, 230, 118, 0.2); }
      50% { box-shadow: 0 0 20px rgba(0, 230, 118, 0.5); }
    }

    .stat-title {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .color-cyan-text { color: #80deea; }
    .color-red-text { color: #ff8a80; }
    .color-green-text { color: #b9f6ca; }
    .color-amber-text { color: #ffe57f; }

    .stat-num {
      font-size: 22px;
      font-weight: 800;
      line-height: 1.1;
      margin-top: 2px;
    }
    .stat-num-white { color: #ffffff; }
    .stat-num-red { color: #ff8a80; }
    .stat-num-green { color: #69f0ae; }
    .stat-num-amber { color: #ffd740; }

    .stat-sub {
      font-size: 11px;
      font-weight: 400;
      opacity: 0.8;
      margin-left: 2px;
    }

    .stat-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
    }
    .bg-icon-cyan { background: #006064; color: #80deea; }
    .bg-icon-red { background: #b71c1c; color: #ffcdd2; }
    .bg-icon-green { background: #1b5e20; color: #b9f6ca; }
    .bg-icon-amber { background: #f57f17; color: #fff9c4; }

    /* ==========================================================================
       3. FILTER KELAS TABS (BARISAN GLASS TILE)
       ========================================================================== */
    .filter-section {
      padding: 4px 16px;
      flex-shrink: 0;
    }

    .filter-top-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 6px;
    }

    .filter-heading {
      font-size: 11px;
      font-weight: 700;
      color: #80deea;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .filter-count {
      color: #90a4ae;
      font-size: 11px;
      margin-left: 4px;
    }

    .rotation-status-badge {
      background: #004d40;
      color: #80cbc4;
      font-size: 10px;
      font-weight: 700;
      padding: 2px 8px;
      border-radius: 10px;
      cursor: pointer;
      display: flex;
      align-items: center;
    }

    .filter-tabs-row {
      display: flex;
      align-items: center;
      overflow-x: auto;
      white-space: nowrap;
      gap: 6px;
      padding-bottom: 4px;
    }

    .glass-tile {
      background: #143542;
      border: 1px solid rgba(0, 229, 255, 0.25);
      border-radius: 8px;
      color: #e0f7fa;
      padding: 5px 12px;
      font-size: 11px;
      font-weight: 700;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      user-select: none;
      transition: all 0.2s;
    }

    .glass-tile:hover {
      background: #1a4455;
      border-color: #00e5ff;
      color: #ffffff;
    }

    .glass-tile.active {
      background: #00838f !important;
      border-color: #00e5ff !important;
      color: #ffffff !important;
      box-shadow: 0 0 10px rgba(0, 229, 255, 0.4);
    }

    /* ==========================================================================
       4. AREA UTAMA KARTU RUANG RAWAT INAP
       ========================================================================== */
    .rooms-main-area {
      flex: 1;
      overflow-y: auto;
      padding: 4px 16px 12px 16px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .rooms-flex-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      align-items: stretch;
      gap: 14px;
      width: 100%;
      margin: auto 0;
    }

    /* Mode Kartu Normal (Mode Semua - 23 Ruang) */
    .room-card-wrapper {
      margin: 4px;
      transition: all 0.25s ease;
    }

    .size-normal {
      flex: 0 1 calc(20% - 12px);
      min-width: 240px;
      max-width: 320px;
    }

    /* Mode Kartu Sedang (4 - 6 Ruang) */
    .size-medium {
      flex: 0 1 calc(33.333% - 14px);
      min-width: 340px;
      max-width: 460px;
    }

    /* Mode Kartu Besar (1 - 3 Ruang) */
    .size-large {
      flex: 0 1 500px;
      min-width: 380px;
      max-width: 580px;
    }

    /* Kartu Ruangan Dasar */
    .room-card {
      background: #122e3a;
      border: 1px solid rgba(0, 229, 255, 0.25);
      border-radius: 12px;
      padding: 10px 12px;
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.4);
      position: relative;
      overflow: hidden;
    }

    .room-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 6px;
    }

    .room-name {
      font-size: 13px;
      font-weight: 700;
      color: #ffffff;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 70%;
    }

    .room-chip-kelas {
      background: #006064;
      color: #e0f7fa;
      font-size: 10px;
      font-weight: 700;
      padding: 2px 7px;
      border-radius: 12px;
      border: 1px solid rgba(0, 229, 255, 0.3);
      flex-shrink: 0;
    }

    /* Progress Bar */
    .progress-box {
      margin: 4px 0 8px 0;
    }

    .progress-label-row {
      display: flex;
      justify-content: space-between;
      font-size: 10px;
      color: #cfd8dc;
      margin-bottom: 3px;
    }

    .progress-track {
      width: 100%;
      height: 6px;
      background: #263238;
      border-radius: 4px;
      overflow: hidden;
    }

    .progress-bar-fill {
      height: 100%;
      border-radius: 4px;
      transition: width 0.3s;
    }
    .fill-green { background: #00e676; }
    .fill-amber { background: #ffd600; }
    .fill-red { background: #ff5252; }

    /* Baris Chip Status Tempat Tidur (Mode Normal) */
    .chips-status-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 4px;
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      padding-top: 6px;
      margin-top: 4px;
    }

    .status-chip {
      font-size: 10px;
      font-weight: 700;
      padding: 3px 6px;
      border-radius: 6px;
      display: inline-flex;
      align-items: center;
      white-space: nowrap;
    }
    .chip-total { background: #006064; color: #ffffff; }
    .chip-terisi { background: #d32f2f; color: #ffffff; }
    .chip-kosong-active {
      background: #00c853;
      color: #000000;
      box-shadow: 0 0 8px rgba(0, 230, 118, 0.5);
    }
    .chip-kosong-zero { background: #37474f; color: #90a4ae; }

    /* Switch Tampilan Antara Mode Normal dan Mode Besar via CSS murni */
    .size-normal .big-stats-grid { display: none; }
    .size-normal .chips-status-row { display: flex; }

    .size-medium .big-stats-grid,
    .size-large .big-stats-grid { display: flex; }
    .size-medium .chips-status-row,
    .size-large .chips-status-row { display: none; }

    .size-medium .room-card,
    .size-large .room-card {
      padding: 16px;
      border-radius: 16px;
      border: 1.5px solid rgba(0, 229, 255, 0.35);
      min-height: 210px;
    }

    .size-large .room-card {
      min-height: 240px;
      border: 2px solid rgba(0, 229, 255, 0.45);
    }

    .size-medium .room-name,
    .size-large .room-name {
      font-size: 16px;
      text-shadow: 0 0 10px rgba(0, 229, 255, 0.4);
    }

    .size-large .room-name {
      font-size: 18px;
    }

    .big-stats-grid {
      display: flex;
      gap: 10px;
      margin-top: 14px;
    }

    .big-stat-box {
      flex: 1;
      border-radius: 10px;
      padding: 10px 8px;
      text-align: center;
    }

    .box-total {
      background: rgba(0, 188, 212, 0.15);
      border: 1px solid rgba(0, 229, 255, 0.35);
    }
    .box-terisi {
      background: rgba(244, 67, 54, 0.18);
      border: 1px solid rgba(244, 67, 54, 0.4);
    }
    .box-kosong {
      background: rgba(0, 230, 118, 0.2);
      border: 1.5px solid #00e676;
      box-shadow: 0 0 14px rgba(0, 230, 118, 0.4);
    }

    .big-stat-label {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .big-stat-value {
      font-size: 32px;
      font-weight: 800;
      margin-top: 4px;
      line-height: 1;
    }

    /* ==========================================================================
       5. FOOTER RUNNING TEXT TICKER
       ========================================================================== */
    .tt-footer-ticker {
      height: 36px;
      background: #000000;
      border-top: 2px solid #16202c;
      box-shadow: 0 -4px 15px rgba(0, 0, 0, 0.8);
      display: flex;
      align-items: center;
      padding: 0 16px;
      flex-shrink: 0;
      position: relative;
      z-index: 90;
    }

    .badge-info-igd {
      background: #004d40;
      color: #ffffff;
      font-size: 11px;
      font-weight: 800;
      padding: 3px 8px;
      border-radius: 4px;
      margin-right: 14px;
      flex-shrink: 0;
      letter-spacing: 0.5px;
    }

    .ticker-wrapper {
      position: relative;
      overflow: hidden;
      white-space: nowrap;
      flex: 1;
    }

    .ticker-content {
      display: inline-block;
      white-space: nowrap;
      padding-left: 100%;
      font-size: 12px;
      color: #80deea;
      animation: ticker-slide 35s linear infinite;
    }

    @keyframes ticker-slide {
      0% { transform: translate3d(0, 0, 0); }
      100% { transform: translate3d(-100%, 0, 0); }
    }

    /* Fallback jika TV lama tidak support CSS Grid / Flex gap */
    .q-mr-xs { margin-right: 4px; }
    .q-ml-xs { margin-left: 4px; }
  </style>
</head>
<body>

<div id="display-app">
  <!-- ========================================================================
       1. HEADER DISPLAY
       ======================================================================== -->
  <div class="tt-header-container">
    <div class="header-left">
      <img src="<?php echo $logoSrc; ?>" alt="Logo RSUD" class="tt-logo">
      <div class="header-title-box">
        <div class="title-row">
          <span class="hospital-title">UOBK RSUD dr. MOHAMAD SALEH</span>
          <span class="badge-igd">IGD</span>
        </div>
        <div class="hospital-subtitle">Display Ketersediaan Tempat Tidur Rawat Inap</div>
      </div>
    </div>

    <div class="header-right">
      <!-- Tombol Koneksi Wifi Status (Sync AJAX Senyap) -->
      <button class="header-btn" title="Status Koneksi SIMRS (Klik untuk Sync)" onclick="fetchDataAjax();">
        <span id="status-wifi-icon" style="font-size: 14px; margin-right: 4px;"><?php echo $isError ? '⚠️' : '📶'; ?></span>
        <span id="status-wifi-text"><?php echo $isError ? 'Offline' : 'Online'; ?></span>
      </button>

      <!-- Tombol Refresh Data (AJAX di Latar Belakang Tanpa Me-reload Layar) -->
      <button class="header-btn" title="Refresh Data Sekarang" onclick="fetchDataAjax();">
        <span id="btn-refresh-icon" style="font-size: 14px; margin-right: 4px;">🔄</span>
        <span>Refresh</span>
      </button>

      <!-- Tombol Timer Durasi Rotasi -->
      <button class="header-btn timer-badge-btn" id="btn-toggle-timer" onclick="toggleTimerMenu();">
        <span style="font-size: 14px; margin-right: 4px;">⏱</span>
        <span id="label-timer-durasi">12s</span>
      </button>

      <!-- Tombol Fullscreen -->
      <button class="header-btn" title="Layar Penuh" onclick="toggleFullscreen();">
        <span style="font-size: 14px;">⛶</span>
      </button>

      <div class="header-divider"></div>

      <!-- Jam Digital Realtime -->
      <div class="digital-clock-box">
        <div class="clock-digits font-mono">
          <span id="clk-jam"><?php echo date('H'); ?></span>
          <span class="clock-colon">:</span>
          <span id="clk-menit"><?php echo date('i'); ?></span>
          <span class="clock-colon">:</span>
          <span id="clk-detik" class="clock-seconds"><?php echo date('s'); ?></span>
        </div>
        <div class="clock-date" id="clk-tanggal"><?php echo $tglFormatted; ?></div>
      </div>
    </div>
  </div>

  <!-- Popover Pengaturan Durasi Timer -->
  <div id="timer-popover">
    <div class="popover-header">
      <span>Durasi Rotasi Tab</span>
      <span class="popover-badge" id="badge-popover-seconds">12 Detik</span>
    </div>
    <div style="font-size: 11px; color: #80deea; margin-bottom: 8px;">
      Pilih interval waktu perpindahan otomatis antar kelas:
    </div>
    <div class="popover-grid">
      <button class="popover-opt-btn" onclick="setCycleDuration(5)">5 Detik</button>
      <button class="popover-opt-btn" onclick="setCycleDuration(8)">8 Detik</button>
      <button class="popover-opt-btn" onclick="setCycleDuration(10)">10 Detik</button>
      <button class="popover-opt-btn active" id="btn-opt-12" onclick="setCycleDuration(12)">12s (Default)</button>
      <button class="popover-opt-btn" onclick="setCycleDuration(15)">15 Detik</button>
      <button class="popover-opt-btn" onclick="setCycleDuration(20)">20 Detik</button>
      <button class="popover-opt-btn" onclick="setCycleDuration(30)">30 Detik</button>
    </div>
  </div>

  <!-- ========================================================================
       2. KPI STATS SUMMARY
       ======================================================================== -->
  <div class="tt-stats-grid">
    <!-- Total Kapasitas -->
    <div class="stat-card border-cyan">
      <div>
        <div class="stat-title color-cyan-text">Total Kapasitas</div>
        <div class="stat-num stat-num-white font-mono">
          <span id="kpi-total-bed"><?php echo $totalBed; ?></span>
          <span class="stat-sub color-cyan-text">Bed</span>
        </div>
      </div>
      <div class="stat-icon bg-icon-cyan">🏥</div>
    </div>

    <!-- Bed Terisi -->
    <div class="stat-card border-red">
      <div>
        <div class="stat-title color-red-text">Bed Terisi (Pasien)</div>
        <div class="stat-num stat-num-red font-mono">
          <span id="kpi-terisi-bed"><?php echo $totalTerisi; ?></span>
          <span class="stat-sub color-red-text">Bed</span>
        </div>
      </div>
      <div class="stat-icon bg-icon-red">🛏️</div>
    </div>

    <!-- Bed Kosong (Siap Pakai) -->
    <div class="stat-card border-emerald">
      <div>
        <div class="stat-title color-green-text">Bed Kosong (Tersedia)</div>
        <div class="stat-num stat-num-green font-mono">
          <span id="kpi-kosong-bed"><?php echo $totalKosong; ?></span>
          <span class="stat-sub color-green-text">Siap Pakai</span>
        </div>
      </div>
      <div class="stat-icon bg-icon-green">✨</div>
    </div>

    <!-- BOR -->
    <div class="stat-card border-amber">
      <div>
        <div class="stat-title color-amber-text">Tingkat Hunian (BOR)</div>
        <div class="stat-num stat-num-amber font-mono">
          <span id="kpi-bor-bed"><?php echo $borPersen; ?>%</span>
          <span class="stat-sub color-amber-text">Terpakai</span>
        </div>
      </div>
      <div class="stat-icon bg-icon-amber">📊</div>
    </div>
  </div>

  <!-- ========================================================================
       3. FILTER KELAS TABS
       ======================================================================== -->
  <div class="filter-section">
    <div class="filter-top-bar">
      <div>
        <span class="filter-heading">Filter Kelas</span>
        <span class="filter-count" id="filter-room-count">(<?php echo count($dataRuang); ?> Ruang)</span>
      </div>
      <div class="rotation-status-badge" id="btn-toggle-rotation" onclick="toggleAutoRotation();">
        <span id="rotation-icon" style="margin-right: 4px;">🔄</span>
        <span id="rotation-text">Rotasi Aktif</span>
      </div>
    </div>

    <div class="filter-tabs-row" id="tabs-container">
      <div class="glass-tile active" data-kelas="semua" onclick="changeKelas('semua');">
        <span style="margin-right: 5px;">📋</span> Semua
      </div>
      <?php foreach ($kelasList as $k): ?>
        <div class="glass-tile" data-kelas="<?php echo htmlspecialchars($k); ?>" onclick="changeKelas('<?php echo htmlspecialchars($k); ?>');">
          <span style="margin-right: 5px;">🏷️</span> Kelas <?php echo htmlspecialchars($k); ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- ========================================================================
       4. AREA UTAMA KARTU RUANG RAWAT INAP
       ======================================================================== -->
  <div class="rooms-main-area">
    <div class="rooms-flex-container" id="rooms-container">
      <?php foreach ($dataRuang as $index => $r): ?>
        <?php
          $fillColor = $r['persen'] >= 90 ? 'fill-red' : ($r['persen'] >= 70 ? 'fill-amber' : 'fill-green');
          $kosongClass = $r['kosong'] > 0 ? 'chip-kosong-active' : 'chip-kosong-zero';
        ?>
        <div class="room-card-wrapper size-normal" data-kelas="<?php echo htmlspecialchars($r['jenis']); ?>" data-ruang="<?php echo htmlspecialchars($r['ruang']); ?>">
          <div class="room-card">
            <!-- Header Kartu -->
            <div>
              <div class="room-header">
                <div class="room-name" title="<?php echo htmlspecialchars($r['ruang']); ?>">
                  <?php echo htmlspecialchars($r['ruang']); ?>
                </div>
                <div class="room-chip-kelas">
                  Kelas: <?php echo htmlspecialchars($r['jenis']); ?>
                </div>
              </div>

              <!-- Progress Bar Keterisian -->
              <div class="progress-box">
                <div class="progress-label-row">
                  <span class="label-keterisian-text">Keterisian (<?php echo $r['terisi']; ?> / <?php echo $r['total']; ?> Bed)</span>
                  <span class="font-mono label-persen-text" style="font-weight: 700; color: <?php echo $r['persen'] >= 90 ? '#ff8a80' : '#80deea'; ?>;">
                    <?php echo $r['persen']; ?>%
                  </span>
                </div>
                <div class="progress-track">
                  <div class="progress-bar-fill <?php echo $fillColor; ?>" style="width: <?php echo $r['persen']; ?>%;"></div>
                </div>
              </div>
            </div>

            <!-- Chips Status (Mode Normal / Tab Semua) -->
            <div class="chips-status-row">
              <span class="status-chip chip-total chip-total-text">Total: <?php echo $r['total']; ?></span>
              <span class="status-chip chip-terisi chip-terisi-text">Terisi: <?php echo $r['terisi']; ?></span>
              <span class="status-chip chip-kosong-chip <?php echo $kosongClass; ?>">Kosong: <?php echo $r['kosong']; ?></span>
            </div>

            <!-- Big Stat Boxes (Mode Sedang & Besar / Tab Kelas Tertentu) -->
            <div class="big-stats-grid">
              <div class="big-stat-box box-total">
                <div class="big-stat-label color-cyan-text">Total Bed</div>
                <div class="big-stat-value font-mono color-cyan-text big-box-total"><?php echo $r['total']; ?></div>
              </div>
              <div class="big-stat-box box-terisi">
                <div class="big-stat-label color-red-text">Terisi</div>
                <div class="big-stat-value font-mono color-red-text big-box-terisi"><?php echo $r['terisi']; ?></div>
              </div>
              <div class="big-stat-box box-kosong">
                <div class="big-stat-label color-green-text">Kosong</div>
                <div class="big-stat-value font-mono color-green-text big-box-kosong"><?php echo $r['kosong']; ?></div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Banner Petunjuk Fullscreen Otomatis -->
  <div id="fullscreen-prompt" onclick="enterFullscreen();" style="position: fixed; bottom: 44px; left: 50%; transform: translateX(-50%); background: rgba(0, 77, 64, 0.95); border: 2px solid #00e5ff; border-radius: 30px; padding: 7px 22px; color: #ffffff; font-weight: bold; font-size: 13px; z-index: 9999; cursor: pointer; box-shadow: 0 4px 20px rgba(0, 229, 255, 0.5); display: flex; align-items: center;">
    <span style="font-size: 18px; margin-right: 8px;">📺</span>
    <span>Sentuh Layar atau Tekan OK di Remote untuk Layar Penuh</span>
  </div>

  <!-- ========================================================================
       5. FOOTER RUNNING TEXT TICKER
       ======================================================================== -->
  <div class="tt-footer-ticker">
    <div class="badge-info-igd">INFO IGD</div>
    <div class="ticker-wrapper">
      <div class="ticker-content">
        Selamat Datang di UOBK RSUD dr. Mohamad Saleh Kota Probolinggo • Informasi Ketersediaan Tempat Tidur Rawat Inap diperbarui secara otomatis secara realtime • Untuk informasi lebih lanjut mengenai alur rawat inap dari IGD, silakan hubungi petugas admisi IGD • 
        <span id="ticker-last-update" style="color: #ffd740; font-weight: bold; margin-left: 6px;">
          [Terakhir Diperbarui: <?php echo $lastUpdated; ?> WIB]
        </span>
      </div>
    </div>
  </div>
</div>

<!-- ==========================================================================
     JAVASCRIPT MURNI ES3 / ES5 (NON-MODULE - JAMINAN BISA DI SMART TV LAMA)
     ========================================================================== -->
<script type="text/javascript">
(function() {
  // Variabel Global Runtime
  var activeKelas = 'semua';
  var cycleDuration = 12; // detik
  var isAutoRotate = true;
  var rotateTimer = null;
  var refreshTimer = null;
  var clockTimer = null;

  // Daftar Semua Kelas
  var kelasList = ['semua'];
  <?php foreach ($kelasList as $k): ?>
    kelasList.push('<?php echo addslashes($k); ?>');
  <?php endforeach; ?>

  // Baca pengaturan tersimpan dari localStorage secara aman (try-catch)
  try {
    if (window.localStorage) {
      var savedSec = parseInt(window.localStorage.getItem('display_tt_cycle_seconds'), 10);
      if (savedSec && savedSec > 0) {
        cycleDuration = savedSec;
      }
    }
  } catch(e) {}

  // Update UI tombol timer sesuai cycleDuration
  function updateTimerButtonUI() {
    var lbl = document.getElementById('label-timer-durasi');
    var badge = document.getElementById('badge-popover-seconds');
    if (lbl) lbl.innerHTML = cycleDuration + 's';
    if (badge) badge.innerHTML = cycleDuration + ' Detik';

    // Update active class di popover
    var btns = document.getElementsByClassName('popover-opt-btn');
    for (var i = 0; i < btns.length; i++) {
      btns[i].className = btns[i].className.replace(' active', '');
      if (btns[i].innerHTML.indexOf(cycleDuration + '') === 0 || 
         (cycleDuration === 12 && btns[i].id === 'btn-opt-12')) {
        btns[i].className += ' active';
      }
    }
  }

  // Jam Digital & Tanggal Realtime (Otomatis Berubah Hari Tanpa Reload)
  var hariNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
  var bulanNames = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

  function updateClock() {
    var d = new Date();
    var h = d.getHours();
    var m = d.getMinutes();
    var s = d.getSeconds();

    var hStr = (h < 10 ? '0' : '') + h;
    var mStr = (m < 10 ? '0' : '') + m;
    var sStr = (s < 10 ? '0' : '') + s;

    var elH = document.getElementById('clk-jam');
    var elM = document.getElementById('clk-menit');
    var elS = document.getElementById('clk-detik');

    if (elH) elH.innerHTML = hStr;
    if (elM) elM.innerHTML = mStr;
    if (elS) elS.innerHTML = sStr;

    // Perbarui Nama Hari dan Tanggal secara Otomatis saat Melewati Tengah Malam (00:00:00)
    var elTgl = document.getElementById('clk-tanggal');
    if (elTgl) {
      var hari = hariNames[d.getDay()];
      var tgl = d.getDate();
      var bln = bulanNames[d.getMonth() + 1];
      var thn = d.getFullYear();
      var tglStr = hari + ', ' + tgl + ' ' + bln + ' ' + thn;
      if (elTgl.innerHTML !== tglStr) {
        elTgl.innerHTML = tglStr;
      }
    }
  }

  // Fungsi Filter & Ganti Kelas
  window.changeKelas = function(targetKelas) {
    activeKelas = targetKelas;

    // 1. Update status active pada tab
    var tabs = document.getElementsByClassName('glass-tile');
    for (var i = 0; i < tabs.length; i++) {
      var tabKelas = tabs[i].getAttribute('data-kelas');
      if (tabKelas === targetKelas) {
        tabs[i].className = 'glass-tile active';
        // Auto scroll ke tab yang aktif jika overflow
        try {
          if (tabs[i].scrollIntoView) {
            tabs[i].scrollIntoView(false);
          }
        } catch(e) {}
      } else {
        tabs[i].className = 'glass-tile';
      }
    }

    // 2. Filter kartu ruangan
    var wrappers = document.getElementsByClassName('room-card-wrapper');
    var visibleCount = 0;

    for (var j = 0; j < wrappers.length; j++) {
      var cardKelas = wrappers[j].getAttribute('data-kelas');
      var isVisible = (targetKelas === 'semua' || cardKelas === targetKelas);

      if (isVisible) {
        wrappers[j].style.display = '';
        visibleCount++;
      } else {
        wrappers[j].style.display = 'none';
      }
    }

    // 3. Update counter ruangan di heading
    var cntEl = document.getElementById('filter-room-count');
    if (cntEl) {
      cntEl.innerHTML = '(' + visibleCount + ' Ruang)';
    }

    // 4. Ubah ukuran kartu secara proporsional sesuai jumlah data
    var sizeClass = 'size-normal';
    if (visibleCount <= 3) {
      sizeClass = 'size-large';
    } else if (visibleCount <= 6) {
      sizeClass = 'size-medium';
    }

    for (var k = 0; k < wrappers.length; k++) {
      wrappers[k].className = 'room-card-wrapper ' + sizeClass;
    }
  };

  // Update Data Dashboard secara Realtime di Latar Belakang (AJAX Tanpa Reload)
  function updateDashboardData(res) {
    if (!res) return;

    // 1. Update KPI Card Atas
    var elTotal = document.getElementById('kpi-total-bed');
    var elTerisi = document.getElementById('kpi-terisi-bed');
    var elKosong = document.getElementById('kpi-kosong-bed');
    var elBor = document.getElementById('kpi-bor-bed');

    if (elTotal && res.totalBed !== undefined) elTotal.innerHTML = res.totalBed;
    if (elTerisi && res.totalTerisi !== undefined) elTerisi.innerHTML = res.totalTerisi;
    if (elKosong && res.totalKosong !== undefined) elKosong.innerHTML = res.totalKosong;
    if (elBor && res.borPersen !== undefined) elBor.innerHTML = res.borPersen + '%';

    // 2. Update Ticker Last Update
    var elTicker = document.getElementById('ticker-last-update');
    if (elTicker && res.lastUpdated) {
      elTicker.innerHTML = '[Terakhir Diperbarui: ' + res.lastUpdated + ' WIB]';
    }

    // 3. Update Setiap Kartu Ruangan Berdasarkan data-ruang
    if (res.dataRuang && res.dataRuang.length) {
      for (var i = 0; i < res.dataRuang.length; i++) {
        var item = res.dataRuang[i];
        var wrappers = document.getElementsByClassName('room-card-wrapper');

        for (var j = 0; j < wrappers.length; j++) {
          var cardRuang = wrappers[j].getAttribute('data-ruang');
          if (cardRuang === item.ruang) {
            // Update Teks Keterisian
            var elKet = wrappers[j].querySelector('.label-keterisian-text');
            if (elKet) elKet.innerHTML = 'Keterisian (' + item.terisi + ' / ' + item.total + ' Bed)';

            // Update Persen & Warna
            var elPrs = wrappers[j].querySelector('.label-persen-text');
            if (elPrs) {
              elPrs.innerHTML = item.persen + '%';
              elPrs.style.color = item.persen >= 90 ? '#ff8a80' : '#80deea';
            }

            // Update Progress Bar Fill
            var elBar = wrappers[j].querySelector('.progress-bar-fill');
            if (elBar) {
              elBar.style.width = item.persen + '%';
              var fillColor = item.persen >= 90 ? 'fill-red' : (item.persen >= 70 ? 'fill-amber' : 'fill-green');
              elBar.className = 'progress-bar-fill ' + fillColor;
            }

            // Update Chip Mode Normal
            var elChipTotal = wrappers[j].querySelector('.chip-total-text');
            if (elChipTotal) elChipTotal.innerHTML = 'Total: ' + item.total;

            var elChipTerisi = wrappers[j].querySelector('.chip-terisi-text');
            if (elChipTerisi) elChipTerisi.innerHTML = 'Terisi: ' + item.terisi;

            var elChipKosong = wrappers[j].querySelector('.chip-kosong-chip');
            if (elChipKosong) {
              elChipKosong.innerHTML = 'Kosong: ' + item.kosong;
              elChipKosong.className = 'status-chip chip-kosong-chip ' + (item.kosong > 0 ? 'chip-kosong-active' : 'chip-kosong-zero');
            }

            // Update Big Stat Boxes Mode Besar
            var elBigTotal = wrappers[j].querySelector('.big-box-total');
            if (elBigTotal) elBigTotal.innerHTML = item.total;

            var elBigTerisi = wrappers[j].querySelector('.big-box-terisi');
            if (elBigTerisi) elBigTerisi.innerHTML = item.terisi;

            var elBigKosong = wrappers[j].querySelector('.big-box-kosong');
            if (elBigKosong) elBigKosong.innerHTML = item.kosong;

            break;
          }
        }
      }
    }
  }

  // Fungsi Fetch Data dari Server via AJAX Tanpa Mereload Halaman
  window.fetchDataAjax = function() {
    try {
      var icon = document.getElementById('btn-refresh-icon');
      if (icon) icon.style.opacity = '0.5';

      var xhr = new XMLHttpRequest();
      xhr.open('GET', 'index.php?action=data&_t=' + new Date().getTime(), true);
      xhr.timeout = 8000;
      xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
          if (icon) icon.style.opacity = '1';

          var wifiIcon = document.getElementById('status-wifi-icon');
          var wifiText = document.getElementById('status-wifi-text');

          if (xhr.status === 200) {
            try {
              var res = JSON.parse(xhr.responseText);
              if (res && res.dataRuang) {
                updateDashboardData(res);
                if (wifiIcon) wifiIcon.innerHTML = '📶';
                if (wifiText) wifiText.innerHTML = 'Online';
              }
            } catch(e) {}
          } else {
            if (wifiIcon) wifiIcon.innerHTML = '⚠️';
            if (wifiText) wifiText.innerHTML = 'Offline';
          }
        }
      };
      xhr.send();
    } catch(err) {}
  };

  // Fungsi Auto-Rotation Tab Kelas (Berputar Penuh Tanpa Pernah Reload Kasar)
  function startAutoRotation() {
    if (rotateTimer) clearInterval(rotateTimer);
    if (!isAutoRotate) return;

    rotateTimer = setInterval(function() {
      var curIdx = 0;
      for (var i = 0; i < kelasList.length; i++) {
        if (kelasList[i] === activeKelas) {
          curIdx = i;
          break;
        }
      }
      var nextIdx = (curIdx + 1) % kelasList.length;

      // Saat kembali ke kelas "Semua", ambil data terbaru via AJAX senyap
      if (nextIdx === 0) {
        fetchDataAjax();
      }

      window.changeKelas(kelasList[nextIdx]);
    }, cycleDuration * 1000);
  }

  // Toggle Auto Rotation
  window.toggleAutoRotation = function() {
    isAutoRotate = !isAutoRotate;
    var icon = document.getElementById('rotation-icon');
    var text = document.getElementById('rotation-text');

    if (isAutoRotate) {
      if (icon) icon.innerHTML = '🔄';
      if (text) text.innerHTML = 'Rotasi Aktif';
      startAutoRotation();
    } else {
      if (icon) icon.innerHTML = '⏸️';
      if (text) text.innerHTML = 'Rotasi Diam';
      if (rotateTimer) clearInterval(rotateTimer);
    }
  };

  // Ubah Durasi Rotasi
  window.setCycleDuration = function(sec) {
    cycleDuration = sec;
    try {
      if (window.localStorage) {
        window.localStorage.setItem('display_tt_cycle_seconds', sec + '');
      }
    } catch(e) {}
    updateTimerButtonUI();
    window.toggleTimerMenu(false);
    startAutoRotation();
  };

  // Toggle Menu Popover Durasi
  window.toggleTimerMenu = function(forceClose) {
    var pop = document.getElementById('timer-popover');
    if (!pop) return;

    if (forceClose === false || pop.style.display === 'block') {
      pop.style.display = 'none';
    } else {
      pop.style.display = 'block';
    }
  };

  // Logika Fullscreen Handal (Tidak Lepas karena Tidak Ada Reload)
  function isFullscreenActive() {
    var doc = window.document;
    return !!(doc.fullscreenElement || doc.mozFullScreenElement || doc.webkitFullscreenElement || doc.msFullscreenElement);
  }

  window.enterFullscreen = function() {
    var docEl = document.documentElement;
    var req = docEl.requestFullscreen || docEl.webkitRequestFullScreen || docEl.mozRequestFullScreen || docEl.msRequestFullscreen;
    if (req) {
      try {
        var p = req.call(docEl);
        if (p && p.catch) { p.catch(function() {}); }
      } catch(e) {}
    }
    hideFullscreenPromptIfActive();
  };

  window.toggleFullscreen = function() {
    var doc = window.document;
    if (isFullscreenActive()) {
      var cancel = doc.exitFullscreen || doc.webkitExitFullscreen || doc.mozCancelFullScreen || doc.msExitFullscreen;
      if (cancel) cancel.call(doc);
    } else {
      window.enterFullscreen();
    }
    setTimeout(hideFullscreenPromptIfActive, 300);
  };

  function hideFullscreenPromptIfActive() {
    var prm = document.getElementById('fullscreen-prompt');
    if (prm) {
      prm.style.display = isFullscreenActive() ? 'none' : 'flex';
    }
  }

  // Event Listener Fullscreen Browser TV
  document.addEventListener('fullscreenchange', hideFullscreenPromptIfActive);
  document.addEventListener('webkitfullscreenchange', hideFullscreenPromptIfActive);
  document.addEventListener('mozfullscreenchange', hideFullscreenPromptIfActive);
  document.addEventListener('msfullscreenchange', hideFullscreenPromptIfActive);

  // Sentuh Layar atau Tekan Remote di Mana Saja Langsung Fullscreen Otomatis
  document.addEventListener('click', function(e) {
    if (!isFullscreenActive()) {
      window.enterFullscreen();
    }
  }, false);

  document.addEventListener('keydown', function(e) {
    if (!isFullscreenActive() && (e.keyCode === 13 || e.key === 'Enter')) {
      window.enterFullscreen();
    }
  }, false);

  // Tutup Popover jika klik di luar
  document.addEventListener('click', function(e) {
    var pop = document.getElementById('timer-popover');
    var btn = document.getElementById('btn-toggle-timer');
    if (pop && btn && pop.style.display === 'block') {
      if (!pop.contains(e.target) && !btn.contains(e.target)) {
        pop.style.display = 'none';
      }
    }
  }, false);

  // Inisialisasi Saat Halaman Dimuat
  clockTimer = setInterval(updateClock, 1000);
  updateClock();
  updateTimerButtonUI();
  startAutoRotation();

  // Polling AJAX berkala setiap 30 detik untuk memastikan data selalu fresh
  refreshTimer = setInterval(fetchDataAjax, 30000);

  // Coba Langsung Fullscreen saat Load Selesai
  window.addEventListener('load', function() {
    window.enterFullscreen();
    setTimeout(hideFullscreenPromptIfActive, 500);
  });

})();
</script>

</body>
</html>