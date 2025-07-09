<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Ana Sayfa</title>

    <!-- Harici CSS dosyası -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        .student-name-gradient {
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
.student-name-underline {
    display: block;
    width: 120px;
    height: 6px;
    margin: 0.5rem auto 1.5rem auto;
    border-radius: 3px;
    background: linear-gradient(90deg, #0d6efd 60%, #2E49A5 100%);
    opacity: 0.85;
    box-shadow: 0 2px 8px #0d6efd33;
}
    </style>
    <?php
require_once '../backend/database.php'; // Mevcut veritabanı sınıfını dahil et
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../public/giris.php");
    exit();
}
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $alertClass = "alert-" . $message['type'];  // alert-success, alert-danger gibi sınıflar
    
    // Mesajı ekrana yazdır
    echo "<div class='alert $alertClass alert-dismissible fade show' role='alert'>";
    echo $message['text'];
    echo "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
    echo "</div>";
    
    // Mesajı oturumdan temizle
    unset($_SESSION['message']);
}
$db = new Database();
$user_id = $_SESSION['id']; // Oturum açmış kullanıcının ID'si
$user = $db->getRow("SELECT * FROM users WHERE id = ?", [$user_id]);
$currentPage = basename($_SERVER['PHP_SELF']);
$excludePages = ['giris.php'];

if (!in_array($currentPage, $excludePages)) {
    $_SESSION['previous_page'] = $_SERVER['REQUEST_URI'];
}
$role = $_SESSION['role']; // Kullanıcının rolünü al
// Sadece "manager" veya "developer" rolüne izin ver
if ($role !== 'student' && $role !== 'club_president' && $role !== 'manager' && $role !== 'developer') {
    // Eğer daha önceki sayfa kayıtlıysa oraya dön
    if (isset($_SESSION['previous_page'])) {
        header("Location: " . $_SESSION['previous_page']);
    } else {
        // Önceki sayfa yoksa varsayılan sayfaya gönder
        header("Location: ../index.php");
    }
    exit();
}


if ($role == 'student') {
    $studentInfo = $db->getRow("SELECT * FROM students WHERE user_id = ?", [$user_id]);

    // Kulüp üyeliklerini sorgulamak
$sql = "SELECT clubs.name, clubs.id, 
(SELECT COUNT(*) FROM clubmembers WHERE clubmembers.club_id = clubs.id AND clubmembers.status = 'approved') AS member_count
FROM clubs 
JOIN clubmembers ON clubs.id = clubmembers.club_id
WHERE clubmembers.user_id = ? AND clubmembers.status = 'approved'";

$stmt = $db->prepare($sql);
$stmt->execute([$user_id]);
$clubs = $stmt->fetchAll();

// Eğer öğrenci üye olduğu kulüpleri varsa, 'Üye Olunan Kulüpler' bölümünü gösteriyoruz
$has_clubs = count($clubs) > 0;
}

if ($role == 'club_president') {
    // Önce clubpresidents tablosundan student_number'ı alıyoruz
    $presidentClubInfo = $db->getRow("SELECT * FROM clubpresidents WHERE user_id = ?", [$user_id]);
    
    if ($presidentClubInfo) {
        $student_number = $presidentClubInfo['student_number'];
        
        // Şimdi students tablosundan student_number'a göre bilgileri çekiyoruz
        $presidentInfo = $db->getRow("SELECT * FROM students WHERE student_number = ?", [$student_number]);

        // DİKKAT: clubpresidents tablosunda kulüp id'si 'kulup_id' olarak geçiyor!
        $club_id = $presidentClubInfo['kulup_id'];

        // Kulüp adı
        $clubName = $db->getColumn("SELECT name FROM clubs WHERE id = ?", [$club_id]);

        // Toplam üye sayısı
        $totalMembers = $db->getColumn("SELECT COUNT(*) FROM clubmembers WHERE club_id = ? AND status = 'approved'", [$club_id]);

        // Onay bekleyen başvuru sayısı
        $pendingMembers = $db->getColumn("SELECT COUNT(*) FROM clubmembers WHERE club_id = ? AND status = 'pending'", [$club_id]);

        // Mevcut etkinlik sayısı (onaylanmış etkinlikler)
        $eventCount = $db->getColumn("SELECT COUNT(*) FROM event_application WHERE club_president_id = ? AND application_status = 'approved'", [$user_id]);
    }
}
if($role == 'developer'){
    $presidents = $db->getData('clubpresidents', '*'); // clubpresident tablosundan tüm başkanları al
    $students = $db->getData('students', '*'); // Öğrenciler
    $managers = $db->getData('managers', '*'); // Aktivite Merkezi Sorumluları
}

