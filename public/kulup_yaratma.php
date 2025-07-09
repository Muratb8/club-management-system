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


require_once '../backend/database.php';
$db = new Database();
$db->getConnection();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'create_club') {
    $club_name = trim($_POST['clubName'] ?? '');
    $club_description = trim($_POST['clubDescription'] ?? '');
    $president_email = trim($_POST['presidentEmail'] ?? '');
    $student_number = trim($_POST['studentNumber'] ?? '');
    $password = $_POST['password'] ?? '';
    $logo = $_FILES['logo'] ?? null;

    // Alan kontrolleri
    if ($club_name === '' || $club_description === '' || $president_email === '' || $student_number === '' || $password === '') {
        $message = '<div class="alert alert-danger">Tüm alanlar zorunludur.</div>';
    } else {
        // 1. Öğrenci numarası students tablosunda var mı?
        $student = $db->getRow("SELECT * FROM students WHERE student_number = :student_number", [
            'student_number' => $student_number
        ]);
        if (!$student) {
            $message = '<div class="alert alert-danger">Bu öğrenci numarasına sahip bir öğrenci bulunamadı.</div>';
        } else {
            // 2. Başkanın user kaydı var mı?
            $user = $db->getRow("SELECT id FROM users WHERE email = :email", ['email' => $president_email]);
            if (!$user) {
                // Yeni kullanıcı oluştur
                $db->insertData('users', [
                    'email' => $president_email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'role' => 'club_president'
                ]);
                $user_id = $db->conn->lastInsertId();
            } else {
                $user_id = $user['id'];
                // Rolü club_president olarak güncelle
                $db->updateData('users', ['role' => 'club_president'], 'id = :id', ['id' => $user_id]);
            }

            // 3. Logo yükleme işlemi (isteğe bağlı)
            $logo_path = null;
            if ($logo && $logo['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($logo['name'], PATHINFO_EXTENSION);
                $logo_path = 'uploads/' . uniqid('club_', true) . '.' . $ext;
                move_uploaded_file($logo['tmp_name'], '../' . $logo_path);
            }

            // 4. Kulüp zaten var mı?
            $existing = $db->getRow("SELECT id, clubpresident_id FROM clubs WHERE name = :name", ['name' => $club_name]);
            if ($existing) {
                // Kulüp zaten var, başkan olarak ata (eğer başkanı yoksa)
                if (is_null($existing['clubpresident_id']) || $existing['clubpresident_id'] == 0) {
                    $db->insertData('clubpresidents', [
                        'user_id' => $user_id,
                        'kulup_id' => $existing['id'],
                        'student_number' => $student_number
                    ]);
                    $president_id = $db->conn->lastInsertId();
                    $db->updateData('clubs', ['clubpresident_id' => $president_id], 'id = :id', ['id' => $existing['id']]);
                    $message = '<div class="alert alert-success">Kulüp zaten vardı, başkan olarak atandı.</div>';
                } else {
                    $message = '<div class="alert alert-warning">Bu kulübün zaten bir başkanı var.</div>';
                }
            } else {
                // 5. Yeni kulüp oluştur
                $db->insertData('clubs', [
                    'name' => $club_name,
                    'description' => $club_description,
                    'logo' => $logo_path,
                    'clubpresident_id' => null
                ]);
                $club_id = $db->conn->lastInsertId();
                // 6. clubpresidents tablosuna ekle
                $db->insertData('clubpresidents', [
                    'user_id' => $user_id,
                    'kulup_id' => $club_id,
                    'student_number' => $student_number
                ]);
                $president_id = $db->conn->lastInsertId();
                // 7. clubs tablosunda clubpresident_id güncelle
                $db->updateData('clubs', ['clubpresident_id' => $president_id], 'id = :id', ['id' => $club_id]);
                $message = '<div class="alert alert-success">Kulüp başarıyla oluşturuldu ve başkan atandı.</div>';
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'update_president') {
    $club_id = intval($_POST['club_id'] ?? 0);
    $president_email = trim($_POST['studentEmail'] ?? '');
    $student_number = trim($_POST['studentNumber'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($club_id === 0 || $president_email === '' || $student_number === '' || $password === '') {
        $message = '<div class="alert alert-danger">Tüm alanlar zorunludur.</div>';
    } else {
        // Öğrenci numarası students tablosunda var mı?
        $student = $db->getRow("SELECT * FROM students WHERE student_number = :student_number", [
            'student_number' => $student_number
        ]);
        if (!$student) {
            $message = '<div class="alert alert-danger">Bu öğrenci numarasına sahip bir öğrenci bulunamadı.</div>';
        } else {
            // Kullanıcı var mı?
            $user = $db->getRow("SELECT id FROM users WHERE email = :email", ['email' => $president_email]);
            if (!$user) {
                // Yeni kullanıcı oluştur
                $db->insertData('users', [
                    'email' => $president_email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'role' => 'club_president'
                ]);
                $user_id = $db->conn->lastInsertId();
            } else {
                $user_id = $user['id'];
                $db->updateData('users', ['role' => 'club_president'], 'id = :id', ['id' => $user_id]);
            }

            // clubpresidents tablosuna ekle
            $db->insertData('clubpresidents', [
                'user_id' => $user_id,
                'kulup_id' => $club_id,
                'student_number' => $student_number
            ]);
            $president_id = $db->conn->lastInsertId();
            // clubs tablosunda clubpresident_id güncelle
            $db->updateData('clubs', ['clubpresident_id' => $president_id], 'id = :id', ['id' => $club_id]);
            $message = '<div class="alert alert-success">Başkan başarıyla atandı.</div>';
        }
    }
}
?>
    <style>
        .content {
            margin-left:120px;
            width: 100%;
        }
        h2 {
            color: #007bff;
            margin-left: 200px; /* Başlığı sağa kaydır */
        }
    
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            margin-top: 20px;
            text-align: left; /* Form elemanlarını sola hizala */
            padding-left: 30px; /* Formu biraz sola kaydır */

        }
        .card-body {
        margin-left: 10px; /* Daha sola çekmek için margin ayarlandı */
        padding: 20px; /* İç boşluğu dengeli tutmak için padding ekli */
        max-width: 400px; /* Genişliği sınırlandırarak kompakt bir görünüm sağlandı */
    }
    .form-container {
        display: flex;
        justify-content: center;
        margin-left: 30%; /* Kutucuğu sola çekmek için negatif margin */
        max-width: 600px; /* Daha kompakt bir genişlik ayarı */
        margin: 0 auto;
        padding: 20px; /* İç boşluk dengesi */
        background-color: #f8f9fa; /* Arka plan rengi */
        border-radius: 8px; /* Köşeleri yuvarlama */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Hafif gölge efekti */
        
    }
    .kulupyaratma-main-card {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 8px 32px rgba(13, 30, 77, 0.18), 0 2px 8px rgba(13,110,253,0.10);
    padding: 32px 24px;
    margin-bottom: 40px;
    margin-top: 32px;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}
.kulupyaratma-hero {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin-bottom: 24px;
    margin-top: 8px;
    position: relative;
    z-index: 1;
}
.kulupyaratma-hero-bg {
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
.kulupyaratma-title-modern-main {
    font-family: 'Poppins', sans-serif;
    font-weight: 800;
    font-size: 2.1rem;
    background: linear-gradient(90deg, #0d6efd 60%, #2E49A5 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    letter-spacing: 2px;
    display: inline-block;
    margin-bottom: 0.2rem;
    text-shadow: 0 2px 12px rgba(13,110,253,0.08);
}
.kulupyaratma-title-underline-modern {
    display: block;
    width: 120px;
    height: 6px;
    margin: 0.5rem auto 1.5rem auto;
    border-radius: 3px;
    background: linear-gradient(90deg, #0d6efd 60%, #2E49A5 100%);
    opacity: 0.85;
    box-shadow: 0 2px 8px #0d6efd33;
}
.kulupyaratma-tab-btn {
    border: none;
    border-radius: 8px 8px 0 0;
    background: #f4f6fb;
    color: #2E49A5;
    font-weight: 600;
    padding: 12px 28px;
    font-size: 1.08rem;
    margin-right: 8px;
    margin-bottom: -2px;
    transition: background 0.15s, color 0.15s;
}
.kulupyaratma-tab-btn.active, .kulupyaratma-tab-btn:hover {
    background: linear-gradient(90deg, #0d6efd 60%, #2E49A5 100%);
    color: #fff;
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
        <div class="kulupyaratma-main-card">
        <div class="kulupyaratma-hero position-relative">
        <div class="kulupyaratma-hero-bg"></div>
        <span class="kulupyaratma-title-modern-main">
            <i class="bi bi-building-add"></i> Kulüp Yaratma
        </span>
        <span class="kulupyaratma-title-underline-modern"></span>
    </div>
    <div class="d-flex gap-2 mb-3">
        <button id="btnCreateClub" class="kulupyaratma-tab-btn active" type="button">Kulüp Yaratma</button>
        <button id="btnUpdatePresident" class="kulupyaratma-tab-btn" type="button">Kulüp Başkanı Güncelleme</button>
    </div>
    <div class="card-body px-0">
        <?= $message ?>
        <!-- Kulüp Yaratma Formu -->
        <form id="createClubForm" method="POST" enctype="multipart/form-data" style="display:block;">
            <input type="hidden" name="form_type" value="create_club">
            <div class="mb-3">
                <label for="clubName" class="form-label">Kulüp Adı</label>
                <input type="text" class="form-control" id="clubName" name="clubName" required>
            </div>
            <div class="mb-3">
                <label for="clubDescription" class="form-label">Kulüp Açıklaması</label>
                <textarea class="form-control" id="clubDescription" name="clubDescription" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label for="presidentEmail" class="form-label">Başkan E-Posta</label>
                <input type="email" class="form-control" id="presidentEmail" name="presidentEmail" required>
            </div>
            <div class="mb-3">
                <label for="studentNumber" class="form-label">Öğrenci Numarası</label>
                <input type="text" class="form-control" id="studentNumber" name="studentNumber" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Şifre</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="logo" class="form-label">Kulüp Logosu</label>
                <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
            </div>
            <button type="submit" class="btn btn-success w-100">Kulüp Oluştur</button>
        </form>
        <!-- Kulüp Başkanı Güncelleme Formu -->
        <form id="updatePresidentForm" method="POST" style="display:none;">
            <input type="hidden" name="form_type" value="update_president">
            <div class="mb-3">
                <label for="clubSelect" class="form-label">Başkanı Olmayan Kulüpler</label>
                <select class="form-select" id="clubSelect" name="club_id" required>
                    <option value="">Kulüp seçiniz</option>
                    <?php
                    $clubs_no_president = $db->getAllRecords("SELECT id, name FROM clubs WHERE clubpresident_id IS NULL OR clubpresident_id = 0");
                    foreach ($clubs_no_president as $club) {
                        echo '<option value="' . $club['id'] . '">' . htmlspecialchars($club['name']) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="studentEmail" class="form-label">Başkan E-Posta</label>
                <input type="email" class="form-control" id="studentEmail" name="studentEmail" required>
            </div>
            <div class="mb-3">
                <label for="studentNumber" class="form-label">Öğrenci Numarası</label>
                <input type="text" class="form-control" id="studentNumber" name="studentNumber" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Şifre</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Başkan Ata</button>
        </form>
    </div>
</div>
<script>
document.getElementById('btnCreateClub').onclick = function() {
    document.getElementById('createClubForm').style.display = 'block';
    document.getElementById('updatePresidentForm').style.display = 'none';
    this.classList.add('active');
    document.getElementById('btnUpdatePresident').classList.remove('active');
};
document.getElementById('btnUpdatePresident').onclick = function() {
    document.getElementById('createClubForm').style.display = 'none';
    document.getElementById('updatePresidentForm').style.display = 'block';
    this.classList.add('active');
    document.getElementById('btnCreateClub').classList.remove('active');
};
</script>

    </body>
</html>
