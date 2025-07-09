<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etkinlik Çizelgesi</title>
    <!-- Harici CSS dosyası -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
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
    // Veritabanı bağlantınızı yapın
require_once '../backend/database.php';

try {
    $db = new Database();
    // Etkinlik bilgilerini alalım
    $sql = "SELECT e.id AS id, e.event_topic, e.event_date, e.participants, c.name AS club_name, s.first_name, e.description, e.location 
    FROM event_application e
    LEFT JOIN clubpresidents cp ON e.club_president_id = cp.user_id
    LEFT JOIN clubs c ON cp.president_id = c.clubpresident_id
    LEFT JOIN students s ON cp.student_number = s.student_number
    WHERE e.application_status = 'pending';"; 
    $events = $db->getAllRecords($sql); // Veritabanından verileri alıyoruz
} catch (Exception $e) {
    echo "Veritabanı hatası: " . $e->getMessage();
    exit;
}
    ?>
  <style>

.istek-main-card {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 8px 32px rgba(13, 30, 77, 0.18), 0 2px 8px rgba(13,110,253,0.10);
    padding: 32px 24px;
    margin-bottom: 40px;
    margin-top: 32px;
    max-width: 1200px;
    margin-left: auto;
    margin-right: auto;
}
.istek-hero {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin-bottom: 24px;
    margin-top: 8px;
    position: relative;
    z-index: 1;
}
.istek-hero-bg {
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
.istek-title-modern-main {
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
.istek-title-underline-modern {
    display: block;
    width: 120px;
    height: 6px;
    margin: 0.5rem auto 1.5rem auto;
    border-radius: 3px;
    background: linear-gradient(90deg, #0d6efd 60%, #2E49A5 100%);
    opacity: 0.85;
    box-shadow: 0 2px 8px #0d6efd33;
}
.istek-box {
    background-color: #f8f9fa;
    padding: 22px 20px 18px 20px;
    margin-bottom: 22px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(13, 30, 77, 0.08);
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
    border: 1.5px solid #e3e9f7;
}
.istek-box:hover {
    background: linear-gradient(90deg, #0d6efd11 60%, #2E49A511 100%);
    color: #0d6efd;
}
.istek-box h5 {
    color: #0d6efd;
    font-size: 1.18rem;
    font-weight: 700;
}
.istek-container-box {
    display: none;
    margin-top: 10px;
    background-color: #fff;
    padding: 22px 18px 10px 18px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(13, 30, 77, 0.08);
    border: 1px solid #e3e9f7;
}
.istek-container-box label {
    font-weight: 600;
    color: #2E49A5;
}
.istek-container-box input {
    background: #f4f6fb;
    border-radius: 6px;
    border: 1px solid #e3e9f7;
}
.istek-container-box .btn {
    min-width: 110px;
}

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
        .nav-item:hover {
            background-color: #495057;
            border-radius: 4px;
        }
        .toggle-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            z-index: 1000;
        }
        .box {
    background-color: #fff;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    cursor: pointer; /* Tıklama simgesini aktif eder */
    transition: background-color 0.3s ease; /* Arka plan rengi geçişi için animasyon */
}
/* Container'ın başlangıçta gizli olmasını sağlıyoruz */
.container-box {
    display: none; /* Başlangıçta gizli */
    margin-top: 10px;
            background-color: #f8f9fa; /* Açık gri arka plan */
            padding: 20px;
            border-radius: 8px; /* Köşeleri yuvarla */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Hafif gölge */
            margin-top: 20px;
        }

/* Box kutularına hover efekti eklemek isterseniz */
.box:hover {
    background-color: #e9ecef;
    cursor: pointer;
}
/* Hover efekti ile kutucuğun arka planını mavi yapmak */
.box:hover {
    background-color: #5bc0de; /* Mavi renk */
    color: white; /* Yazıyı beyaz yapar */
}
        .box h5 {
            color: #007bff;
            font-size: 1.2rem;
        }
        .box p {
            font-size: 1rem;
            color: #333;
        }
      
        @media (max-width: 768px) {
            .row {
                margin-left: 0; /* Mobilde margin kaldırılacak */
            }
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        <?php include '../includes/mobil_menu.php'?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 mt-4" id="content">
             <?php include '../includes/ai_chat_widget.php'; ?>
                <div class="istek-main-card">
        <div class="istek-hero position-relative">
            <div class="istek-hero-bg"></div>
            <span class="istek-title-modern-main">
                <i class="bi bi-calendar-check"></i> Etkinlik Çizelgesi
            </span>
            <span class="istek-title-underline-modern"></span>
        </div>
        <div class="container">
            <div class="row">
                    <?php if ($events): ?>
                    <?php foreach ($events as $event): ?>
                        <div class="col-md-4">
                            <div class="istek-box" onclick="toggleContainer(this)">
                                <h5>Etkinlik Adı: <?php echo htmlspecialchars($event['event_topic'], ENT_QUOTES, 'UTF-8'); ?></h5>
                                <p>Kulüp Adı: <?php echo htmlspecialchars($event['club_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                            <div class="istek-container-box">
                                <form>
                                    <div class="mb-3">
                                        <label class="form-label">Kulüp Adı</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($event['club_name'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Kulüp Başkanı Adı</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($event['first_name'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Etkinlik Adı</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($event['event_topic'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Etkinliğin Açıklaması</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($event['description'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Etkinliğe Katılan Ünlüler</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($event['participants'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Etkinlik Tarihi</label>
                                        <input type="datetime" class="form-control" value="<?php echo htmlspecialchars($event['event_date'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-success me-2 action-btn" data-id="<?php echo $event['id']; ?>" data-action="approve">Onayla</button>
                                        <button type="button" class="btn btn-danger action-btn" data-id="<?php echo $event['id']; ?>" data-action="reject">Reddet</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Hiçbir etkinlik başvurusu bulunmamaktadır.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
        </main>
    </div>
</div>




<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Kutulara tıklanıldığında container'ı açıp kapatmak için fonksiyon
function toggleContainer(boxElement) {
    var container = boxElement.nextElementSibling; // Container'ı bul
    // Container zaten görünüyorsa, gizle, yoksa göster
    if (container.style.display === "none" || container.style.display === "") {
        container.style.display = "block";
    } else {
        container.style.display = "none";
    }
}

document.querySelectorAll('.action-btn').forEach(button => {
    button.addEventListener('click', function () {
        const action = this.getAttribute('data-action');
        const id = this.getAttribute('data-id');
        const managerId = '<?php echo $_SESSION['id']; ?>';  // Kullanıcı ID'si
        const confirmMessage = action === 'approve'
            ? "Bu etkinliği onaylamak istediğinizden emin misiniz?"
            : "Bu etkinliği reddetmek istediğinizden emin misiniz?";

        if (confirm(confirmMessage)) {
            fetch('../backend/update_status.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({ 
                    id: id,
                    action: action,
                    manager_id: managerId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert("İşlem başarıyla gerçekleştirildi.");
                    location.reload();
                } else {
                    alert("Hata oluştu: " + (data.error || "Bilinmeyen hata"));
                }
            });
        }
    });
})
</script>
</body>
</html>
