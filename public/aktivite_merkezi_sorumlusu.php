<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etkinlik Çizelgesi</title>
    <?php
session_start();

$currentPage = basename($_SERVER['PHP_SELF']);
$excludePages = ['giris.php'];

if (!isset($_SESSION['id'])) {
    header("Location: ../public/giris.php");
    exit();
}

$currentPage = basename($_SERVER['PHP_SELF']);
$excludePages = ['giris.php'];

if (!in_array($currentPage, $excludePages)) {
    $_SESSION['previous_page'] = $_SERVER['REQUEST_URI'];
}
$role = $_SESSION['role']; // Kullanıcının rolünü al
// Sadece "manager" veya "developer" rolüne izin ver
if ($role !== 'manager' && $role !== 'developer') {
    // Eğer daha önceki sayfa kayıtlıysa oraya dön
    if (isset($_SESSION['previous_page'])) {
        header("Location: " . $_SESSION['previous_page']);
    } else {
        // Önceki sayfa yoksa varsayılan sayfaya gönder
        header("Location: ../index.php");
    }
    exit();
}
include("../backend/database.php"); // veritabanı bağlantın burada
$db=$db = new Database(); 

// Tarihleri elle veya dinamik oluşturabilirsin
$month_offset = isset($_GET['month_offset']) ? intval($_GET['month_offset']) : 0;
$saatler = ['06:00','07:00','08:00','09:00','10:00','11:00','12:00','13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00', '22:00'];

 // Veritabanından etkinlikleri çek
 $etkinlikler = $db->getAllRecords("
 SELECT event_topic, event_date 
 FROM event_application 
 WHERE application_status = 'approved'
");
// Etkinlikleri tarih ve saate göre gruplama
$etkinlik_by_date = [];
foreach ($etkinlikler as $etkinlik) {
    $timestamp = strtotime($etkinlik['event_date']);
    $event_date = date("Y-m-d", $timestamp);
    $rounded_hour = date("H:00", $timestamp); // 🔁 Saat 17:25 → 17:00 gibi
    $etkinlik_by_date[$event_date][$rounded_hour] = $etkinlik;
}
$week_offset = isset($_GET['week_offset']) ? intval($_GET['week_offset']) : 0;

$current_date = new DateTime();
$current_date->modify("+$month_offset month"); // Aylık kayma
$current_date->modify("+".($week_offset * 7)." days"); // Haftalık kayma
$dates = [];
for ($i = 0; $i < 7; $i++) {
    $date = $current_date->format('Y-m-d'); // GÜNCELLENDİ
    $dates[] = $date;
    $current_date->modify('+1 day');
}

function turkce_tarih($dateString) {
    $aylar = [
        '01' => 'ocak', '02' => 'şubat', '03' => 'mart', '04' => 'nisan',
        '05' => 'mayıs', '06' => 'haziran', '07' => 'temmuz', '08' => 'ağustos',
        '09' => 'eylül', '10' => 'ekim', '11' => 'kasım', '12' => 'aralık'
    ];

    $dt = new DateTime($dateString);
    $gun = $dt->format('j'); // Gün (başında sıfır olmadan)
    $ay = $dt->format('m');  // Ay (01-12)
    return $gun . ' ' . $aylar[$ay];
}
?>
    
    <!-- Harici CSS dosyası -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        .content {
            flex-grow: 1;
            padding: 30px;
            margin-left: 220px;
            transition: margin-left 0.3s ease;
        }
        .content.shifted {
            margin-left: 0;
        }
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }
        .table-bordered {
            border: 2px solid #007bff;
        }
        .table-light {
            background-color: #e9ecef;
        }
        .table th {
            background-color: #007bff;
            color: white;
        }
        h2 {
            color: #007bff;
        }
        .table td {
            background-color: #ffffff;
        }
        .table td:hover {
            background-color: #f1f1f1;
        }
        .table-responsive {
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container-fluid">
<div class="row">
<?php include '../includes/sidebar.php'; ?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 mt-4" id="content">
                       <?php include '../includes/ai_chat_widget.php'; ?>
    <div class="content" id="content">
      
        <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
    <button id="prevMonthBtn" class="btn btn-outline-dark">◀ Önceki Ay</button>
    <div>
        <button id="prevWeekBtn" class="btn btn-outline-secondary me-2">◀ Önceki Hafta</button>
        <button id="nextWeekBtn" class="btn btn-primary">Sonraki Hafta ▶</button>
    </div>
    <button id="nextMonthBtn" class="btn btn-dark">Sonraki Ay ▶</button>
</div>
            <div class="table-responsive">
            <table class="table table-bordered">
    <thead class="table-light">
        <tr>
            <th class="text-center">Saat</th>
            <?php foreach ($dates as $date): ?>
    <th class="text-center"><?= turkce_tarih($date) ?></th>
<?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
                        <?php foreach ($saatler as $hour): ?>
                            <tr>
                                <td class="text-center"><?= $hour ?></td>
                                <?php foreach ($dates as $date): ?>
                                    <?php if (isset($etkinlik_by_date[$date][$hour])): 
                                        $etkinlik = $etkinlik_by_date[$date][$hour]; ?>
                                        <td><strong><?= $etkinlik['event_topic'] ?></strong></td>
                                    <?php else: ?>
                                        <td></td>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
</table>
            </div>
        </div>
    </div>
    </main>
    </div>
    </div>
 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('nextWeekBtn').addEventListener('click', function () {
        const urlParams = new URLSearchParams(window.location.search);
        let weekOffset = parseInt(urlParams.get('week_offset') || '0');
        weekOffset++;
        window.location.href = window.location.pathname + '?week_offset=' + weekOffset;
    });

    document.getElementById('prevWeekBtn').addEventListener('click', function () {
        const urlParams = new URLSearchParams(window.location.search);
        let weekOffset = parseInt(urlParams.get('week_offset') || '0');
        weekOffset--;
        window.location.href = window.location.pathname + '?week_offset=' + weekOffset;
    });
    function updateURLParam(param, change) {
    const urlParams = new URLSearchParams(window.location.search);
    let current = parseInt(urlParams.get(param) || '0');
    urlParams.set(param, current + change);
    window.location.search = urlParams.toString();
}

document.getElementById('prevWeekBtn').addEventListener('click', () => updateURLParam('week_offset', -1));
document.getElementById('nextWeekBtn').addEventListener('click', () => updateURLParam('week_offset', 1));
document.getElementById('prevMonthBtn').addEventListener('click', () => updateURLParam('month_offset', -1));
document.getElementById('nextMonthBtn').addEventListener('click', () => updateURLParam('month_offset', 1));
</script>
</script>
</body>
</html>