if ($role == 'manager' || $role == 'club_president') {
    // Toplam öğrenci sayısı
    $totalStudents = $db->getColumn("SELECT COUNT(*) FROM students");
    // Toplam kulüp başkanı
    $totalPresidents = $db->getColumn("SELECT COUNT(*) FROM clubpresidents");
    // Toplam kulüp
    $totalClubs = $db->getColumn("SELECT COUNT(*) FROM clubs");
    // Kulüp üyeliği olan öğrenci sayısı (her öğrenci birden fazla kulübe üye olabilir, benzersiz say)
    $studentsWithClub = $db->getColumn("SELECT COUNT(DISTINCT user_id) FROM clubmembers WHERE status = 'approved'");
    // Toplam kulüp üyeliği (tüm üyelikler)
    $totalMemberships = $db->getColumn("SELECT COUNT(*) FROM clubmembers WHERE status = 'approved'");
    // Toplam etkinlik (isteğe bağlı)
    $totalEvents = $db->getColumn("SELECT COUNT(*) FROM event_application");

     // Kulüp üyelikleri (her kulüp için üye sayısı)
    $clubMemberships = $db->getRows("SELECT name, (SELECT COUNT(*) FROM clubmembers WHERE club_id = clubs.id AND status = 'approved') as member_count FROM clubs");
    $eventApplications = $db->getRows("SELECT event_topic as event_name, COUNT(*) as app_count FROM event_application GROUP BY event_topic");
}
?>
</head>
<body>
    <div class="container-fluid">
        <div class="row">   
            <?php include_once '../includes/sidebar.php'; ?>
            <!-- Mobil Hamburger Menü -->
            <?php include_once '../includes/mobil_menu.php'; ?>
        
            <!-- Ana İçerik -->
            <main class="col-md-9 col-lg-10 p-4">
              
                   <?php include '../includes/ai_chat_widget.php'; ?>
          

<?php if ($role == 'student'): ?>
    <div class="row justify-content-center mb-4">
    <div class="col-md-8">
        <div class="card shadow-lg border-0 p-4" style="border-radius: 22px;">
            <div class="card-body">
                <div class="text-center mb-4">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($studentInfo['first_name'].' '.$studentInfo['last_name']) ?>&background=0d6efd&color=fff&size=120" alt="Profil Resmi" class="profile-img mb-3 rounded-circle border border-3 border-primary" style="width:120px;height:120px;">
                    <div class="student-name-gradient">
                        <?= htmlspecialchars($studentInfo['first_name'] . " " . $studentInfo['last_name']); ?>
                    </div>
                    <div class="student-name-underline"></div>
                </div>
                <div class="row justify-content-center">
                    <div class="col-md-6 text-start">
                        <div class="mb-2"><strong>GPA:</strong> <span class="text-muted"><?= htmlspecialchars($studentInfo['gpa']); ?></span></div>
                        <div class="mb-2"><strong>CGPA:</strong> <span class="text-muted"><?= htmlspecialchars($studentInfo['cgpa']); ?></span></div>
                        <div class="mb-2"><strong>Doğum Yeri:</strong> <span class="text-muted"><?= htmlspecialchars($studentInfo['birth_place']); ?></span></div>
                        <div class="mb-2"><strong>Doğum Tarihi:</strong> <span class="text-muted"><?= htmlspecialchars($studentInfo['birth_date']); ?></span></div>
                    </div>
                    <div class="col-md-6 text-start">
                        <div class="mb-2"><strong>Cinsiyet:</strong> <span class="text-muted"><?= htmlspecialchars($studentInfo['gender']); ?></span></div>
                        <div class="mb-2"><strong>Telefon:</strong> <span class="text-muted"><?= htmlspecialchars($studentInfo['phone_number']); ?></span></div>
                        <div class="mb-2"><strong>Öğrenci No:</strong> <span class="text-muted"><?= htmlspecialchars($studentInfo['student_number']); ?></span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Üye Olunan Kulüpler Kartı -->
