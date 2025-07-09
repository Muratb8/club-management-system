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
if ($role !== 'manager' && $role !== 'developer') {
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
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kulüp Üye Listesi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .kulupbilgi-main-card {
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
        .kulupbilgi-hero {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            margin-top: 8px;
            position: relative;
            z-index: 1;
        }
        .kulupbilgi-hero-bg {
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
        .kulupbilgi-title-modern-main {
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
        .kulupbilgi-title-underline-modern {
            display: block;
            width: 120px;
            height: 6px;
            margin: 0.5rem auto 1.5rem auto;
            border-radius: 3px;
            background: linear-gradient(90deg, #0d6efd 60%, #2E49A5 100%);
            opacity: 0.85;
            box-shadow: 0 2px 8px #0d6efd33;
        }
        .table-modern thead th {
            background: #f4f6fb !important;
            color: #222 !important;
            font-weight: 600;
            text-align: center;
            vertical-align: middle;
            border-bottom: 2px solid #e9ecef;
            font-size: 1.08rem;
        }
        .table-modern tbody td, .table-modern tbody th {
            vertical-align: middle;
            text-align: center;
            font-size: 1.01rem;
            background: #fcfcfd;
            border-bottom: 1px solid #f0f0f0;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        <?php include '../includes/mobil_menu.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
         <?php include '../includes/ai_chat_widget.php'; ?>
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">  </div>

            <div class="kulupbilgi-main-card">
                <div class="kulupbilgi-hero position-relative">
                    <div class="kulupbilgi-hero-bg"></div>
                    <span class="kulupbilgi-title-modern-main">
                        <i class="bi bi-people-fill"></i> Kulüp Bilgileri
                    </span>
                    <span class="kulupbilgi-title-underline-modern"></span>
                </div>

                <!-- Kulüp Üye Sayıları -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-bar-chart"></i> Kulüp Üye Sayıları</h5>
                        <div class="row">
                            <?php
                            $clubs = $db->getAllRecords("SELECT id, name FROM clubs");
                            $colors = ['primary', 'success', 'info', 'warning', 'danger', 'secondary', 'dark'];
                            $colorIndex = 0;
                            foreach ($clubs as $club):
                                $member_count = $db->getRow(
                                    "SELECT COUNT(*) as cnt FROM clubmembers WHERE club_id = :club_id AND status = 'approved'",
                                    ['club_id' => $club['id']]
                                )['cnt'];
                                $color = $colors[$colorIndex % count($colors)];
                                $colorIndex++;
                            ?>
                            <div class="col-md-4 mb-3">
                                <div class="card text-bg-<?= $color ?>">
                                    <div class="card-body text-center">
                                        <h5 class="card-title"><?= htmlspecialchars($club['name']) ?></h5>
                                        <p class="card-text display-6"><?= $member_count ?> Üye</p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Üye Listesi -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-person-badge"></i> Kulüp Başkanları Listesi</h5>
                        <div class="table-responsive">
                            <table class="table table-modern align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Ad Soyad</th>
                                        <th>Kulüp</th>
                                        <th>Telefon</th>
                                        <th>E-posta</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $presidents = $db->getAllRecords(
                                        "SELECT s.first_name, s.last_name, u.email, s.phone_number, c.name as club_name
                                        FROM clubpresidents cp
                                        INNER JOIN students s ON cp.student_number = s.student_number
                                        INNER JOIN users u ON s.user_id = u.id
                                        INNER JOIN clubs c ON cp.kulup_id = c.id
                                        WHERE cp.kulup_id != 0
                                        ORDER BY c.name, s.first_name, s.last_name"
                                    );
                                    $i = 1;
                                    foreach ($presidents as $president): ?>
                                        <tr>
                                            <th scope="row"><?= $i++ ?></th>
                                            <td><?= htmlspecialchars($president['first_name'] . ' ' . $president['last_name']) ?></td>
                                            <td><?= htmlspecialchars($president['club_name']) ?></td>
                                            <td><?= htmlspecialchars($president['phone_number']) ?></td>
                                            <td><?= htmlspecialchars($president['email']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

</body>
</html>