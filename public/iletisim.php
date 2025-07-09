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
$role = $_SESSION['role'];
if ($role !== 'club_president' && $role !== 'manager' && $role !== 'developer') {
    if (isset($_SESSION['previous_page'])) {
        header("Location: " . $_SESSION['previous_page']);
    } else {
        header("Location: ../index.php");
    }
    exit();
}

require_once '../backend/database.php';
$db = new Database();
$db->getConnection();

// Kullanıcının rütbesini çek
$user_id = $_SESSION['id'];
$myManager = $db->getRow("SELECT * FROM managers WHERE user_id = :user_id", ['user_id' => $user_id]);
$isRutbeli = ($myManager && $myManager['status'] === 'Rütbeli');

// Manager ekleme
if ($isRutbeli && isset($_POST['add_manager'])) {
    // Önce users tablosuna ekle
    $userData = [
        'email' => $_POST['email'],
        'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
        'role' => 'manager'
    ];
    $userId = $db->insertData('users', $userData);

    // Sonra managers tablosuna ekle
    $managerData = [
        'user_id' => $userId,
        'ad' => $_POST['ad'],
        'soyad' => $_POST['soyad'],
        'phone_number' => $_POST['phone_number'],
        'status' => $_POST['status']
    ];
    $db->insertData('managers', $managerData);
    header("Location: iletisim.php");
    exit();
}

// Manager silme
if ($isRutbeli && isset($_POST['delete_manager'])) {
    $mudur_id = $_POST['mudur_id'];
    // Önce managers tablosundan, sonra users tablosundan sil
    $manager = $db->getRow("SELECT * FROM managers WHERE mudur_id = :mudur_id", ['mudur_id' => $mudur_id]);
    if ($manager && $manager['status'] === 'Rütbesiz') {
        $db->execute("DELETE FROM managers WHERE mudur_id = :mudur_id", ['mudur_id' => $mudur_id]);
        $db->execute("DELETE FROM users WHERE id = :user_id", ['user_id' => $manager['user_id']]);
    }
    header("Location: iletisim.php");
    exit();
}

// Manager güncelleme (sadece rütbesizler için, e-posta users tablosunda!)
if ($isRutbeli && isset($_POST['update_manager'])) {
    $mudur_id = $_POST['mudur_id'];
    $manager = $db->getRow("SELECT * FROM managers WHERE mudur_id = :mudur_id", ['mudur_id' => $mudur_id]);
    if ($manager && $manager['status'] === 'Rütbesiz') {
        // managers tablosunu güncelle
        $db->updateData(
            'managers',
            [
                'ad' => $_POST['ad'],
                'soyad' => $_POST['soyad'],
                'phone_number' => $_POST['phone_number']
            ],
            'mudur_id = :mudur_id',
            ['mudur_id' => $mudur_id]
        );
        // users tablosunu güncelle (e-posta)
        $db->updateData(
            'users',
            [
                'email' => $_POST['email']
            ],
            'id = :user_id',
            ['user_id' => $manager['user_id']]
        );
    }
    header("Location: iletisim.php");
    exit();
}