<div class="row justify-content-center mb-4">
    <div class="col-md-8">
        <div class="card shadow-lg border-0 p-4" style="border-radius: 22px;">
            <div class="card-body">
                <h5 class="fw-bold mb-3"><i class="bi bi-people"></i> Üye Olunan Kulüpler</h5>
                <?php if ($has_clubs): ?>
                    <ul class="list-group">
                        <?php foreach ($clubs as $club): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center" style="border-radius: 12px;">
                                <?= htmlspecialchars($club['name']); ?>
                                <span class="badge bg-primary rounded-pill"><?= $club['member_count']; ?> Üye</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="alert alert-info mb-0">Henüz bir kulübe üye değilsiniz.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<?php endif; ?>
 
    <?php if ($role == 'club_president'): ?>
        <div class="row justify-content-center mb-4">
    <div class="col-md-8">
        <div class="card shadow-lg border-0 p-4" style="border-radius: 22px;">
            <div class="card-body">
                <div class="text-center mb-4">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($presidentInfo['first_name'].' '.$presidentInfo['last_name']) ?>&background=0d6efd&color=fff&size=120" alt="Profil Resmi" class="profile-img mb-3 rounded-circle border border-3 border-primary" style="width:120px;height:120px;">
                    <div class="student-name-gradient">
                        <?= htmlspecialchars($presidentInfo['first_name'] . " " . $presidentInfo['last_name']); ?>
                    </div>
                    <div class="student-name-underline"></div>
                </div>
                <div class="row justify-content-center">
                    <div class="col-md-6 text-start">
                        <div class="mb-2"><strong>GPA:</strong> <span class="text-muted"><?= htmlspecialchars($presidentInfo['gpa']); ?></span></div>
                        <div class="mb-2"><strong>CGPA:</strong> <span class="text-muted"><?= htmlspecialchars($presidentInfo['cgpa']); ?></span></div>
                        <div class="mb-2"><strong>Doğum Yeri:</strong> <span class="text-muted"><?= htmlspecialchars($presidentInfo['birth_place']); ?></span></div>
                        <div class="mb-2"><strong>Doğum Tarihi:</strong> <span class="text-muted"><?= htmlspecialchars($presidentInfo['birth_date']); ?></span></div>
                    </div>
                    <div class="col-md-6 text-start">
                        <div class="mb-2"><strong>Cinsiyet:</strong> <span class="text-muted"><?= htmlspecialchars($presidentInfo['gender']); ?></span></div>
                        <div class="mb-2"><strong>Telefon:</strong> <span class="text-muted"><?= htmlspecialchars($presidentInfo['phone_number']); ?></span></div>
                        <div class="mb-2"><strong>Öğrenci No:</strong> <span class="text-muted"><?= htmlspecialchars($presidentInfo['student_number']); ?></span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Kulüp ve İstatistik Kartları -->
