<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etkinlikler</title>
    <?php
session_start();
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
if ($role !== 'student' && $role !=='club_president' && $role!=='manager' && $role !== 'developer') {
    // Eğer daha önceki sayfa kayıtlıysa oraya dön
    if (isset($_SESSION['previous_page'])) {
        header("Location: " . $_SESSION['previous_page']);
    } else {
        // Önceki sayfa yoksa varsayılan sayfaya gönder
        header("Location: ../index.php");
    }
    exit();
}
require_once '../backend/database.php';
try {
    $db = new Database();
    $sql = "SELECT e.id AS id, e.event_topic, e.event_date, e.participants, c.name AS club_name, s.first_name, e.description, e.location 
    FROM event_application e
    LEFT JOIN clubpresidents cp ON e.club_president_id = cp.user_id
    LEFT JOIN clubs c ON cp.president_id = c.clubpresident_id
    LEFT JOIN students s ON cp.student_number = s.student_number
    WHERE e.application_status = 'approved';"; 
    $events = $db->getAllRecords($sql);

    // Eğer öğrenci ise, katıldığı etkinlikleri çek
    $joinedEvents = [];
    if ($role === 'student') {
        $studentRow = $db->getRow("SELECT student_id FROM students WHERE user_id = :user_id", ['user_id' => $_SESSION['id']]);
        $student_id = $studentRow ? $studentRow['student_id'] : null;
        if ($student_id) {
            $joined = $db->getAllRecords("SELECT event_id FROM event_participants WHERE student_id = :student_id", ['student_id' => $student_id]);
            $joinedEvents = array_column($joined, 'event_id');
        }
    }
} catch (Exception $e) {
    echo "Veritabanı hatası: " . $e->getMessage();
    exit;
}

?>
    <!-- Harici CSS dosyası -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