// managers ve users tablosunu join ile çekiyoruz
$managers = $db->getAllRecords("
    SELECT m.mudur_id, m.ad, m.soyad, m.phone_number, m.status, u.email
    FROM managers m
    INNER JOIN users u ON m.user_id = u.id
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etkinlik Çizelgesi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .content {
            flex-grow: 1;
            padding: 30px;
            width: 100%;
            overflow-x: hidden;
            transition: transform 0.3s ease;
        }
        .team-member-card {
            margin-bottom: 20px;
            width: 100%;
            max-width: 300px;
        }
        .card-body {
            padding: 15px;
        }
        .card-img-top {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
<div class="d-flex" style="min-height: 100vh;">
<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/mobil_menu.php'?>
    <div class="content d-flex align-items-center justify-content-center w-100" id="content">
         <?php include '../includes/ai_chat_widget.php'; ?>
        <div class="row justify-content-center w-100">
            <div class="col-lg-10">
                <h2 class="text-center mb-4 fw-bold" style="letter-spacing:2px;">Önemli Kişiler</h2>

                <?php if ($isRutbeli): ?>
                <!-- YENİ MANAGER EKLEME FORMU -->
                <div class="card shadow-lg mb-4 border-0 rounded-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3 fw-semibold text-primary"><i class="bi bi-person-plus"></i> Yeni Yönetici Ekle</h5>
                        <form method="POST">
                            <div class="row g-2">
                                <div class="col-md-2">
                                    <input type="text" name="ad" class="form-control rounded-pill" placeholder="Ad" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="soyad" class="form-control rounded-pill" placeholder="Soyad" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="phone_number" class="form-control rounded-pill" placeholder="Telefon" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="email" name="email" class="form-control rounded-pill" placeholder="E-posta" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="password" name="password" class="form-control rounded-pill" placeholder="Şifre" required>
                                </div>
                                <div class="col-md-1">
                                    <select name="status" class="form-select rounded-pill" required>
                                        <option value="Rütbesiz">Rütbesiz</option>
                                        <option value="Rütbeli">Rütbeli</option>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <button type="submit" name="add_manager" class="btn btn-gradient w-100 rounded-pill">Ekle</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <div class="row justify-content-center g-4">
                    <?php if (empty($managers)): ?>
                        <div class="alert alert-warning text-center">Kayıtlı yönetici bulunamadı.</div>
                    <?php else: ?>
                        <?php foreach ($managers as $manager): ?>
                        <div class="col-md-4 d-flex justify-content-center">
                            <div class="card team-member-card shadow border-0 rounded-4 position-relative overflow-hidden">
                                <div class="gradient-bar"></div>
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($manager['ad'].' '.$manager['soyad']) ?>&background=0D8ABC&color=fff&size=150" class="card-img-top rounded-circle mx-auto mt-3 shadow" style="width:100px;height:100px;object-fit:cover;" alt="Member Image">
                                <div class="card-body text-center">
                                    <h5 class="card-title fw-bold"><?= htmlspecialchars($manager['ad'] . ' ' . $manager['soyad']) ?></h5>
                                    <span class="badge <?= $manager['status'] === 'Rütbeli' ? 'bg-gradient-primary' : 'bg-secondary' ?>">
                                        <?= $manager['status'] === 'Rütbeli' ? 'Yönetici (Rütbeli)' : 'Yönetici' ?>
                                    </span>
                                    <div class="mt-2 mb-2">
                                        <div class="text-muted small"><i class="bi bi-phone"></i> <?= htmlspecialchars($manager['phone_number']) ?></div>
                                        <div class="text-muted small"><i class="bi bi-envelope"></i> <?= htmlspecialchars($manager['email']) ?></div>
                                    </div>
                                    <?php if ($isRutbeli && $manager['status'] === 'Rütbesiz'): ?>
                                    <!-- Güncelleme Formu -->
                                    <form method="POST" class="mb-2">
                                        <input type="hidden" name="mudur_id" value="<?= $manager['mudur_id'] ?>">
                                        <input type="text" name="ad" value="<?= htmlspecialchars($manager['ad']) ?>" class="form-control form-control-sm mb-1 rounded-pill" required>
                                        <input type="text" name="soyad" value="<?= htmlspecialchars($manager['soyad']) ?>" class="form-control form-control-sm mb-1 rounded-pill" required>
                                        <input type="text" name="phone_number" value="<?= htmlspecialchars($manager['phone_number']) ?>" class="form-control form-control-sm mb-1 rounded-pill" required>
                                        <input type="email" name="email" value="<?= htmlspecialchars($manager['email']) ?>" class="form-control form-control-sm mb-1 rounded-pill" required>
                                        <button type="submit" name="update_manager" class="btn btn-outline-primary btn-sm w-100 rounded-pill">Güncelle</button>
                                    </form>
                                    <!-- Silme Formu -->
                                    <form method="POST">
                                        <input type="hidden" name="mudur_id" value="<?= $manager['mudur_id'] ?>">
                                        <button type="submit" name="delete_manager" class="btn btn-outline-danger btn-sm w-100 rounded-pill" onclick="return confirm('Silmek istediğinize emin misiniz?')">Sil</button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include_once '../includes/right_top_menu.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<style>
.btn-gradient {
    background: linear-gradient(90deg, #0d8abc 0%, #00c6ff 100%);
    color: #fff;
    border: none;
    transition: background 0.3s;
}
.btn-gradient:hover {
    background: linear-gradient(90deg, #00c6ff 0%, #0d8abc 100%);
    color: #fff;
}
.bg-gradient-primary {
    background: linear-gradient(90deg, #0d8abc 0%, #00c6ff 100%) !important;
    color: #fff !important;
}
.team-member-card {
    transition: transform 0.2s, box-shadow 0.2s;
    border-radius: 1.5rem !important;
    background: #fff;
    box-shadow: 0 4px 24px rgba(13,138,188,0.08);
}
.team-member-card:hover {
    transform: translateY(-8px) scale(1.03);
    box-shadow: 0 8px 32px rgba(13,138,188,0.18);
}
.gradient-bar {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 8px;
    background: linear-gradient(90deg, #0d8abc 0%, #00c6ff 100%);
}
</style>