<div class="row justify-content-center mb-4">
    <div class="col-md-8">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card shadow-lg border-0 p-4 text-center" style="border-radius: 22px;">
                    <div class="card-body">
                        <h5 class="fw-bold mb-2 student-name-gradient"><i class="bi bi-building"></i> Kulüp Adı</h5>
                        <div class="student-name-underline mb-2"></div>
                        <p class="fw-bold fs-5"><?= htmlspecialchars($clubName); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-lg border-0 p-4 text-center" style="border-radius: 22px;">
                    <div class="card-body">
                        <h5 class="fw-bold mb-2 student-name-gradient"><i class="bi bi-people"></i> Toplam Üye</h5>
                        <div class="student-name-underline mb-2"></div>
                        <p class="display-6 fw-bold text-success"><?= $totalMembers; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-lg border-0 p-4 text-center" style="border-radius: 22px;">
                    <div class="card-body">
                        <h5 class="fw-bold mb-2 student-name-gradient"><i class="bi bi-hourglass-split"></i> Bekleyen Başvuru</h5>
                        <div class="student-name-underline mb-2"></div>
                        <p class="display-6 fw-bold text-warning"><?= $pendingMembers; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-lg border-0 p-4 text-center" style="border-radius: 22px;">
                    <div class="card-body">
                        <h5 class="fw-bold mb-2 student-name-gradient"><i class="bi bi-calendar-event"></i> Mevcut Etkinlik</h5>
                        <div class="student-name-underline mb-2"></div>
                        <p class="display-6 fw-bold text-info"><?= $eventCount; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
    <?php if ($role == 'manager'): ?>
<div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center border-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Toplam Öğrenci</h5>
                    <p class="display-6 fw-bold text-primary"><?php echo $totalStudents; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Kulüp Başkanı</h5>
                    <p class="display-6 fw-bold text-success"><?php echo $totalPresidents; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-info mb-3">
                <div class="card-body">
                    <h5 class="card-title">Toplam Kulüp</h5>
                    <p class="display-6 fw-bold text-info"><?php echo $totalClubs; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Kulüp Üyesi Öğrenci</h5>
                    <p class="display-6 fw-bold text-warning"><?php echo $studentsWithClub; ?></p>
                </div>
            </div>
        </div>
        <?php if ($role == 'manager'): ?>
        <div class="col-md-3">
            <div class="card text-center border-secondary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Toplam Üyelik</h5>
                    <p class="display-6 fw-bold text-secondary"><?php echo $totalMemberships; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-dark mb-3">
                <div class="card-body">
                    <h5 class="card-title">Toplam Etkinlik</h5>
                    <p class="display-6 fw-bold text-dark"><?php echo $totalEvents; ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

        