.event-main-card {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 8px 32px rgba(13, 30, 77, 0.22), 0 2px 8px rgba(13,110,253,0.10);
    padding: 32px 24px;
    margin-bottom: 40px;
    margin-top: 32px;
    max-width: 1200px;
    margin-left: auto;
    margin-right: auto;
}
.event-hero {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin-bottom: 24px;
    margin-top: 8px;
    position: relative;
    z-index: 1;
}
.event-hero-bg {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 340px;
    height: 80px;
    background: radial-gradient(circle at 60% 40%, #0d6efd22 60%, transparent 100%);
    filter: blur(12px);
    transform: translate(-50%, -50%);
    z-index: -1;
}
.event-title-modern-main {
    font-family: 'Poppins', sans-serif;
    font-weight: 800;
    font-size: 2.3rem;
    background: linear-gradient(90deg, #0d6efd 60%, #2E49A5 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    letter-spacing: 2px;
    display: inline-block;
    margin-bottom: 0.2rem;
    text-shadow: 0 2px 12px rgba(13,110,253,0.08);
}
.event-title-underline-modern {
    display: block;
    width: 120px;
    height: 6px;
    margin: 0.5rem auto 1.5rem auto;
    border-radius: 3px;
    background: linear-gradient(90deg, #0d6efd 60%, #2E49A5 100%);
    opacity: 0.85;
    box-shadow: 0 2px 8px #0d6efd33;
}
.event-container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: flex-start;
    padding: 20px 0;
}
.event-box {
    flex: 0 0 calc(25% - 20px);
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(13, 30, 77, 0.10);
    padding: 18px 12px 16px 12px;
    margin-bottom: 18px;
    transition: transform 0.2s, box-shadow 0.2s;
    font-size: 1.1rem;
    text-align: center;
    position: relative;
    min-width: 240px;
    max-width: 320px;
}
.event-box:hover {
    transform: scale(1.04);
    box-shadow: 0 8px 32px rgba(13, 30, 77, 0.18);
    background-color: #f8faff;
}
.event-title-modern {
    font-family: 'Poppins', sans-serif;
    font-weight: 800;
    font-size: 1.5rem;
    background: linear-gradient(90deg, #0d6efd 60%, #2E49A5 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    letter-spacing: 1px;
    display: inline-block;
    margin-bottom: 10px;
    text-shadow: 0 2px 8px rgba(13,110,253,0.08);
}
.line {
    border-top: 2px solid #0d6efd;
    margin: 12px 0 16px 0;
}
.event-details {
    font-size: 1rem;
    margin-bottom: 10px;
}
@media (max-width: 1200px) {
    .event-box { flex: 0 0 calc(33.333% - 20px); }
}
@media (max-width: 900px) {
    .event-box { flex: 0 0 calc(50% - 20px); }
}
@media (max-width: 600px) {
    .event-box { flex: 0 0 100%; }
    .event-container { padding: 12px 2px; }
}
    </style>
</head>
<body>

    <div class="container-fluid">
        <div class="row">

         <?php include '../includes/sidebar.php'; ?> 
        <?php include '../includes/mobil_menu.php'?>

            <main class="col-md-9 col-lg-10 p-4">
                 <?php include '../includes/ai_chat_widget.php'; ?>
    <div class="event-main-card">
        <div class="event-hero position-relative">
            <div class="event-hero-bg"></div>
            <span class="event-title-modern-main">
                <i class="bi bi-calendar-event"></i> Etkinlikler
            </span>
            <span class="event-title-underline-modern"></span>
        </div>
        <div class="event-container">
            <?php
            // Sayfalama ayarları
            $perPage = 8;
            $totalEvents = count($events);
            $totalPages = ceil($totalEvents / $perPage);
            $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
            if ($page < 1) $page = 1;
            if ($page > $totalPages) $page = $totalPages;
            $start = ($page - 1) * $perPage;
            $eventsToShow = array_slice($events, $start, $perPage);

            // Katılım kontrolü için örnek (kendi kodunda varsa kullan)
            $userId = $_SESSION['id'];
            $katildigiEtkinlikler = [];
            $katilimlar = $db->getAllRecords(
                "SELECT event_id FROM event_participants WHERE student_id = :user_id",
                ['user_id' => $userId]
            );
            if ($katilimlar && is_array($katilimlar)) {
                foreach ($katilimlar as $katilim) {
                    $katildigiEtkinlikler[] = (int)$katilim['event_id'];
                }
            }
            foreach ($eventsToShow as $event): ?>
                <?php $katildiMi = in_array((int)$event['id'], $katildigiEtkinlikler, true); ?>
                <div class="event-box" data-event-id="<?= $event['id']; ?>">
                    <div class="event-title-modern"><?= htmlspecialchars($event['event_topic']); ?></div>
                    <div class="line"></div>
                    <div class="event-details">
                        <strong>Zaman:</strong> <?= htmlspecialchars($event['event_date']); ?><br>
                        <strong>Mekan:</strong> <?= htmlspecialchars($event['location']); ?>
                    </div>
                  <?php if ($role === 'student'): ?>
            <?php if (in_array($event['id'], $joinedEvents)): ?>
                <button class="btn btn-secondary mt-3 join-btn" disabled>Zaten Katıldınız</button>
            <?php else: ?>
                <button class="btn btn-success mt-3 join-btn">Katıl</button>
            <?php endif; ?>
        <?php elseif ($role === 'club_president' || $role === 'manager'): ?>
            <button class="btn btn-outline-secondary mt-3" disabled>Sadece izleyebilirsiniz</button>
        <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <!-- Sayfalama bağlantıları -->
        <nav aria-label="Etkinlik sayfalama">
            <ul class="pagination justify-content-center mt-4">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page-1 ?>">«</a>
                    </li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page+1 ?>">»</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</main>
        </div>
    </div>
    

    <!-- Bootstrap JS, Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>

document.addEventListener('DOMContentLoaded', function() {
   const buttons = document.querySelectorAll('.join-btn');
    <?php if ($role === 'student'): ?>
    buttons.forEach(button => {
        if (button.disabled) return;
        button.addEventListener('click', function() {
            const eventBox = this.closest('.event-box');
            const eventId = eventBox.getAttribute('data-event-id');
            fetch('../backend/katil.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'event_id=' + eventId
            })
            .then(response => response.text())
            .then(data => {
                alert(data);
                this.disabled = true;
                this.textContent = "Zaten Katıldınız";
            });
        });
    });
    <?php endif; ?>
});
    </script>
</body>
</html>
