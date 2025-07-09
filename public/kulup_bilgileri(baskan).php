<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kulüp Üye Listesi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: giris.php");
    exit();
}
$currentPage = basename($_SERVER['PHP_SELF']);
$excludePages = ['giris.php'];

if (!in_array($currentPage, $excludePages)) {
    $_SESSION['previous_page'] = $_SERVER['REQUEST_URI'];
}
$role = $_SESSION['role']; // Kullanıcının rolünü al
// Sadece "manager" veya "developer" rolüne izin ver
if ($role !== 'club_president' && $role !== 'developer') {
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
$db = new Database();

// Kulüp başkanının bilgilerini güvenli şekilde al
try {
   $president = $db->getRow(
        "SELECT cp.*, s.first_name, s.last_name, c.name as club_name FROM clubpresidents cp
         LEFT JOIN students s ON cp.student_number = s.student_number
         LEFT JOIN clubs c ON cp.kulup_id = c.id
         WHERE cp.user_id = :user_id",
        ['user_id' => $_SESSION['id']]
    );
    if ($president) {
        $kulup_ids = $president['kulup_id'];
        $club_manager_name = htmlspecialchars($president['first_name'] . " " . $president['last_name']);
        $club_name = htmlspecialchars($president['club_name']);
    } else {
        die("Hata: Kulüp başkanı bilgisi bulunamadı.");
    }
} catch (Exception $e) {
    error_log("DB Hatası: " . $e->getMessage());
    die("Veri alınırken bir hata oluştu.");
}


try {
    $clubmembers = $db->getData('clubmembers', '*', 'club_id = :club_id AND status = :status', ['club_id' => $kulup_ids, 'status' => 'approved']);
    $total_members = count($clubmembers);
    $member_ids = array_column($clubmembers, 'user_id');
} catch (Exception $e) {
    error_log("Üye bilgileri çekilirken hata oluştu: " . $e->getMessage());
    die("Kulüp üyeleri alınamadı.");
}

try {
    $students = [];
    if (!empty($member_ids)) {
        // Placeholder dizisi oluşturuluyor (:id1, :id2, ...)
        $placeholders = [];
        $params = [];
        foreach ($member_ids as $index => $id) {
            $key = ":id" . $index;
            $placeholders[] = $key;
            $params[$key] = $id;
        }
        $params[':kulup_id'] = $kulup_ids;

        $sql = "SELECT s.*, u.email, 'Üye' as role
                FROM students s 
                JOIN users u ON s.user_id = u.id 
                WHERE s.user_id IN (" . implode(',', $placeholders) . ")
                UNION
                SELECT s.*, u.email, 'Başkan' as role
                FROM students s
                JOIN users u ON s.user_id = u.id
                JOIN clubpresidents cp ON cp.user_id = u.id
                WHERE cp.kulup_id = :kulup_id";

        $students = $db->getRows($sql, $params);
    } else {
        $sql = "SELECT s.*, u.email, 'Başkan' as role
                FROM students s
                JOIN users u ON s.user_id = u.id
                JOIN clubpresidents cp ON cp.user_id = u.id
                WHERE cp.kulup_id = :kulup_id";
        $students = $db->getRows($sql, [':kulup_id' => $kulup_ids]);
    }
} catch (Exception $e) {
    error_log("Öğrenci bilgileri alınırken hata: " . $e->getMessage());
    die("Öğrenci bilgileri yüklenemedi.");
}

try {
    $event_result = $db->getRow("SELECT COUNT(*) as event_count FROM events WHERE club_id = :club_id", ['club_id' => $kulup_ids]);
    $event_count = $event_result ? $event_result['event_count'] : 0;
} catch (Exception $e) {
    error_log("Etkinlik sayısı alınamadı: " . $e->getMessage());
    $event_count = 0;
}

// Sayfalama ayarları
$perPage = 10;
$totalMembers = count($students);
$totalPages = ceil($totalMembers / $perPage);
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
if ($page > $totalPages) $page = $totalPages;
$start = ($page - 1) * $perPage;
$studentsToShow = array_slice($students, $start, $perPage);
?>
  <link rel="stylesheet" href="../assets/css/style.css">
    <style>

        .btn-remove {
            color: white;
            background-color: #dc3545;
            border: none;
            border-radius: 4px;
            padding: 5px 10px;
        }

        .btn-remove:hover {
            background-color: #bb2d3b;
        }
.kulup-main-card {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 8px 32px rgba(13, 30, 77, 0.22), 0 2px 8px rgba(13,110,253,0.10);
    padding: 32px 24px;
    margin-bottom: 40px;
    margin-top: 32px;
    max-width: 98vw;         /* Genişliği neredeyse tam ekran yap */
    width: 100%;
    min-width: 320px;
    margin-left: auto;
    margin-right: auto;
}
@media (min-width: 1200px) {
    .kulup-main-card {
        max-width: 1600px;   /* Büyük ekranlarda daha geniş */
    }
}
.kulup-hero {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin-bottom: 24px;
    margin-top: 8px;
    position: relative;
    z-index: 1;
}
.kulup-hero-bg {
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
.kulup-title-modern-main {
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
.kulup-title-underline-modern {
    display: block;
    width: 120px;
    height: 6px;
    margin: 0.5rem auto 1.5rem auto;
    border-radius: 3px;
    background: linear-gradient(90deg, #0d6efd 60%, #2E49A5 100%);
    opacity: 0.85;
    box-shadow: 0 2px 8px #0d6efd33;
}
.table thead.custom-header,
.table thead.custom-header th {
    background: #f4f6fb !important;
    color: #222 !important;
    font-weight: 600;
    border-color: #e3e3e3;
}
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
        <?php include_once '../includes/sidebar.php'; ?>
            <!-- Mobil Hamburger Menü -->
            <?php include_once '../includes/mobil_menu.php'; ?>
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                 <?php include '../includes/ai_chat_widget.php'; ?>
    <div class="kulup-main-card">
        <div class="kulup-hero position-relative">
            <div class="kulup-hero-bg"></div>
            <span class="kulup-title-modern-main">
                <i class="bi bi-people-fill"></i> Kulüp Üye Listesi
            </span>
            <span class="kulup-title-underline-modern"></span>
        </div>

        <!-- Club Details -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title">Toplam Üye</h5>
                                <p class="card-text display-6 fw-bold"><?php echo $total_members; ?></p>
                            </div>
                            <i class="bi bi-people-fill display-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title">Aktif Etkinlik</h5>
                                <p class="card-text display-6 fw-bold"><?php echo $event_count; ?></p>
                            </div>
                            <i class="bi bi-calendar-event-fill display-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title">Kulüp Başkanı</h5>
                                <p class="card-text display-6 fw-bold"><?php echo htmlspecialchars($club_manager_name); ?></p>
                            </div>
                            <i class="bi bi-person-badge-fill display-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Members List Section -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Üye Listesi</h5>
                <div class="mb-3">
                    <input type="text" id="memberSearch" class="form-control" placeholder="İsme göre ara...">
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="custom-header">
                            <tr>
                                <th>#</th>
                                <th>Ad Soyad</th>
                                <th>Gpa</th>
                                <th>Cgpa</th>
                                <th>Doğum Yeri</th>
                                <th>Doğum Tarihi</th>
                                <th>Cinsiyet</th>
                                <th>Telefon Numarası</th>
                                <th>Öğrenci Numarası</th>
                                <th>E-posta</th>
                                <th>Görev</th>
                                <th>Aksiyon</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($studentsToShow) : ?>
                                <?php foreach ($studentsToShow as $key=>$student) : ?>
                                    <tr>
                                        <th scope="row"><?php echo $start + $key + 1; ?></th>
                                        <td><?php echo htmlspecialchars($student['first_name'] . " " . $student['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['gpa']); ?></td>
                                        <td><?php echo htmlspecialchars($student['cgpa']); ?></td>
                                        <td><?php echo htmlspecialchars($student['birth_place']); ?></td>
                                        <td><?php echo htmlspecialchars($student['birth_date']); ?></td>
                                        <td><?php echo htmlspecialchars($student['gender']); ?></td>
                                        <td><?php echo htmlspecialchars($student['phone_number']); ?></td>
                                        <td><?php echo htmlspecialchars($student['student_number']); ?></td>
                                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                                        <td>Üye</td>
                                        <td>
                                            <button class="btn-remove" onclick="removeMember(<?php echo $student['user_id']; ?>)">Üyeyi Çıkar</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="12" class="text-center">Kulüpte üye bulunamadı</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
<nav aria-label="Üye sayfalama">
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
    
  
    <script>
     function removeMember(userId) {
    const reason = prompt("Bu üyeyi neden kulüpten çıkarmak istiyorsunuz?");

    if (reason === null || reason.trim() === "") {
        alert("Lütfen bir neden belirtin.");
        return;
    }

    if (confirm('Bu üyeyi kulüpten çıkarmak istediğinizden emin misiniz?')) {
        fetch('../backend/remove_member.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'user_id=' + encodeURIComponent(userId) +
                  '&club_id=' + encodeURIComponent(<?php echo json_encode($kulup_ids); ?>) +
                  '&reason=' + encodeURIComponent(reason)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Üye başarıyla çıkarıldı');
                location.reload();
            } else {
                alert('Hata: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Hata:', error);
            alert('Bir hata oluştu');
        });
    }
}

    </script>

    <script>
document.getElementById('memberSearch').addEventListener('keyup', function () {
    const searchValue = this.value.toLowerCase();
    const rows = document.querySelectorAll('table tbody tr');

    rows.forEach(row => {
        const nameCell = row.cells[1]; // Ad Soyad sütunu
        if (nameCell) {
            const name = nameCell.textContent.toLowerCase();
            row.style.display = name.includes(searchValue) ? '' : 'none';
        }
    });
});
</script>
</body>

</html>