<?php if ($role == 'developer'): ?>
<div class="container mt-5">
    <div class="row justify-content-center mb-4">
        <!-- Yeni Kulüp Yarat Kartı (ÜSTTE) -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-lg border-0 p-4" style="border-radius: 22px;">
                <h5 class="fw-bold mb-3 student-name-gradient"><i class="bi bi-plus-circle"></i> Yeni Kulüp Yarat</h5>
                <div class="student-name-underline"></div>
                <form action="create_club.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="clubName" class="form-label">Kulüp Adı</label>
                        <input type="text" class="form-control" id="clubName" name="clubName" required>
                    </div>
                    <div class="mb-3">
                        <label for="clubDescription" class="form-label">Açıklama</label>
                        <textarea class="form-control" id="clubDescription" name="clubDescription" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="clubLogo" class="form-label">Logo Yükle</label>
                        <input type="file" class="form-control" id="clubLogo" name="clubLogo" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label for="clubPresident">Kulüp Başkanı Seçin:</label>
                        <select name="clubPresident" id="clubPresident" class="form-control mb-3" required>
                            <?php foreach ($presidents as $president) { ?>
                                <option value="<?= $president['user_id']; ?>"><?= $president['ad']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Kulübü Yarat</button>
                </form>
            </div>
        </div>
        <!-- Kullanıcı Ekleme Kartı (ALTA ALINDI) -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-lg border-0 p-4" style="border-radius: 22px;">
                <h5 class="fw-bold mb-3 student-name-gradient"><i class="bi bi-person-plus"></i> Kullanıcı Ekle</h5>
                <div class="student-name-underline"></div>
                <form id="addUserForm" action="../backend/add_user.php" method="POST">
                    <div class="mb-3">
                        <label for="userEmail" class="form-label">E-posta</label>
                        <input type="email" class="form-control" id="userEmail" name="userEmail" required>
                    </div>
                    <div class="mb-3">
                        <label for="userRole" class="form-label">Rol Seç</label>
                        <select class="form-control" id="userRole" name="userRole" required>
                            <option value="student">Öğrenci</option>
                            <option value="club_president">Kulüp Başkanı</option>
                            <option value="manager">Yönetici</option>
                            <option value="developer">Geliştirici</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Kullanıcı Ekle</button>
                </form>
                <div id="messageBox" class="mt-3"></div>
            </div>
        </div>
    </div>

    <!-- Öğrenci Bilgileri Düzenle Card'ı -->
    <div class="row mb-3 justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0 p-4" style="border-radius: 22px;">
                <h5 class="fw-bold mb-3 student-name-gradient"><i class="bi bi-pencil-square"></i> Öğrenci Bilgilerini Düzenle</h5>
                <div class="student-name-underline"></div>
                <div class="card-body">
                    <div class="form-group mb-4">
                        <label for="studentSelect">Öğrenci Seçin:</label>
                        <select id="studentSelect" class="form-select">
                            <option value="">Öğrenci Seçin</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student['user_id']; ?>"
                                    data-first-name="<?php echo htmlspecialchars($student['first_name']); ?>"
                                    data-last-name="<?php echo htmlspecialchars($student['last_name']); ?>"
                                    data-gpa="<?php echo htmlspecialchars($student['gpa']); ?>"
                                    data-cgpa="<?php echo htmlspecialchars($student['cgpa']); ?>"
                                    data-birth-place="<?php echo htmlspecialchars($student['birth_place']); ?>"
                                    data-birth-date="<?php echo htmlspecialchars($student['birth_date']); ?>"
                                    data-gender="<?php echo htmlspecialchars($student['gender']); ?>"
                                    data-phone-number="<?php echo htmlspecialchars($student['phone_number']); ?>"
                                    data-student-number="<?php echo htmlspecialchars($student['student_number']); ?>"
                                >
                                    <?php 
                                        $fullName = $student['first_name'] && $student['last_name'] ? $student['first_name'] . " " . $student['last_name'] : 'Bilinmeyen';
                                    ?>
                                    <?php echo htmlspecialchars($fullName); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="firstName" class="form-label">Ad:</label>
                        <input type="text" class="form-control" id="firstName" name="first_name" >
                    </div>
                    <div class="mb-3">
                        <label for="lastName" class="form-label">Soyad:</label>
                        <input type="text" class="form-control" id="lastName" name="last_name" >
                    </div>
                    <div class="mb-3">
                        <label for="gpa" class="form-label">GPA:</label>
                        <input type="text" class="form-control" id="gpa" name="gpa" >
                    </div>
                    <div class="mb-3">
                        <label for="cgpa" class="form-label">CGPA:</label>
                        <input type="text" class="form-control" id="cgpa" name="cgpa" >
                    </div>
                    <div class="mb-3">
                        <label for="birthPlace" class="form-label">Doğum Yeri:</label>
                        <input type="text" class="form-control" id="birthPlace" name="birth_place" >
                    </div>
                    <div class="mb-3">
                        <label for="birthDate" class="form-label">Doğum Tarihi:</label>
                        <input type="date" class="form-control" id="birthDate" name="birth_date" >
                    </div>
                    <div class="mb-3">
                        <label for="gender" class="form-label">Cinsiyet:</label>
                        <select class="form-select" id="gender" name="gender" required>
                            <option value="">Seçiniz</option>
                            <option value="erkek">Erkek</option>
                            <option value="kadın">Kadın</option>
                            <option value="diğer">Diğer</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Telefon Numarası:</label>
                        <input type="text" class="form-control" id="phoneNumber" name="phone_number" pattern="[0-9]{12}" placeholder="10 haneli telefon numarası" required>
                    </div>
                    <div class="mb-3">
                        <label for="studentNumber" class="form-label">Öğrenci Numarası:</label>
                        <input type="text" class="form-control" id="studentNumber" name="student_number" required>
                    </div>
                    <button id="saveButton" class="btn btn-primary">Kaydet</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Kulüp Başkanlarını Düzenle Card'ı -->
    <div class="row mb-3 justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0 p-4" style="border-radius: 22px;">
                <h5 class="fw-bold mb-3 student-name-gradient"><i class="bi bi-person-badge"></i> Kulüp Başkanlarını Düzenle</h5>
                <div class="student-name-underline"></div>
                <div class="card-body">
                    <div class="form-group mb-4">
                        <label for="presidentSelect">Başkan Seçin:</label>
                        <select id="presidentSelect" class="form-select">
                            <option value="">Başkan Seçin</option>
                            <?php foreach ($presidents as $president): ?>
                                <option value="<?php echo $president['user_id']; ?>"
                                    data-ad="<?php echo htmlspecialchars($president['ad']); ?>"
                                    data-soyad="<?php echo htmlspecialchars($president['soyad']); ?>"
                                >
                                    <?php 
                                        $fullName = $president['ad'] && $president['soyad'] ? $president['ad'] . " " . $president['soyad'] : 'Bilinmeyen';
                                    ?>
                                    <?php echo htmlspecialchars($fullName); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="ad" class="form-label">Ad:</label>
                        <input type="text" class="form-control" id="ad" name="ad" >
                    </div>
                    <div class="mb-3">
                        <label for="soyad" class="form-label">Soyad:</label>
                        <input type="text" class="form-control" id="soyad" name="soyad" >
                    </div>
                    <button id="presidentsaveButton" class="btn btn-primary">Kaydet</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Aktivite Merkezi Sorumlusu Düzenle Card'ı -->
    <div class="row mb-3 justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0 p-4" style="border-radius: 22px;">
                <h5 class="fw-bold mb-3 student-name-gradient"><i class="bi bi-person-gear"></i> Aktivite Merkezi Sorumlusu Düzenle</h5>
                <div class="student-name-underline"></div>
                <div class="card-body">
                    <div class="form-group mb-4">
                        <label for="managerSelect">Aktivite Merkezi Sorumlusu Seçin:</label>
                        <select id="managerSelect" class="form-select">
                            <option value="">Aktivite Merkezi Sorumlusu Seçin</option>
                            <?php foreach ($managers as $manager): ?>
                                <option value="<?php echo $manager['user_id']; ?>"
                                    data-managerad="<?php echo htmlspecialchars($manager['ad']); ?>"
                                    data-managersoyad="<?php echo htmlspecialchars($manager['soyad']); ?>"
                                >
                                    <?php 
                                        $fullName = $manager['ad'] && $manager['soyad'] ? $manager['ad'] . " " . $manager['soyad'] : 'Bilinmeyen';
                                    ?>
                                    <?php echo htmlspecialchars($fullName); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="ad" class="form-label">Ad:</label>
                        <input type="text" class="form-control" id="managerad" name="managerad" >
                    </div>
                    <div class="mb-3">
                        <label for="soyad" class="form-label">Soyad:</label>
                        <input type="text" class="form-control" id="managersoyad" name="managersoyad" >
                    </div>
                    <button id="managersaveButton" class="btn btn-primary">Kaydet</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php if ($role == 'manager'): ?>
    <div class="row mb-4">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Kulüp Üyelikleri Grafiği</h5>
                </div>
                <div class="card-body">
                    <canvas id="membershipChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Etkinlik Başvuruları Grafiği</h5>
                </div>
                <div class="card-body">
                    <canvas id="eventChart"></canvas>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
               
                </div>
             
            </main>
                            
        </div>
      
    </div>


    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php if ($role == 'manager'): ?>
<script>
    const clubLabels = <?php echo json_encode(array_column($clubMemberships, 'name')); ?>;
    const clubData = <?php echo json_encode(array_column($clubMemberships, 'member_count')); ?>;
    const eventLabels = <?php echo json_encode(array_column($eventApplications, 'event_name')); ?>;
    const eventData = <?php echo json_encode(array_column($eventApplications, 'app_count')); ?>;
</script>
<?php else: ?>
<script>
    const clubLabels = [];
    const clubData = [];
    const eventLabels = [];
    const eventData = [];
</script>
<?php endif; ?>

<script>
$(document).ready(function () {
    $('#addUserForm').on('submit', function (e) {
    e.preventDefault();

    const formData = $(this).serialize(); // Form verileri

    $.ajax({
        url: '../backend/add_user.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function (response) {
            const box = $('#messageBox');
            if (response.success) {
                box.html(`<div class="alert alert-success">${response.message}</div>`);
                $('#addUserForm')[0].reset(); // Formu temizle
            } else {
                box.html(`<div class="alert alert-danger">${response.message}</div>`);
            }
        },
        error: function (xhr, status, error) {
            $('#messageBox').html(`<div class="alert alert-danger">Bir hata oluştu: ${error}</div>`);
        }
    });
});


document.getElementById('saveButton').addEventListener('click', function () {
    let user_id = document.getElementById('studentSelect').value;
    let first_name = document.getElementById('firstName').value;
    let last_name = document.getElementById('lastName').value;
    let gpa = document.getElementById('gpa').value;
    let cgpa = document.getElementById('cgpa').value;
    let birth_place = document.getElementById('birthPlace').value;
    let birth_date = document.getElementById('birthDate').value;
    let gender = document.getElementById('gender').value;
    let phone_number = document.getElementById('phoneNumber').value;
    let student_number = document.getElementById('studentNumber').value;

    // Öğrenci seçilip seçilmediğini kontrol et
    if (!user_id) {
        alert("Lütfen bir öğrenci seçin!");
        return;
    }

    let formData = new FormData();
    formData.append("user_id", user_id);
    formData.append("first_name", first_name);
    formData.append("last_name", last_name);
    formData.append("gpa", gpa);
    formData.append("cgpa", cgpa);
    formData.append("birth_place", birth_place);
    formData.append("birth_date", birth_date);
    formData.append("gender", gender);
    formData.append("phone_number", phone_number);
    formData.append("student_number", student_number);

    // POST isteğini yap
    fetch("../backend/update_student.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            alert("Öğrenci bilgileri başarıyla güncellendi!");
        } else {
            alert("Hata: " + data.message);
        }
    })
    .catch(error => console.error("Hata oluştu:", error));
});

        // Öğrenci seçildiğinde bilgileri inputlara aktar
    document.getElementById('studentSelect').addEventListener('change', function() {
        var selectedOption = this.options[this.selectedIndex];

        // Eğer bir öğrenci seçildiyse
        if (selectedOption.value !== "") {
            // Seçilen öğrencinin verilerini al
            var firstName = selectedOption.getAttribute('data-first-name');
            var lastName = selectedOption.getAttribute('data-last-name');
            var gpa = selectedOption.getAttribute('data-gpa');
            var cgpa = selectedOption.getAttribute('data-cgpa');
            var birthPlace = selectedOption.getAttribute('data-birth-place');
            var birthDate = selectedOption.getAttribute('data-birth-date');
            var gender = selectedOption.getAttribute('data-gender');
            var phoneNumber = selectedOption.getAttribute('data-phone-number');
            var studentNumber = selectedOption.getAttribute('data-student-number');
            // Inputları doldur
            document.getElementById('firstName').value = firstName;
            document.getElementById('lastName').value = lastName;
            document.getElementById('gpa').value = gpa;
            document.getElementById('cgpa').value = cgpa;
            document.getElementById('birthPlace').value = birthPlace;
            document.getElementById('birthDate').value = birthDate;
            document.getElementById('gender').value = gender;
            document.getElementById('phoneNumber').value = phoneNumber;
            document.getElementById('studentNumber').value = studentNumber;
        }
    });

        // Öğrenci seçildiğinde bilgileri inputlara aktar
    document.getElementById('presidentSelect').addEventListener('change', function() {
        var selectedOption = this.options[this.selectedIndex];

        // Eğer bir başkan seçildiyse
        if (selectedOption.value !== "") {
            // Seçilen başkan verilerini al
            var ad = selectedOption.getAttribute('data-ad');
            var soyad = selectedOption.getAttribute('data-soyad');

            // Inputları doldur
            document.getElementById('ad').value = ad;
            document.getElementById('soyad').value = soyad;
        }
    });


    document.getElementById('presidentsaveButton').addEventListener('click', function () {
    let user_id = document.getElementById('presidentSelect').value;
    let ad = document.getElementById('ad').value;
    let soyad = document.getElementById('soyad').value;

    if (!user_id) {
        alert("Lütfen bir Başkan seçin!");
        return;
    }

    let formData = new FormData();
    formData.append("user_id", user_id);
    formData.append("ad", ad);
    formData.append("soyad", soyad);


    fetch("../backend/update_president.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            alert("Başkan bilgileri başarıyla güncellendi!");
        } else {
            alert("Hata: " + data.message);
        }
    })
    .catch(error => console.error("Hata oluştu:", error));
});


        // Aktivite Merkezi Sorumlusu seçildiğinde bilgileri inputlara aktar
        document.getElementById('managerSelect').addEventListener('change', function() {
        var selectedOption = this.options[this.selectedIndex];

        // Eğer bir öğrenci seçildiyse
        if (selectedOption.value !== "") {
            // Seçilen öğrencinin verilerini al
            var ad = selectedOption.getAttribute('data-managerad');
            var soyad = selectedOption.getAttribute('data-managersoyad');

            // Inputları doldur
            document.getElementById('managerad').value = ad;
            document.getElementById('managersoyad').value = soyad;
        }
    });


    document.getElementById('managersaveButton').addEventListener('click', function () {
    let user_id = document.getElementById('managerSelect').value;
    let ad = document.getElementById('managerad').value;
    let soyad = document.getElementById('managersoyad').value;

    if (!user_id) {
        alert("Lütfen bir Aktivite Merkezi Sorumlusu seçin!");
        return;
    }

    let formData = new FormData();
    formData.append("user_id", user_id);
    formData.append("ad", ad);
    formData.append("soyad", soyad);


    fetch("../backend/update_manager.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            alert("Aktivite Merkezi Sorumlusu bilgileri başarıyla güncellendi!");
        } else {
            alert("Hata: " + data.message);
        }
    })
    .catch(error => console.error("Hata oluştu:", error));
});
});

<?php if ($role == 'manager'): ?>
<script>
    const clubLabels = <?php echo json_encode(array_column($clubMemberships, 'name')); ?>;
    const clubData = <?php echo json_encode(array_column($clubMemberships, 'member_count')); ?>;
    const eventLabels = <?php echo json_encode(array_column($eventApplications, 'event_name')); ?>;
    const eventData = <?php echo json_encode(array_column($eventApplications, 'app_count')); ?>;
</script>
<?php else: ?>
<script>
    const clubLabels = [];
    const clubData = [];
    const eventLabels = [];
    const eventData = [];
</script>
<?php endif; ?>
<script>
$(document).ready(function () {
    // Kulüp Üyelikleri Grafiği
    if (document.getElementById('membershipChart') && clubLabels.length > 0) {
        new Chart(document.getElementById('membershipChart'), {
            type: 'bar',
            data: {
                labels: clubLabels,
                datasets: [{
                    label: 'Üye Sayısı',
                    data: clubData,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)'
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } }
            }
        });
    }
    // Etkinlik Başvuruları Grafiği
    if (document.getElementById('eventChart') && eventLabels.length > 0) {
        new Chart(document.getElementById('eventChart'), {
            type: 'pie',
            data: {
                labels: eventLabels,
                datasets: [{
                    label: 'Başvuru Sayısı',
                    data: eventData,
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(153, 102, 255, 0.6)'
                    ]
                }]
            },
            options: {
                responsive: true
            }
        });
    }
});
</script>
    </script>
</body>
</html>
