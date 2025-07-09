<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Başvurular</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <?php
session_start();    
if (!isset($_SESSION['id'])) {
    header("Location: ../public/giris.php");
    exit();
}
    include '../backend/database.php';  // veya bağlantının olduğu dosya yolu
    $db = new Database();

$currentPage = basename($_SERVER['PHP_SELF']);
$excludePages = ['giris.php'];

if (!in_array($currentPage, $excludePages)) {
    $_SESSION['previous_page'] = $_SERVER['REQUEST_URI'];
}
$role = $_SESSION['role']; // Kullanıcının rolünü al
// Sadece "club_president" rolüne izin ver
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
?>

    <!-- Harici CSS dosyası -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap Icons (isteğe bağlı) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
    .modern-table-container {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        padding: 32px 24px;
        margin-top: 32px;
    }
    .table-modern thead th {
        background: #f4f6fb;
        font-weight: 600;
        text-align: center;
        vertical-align: middle;
        border-bottom: 2px solid #e9ecef;
        font-size: 1.08rem;
    }
    .table-modern tbody td {
        vertical-align: middle;
        text-align: center;
        font-size: 1.01rem;
        background: #fcfcfd;
        border-bottom: 1px solid #f0f0f0;
    }
    .table-modern tbody tr {
        transition: box-shadow 0.2s, transform 0.2s;
    }
    .table-modern tbody tr:hover {
        background: #f1f7ff;
        box-shadow: 0 2px 12px rgba(0,123,255,0.06);
        transform: scale(1.01);
    }
    .badge-status {
        font-size: 0.95em;
        padding: 0.5em 1em;
        border-radius: 1em;
    }
    .badge-pending { background: #ffe69c; color: #856404; }
    .badge-approved { background: #b6f5c6; color: #146c43; }
    .badge-rejected { background: #f8d7da; color: #842029; }
    .btn-modern {
        min-width: 40px;
        margin: 2px;
        font-size: 1.1em;
        border-radius: 8px;
        transition: background 0.15s;
    }
    .btn-modern i {
        vertical-align: middle;
    }
.basvuru-main-card {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 8px 32px rgba(13, 30, 77, 0.22), 0 2px 8px rgba(13,110,253,0.10);
    padding: 32px 24px;
    margin-bottom: 40px;
    margin-top: 32px;
    max-width: 1200px; /* Eski: 800px */
    margin-left: auto;
    margin-right: auto;
}
.basvuru-hero {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin-bottom: 24px;
    margin-top: 8px;
    position: relative;
    z-index: 1;
}
.basvuru-hero-bg {
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
.basvuru-title-modern-main {
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
.basvuru-title-underline-modern {
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
</style>

</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        <main class="col-md-9 col-lg-10 p-4">
                               <?php include '../includes/ai_chat_widget.php'; ?>
            <div class="basvuru-main-card">
                <div class="basvuru-hero position-relative">
                    <div class="basvuru-hero-bg"></div>
                    <span class="basvuru-title-modern-main">
                        <i class="bi bi-person-lines-fill"></i> Başvurular
                    </span>
                    <span class="basvuru-title-underline-modern"></span>
                </div>
                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Öğrenci No</th> <!-- Yeni sütun -->
                                <th>İsim</th>
                                <th>Soyisim</th>
                                <th>Cinsiyet</th>
                                <th>Telefon</th>
                                <th>GPA</th>
                                <th>CGPA</th>
                                <th>Başvuru Durumu</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="basvuruTablosu">
                            <?php
                            $user_id = $_SESSION['id'];
                            $kulupQuery = "SELECT kulup_id FROM clubpresidents WHERE user_id = ?";
                            $kulupResult = $db->getRow($kulupQuery, [$user_id]);
                            
                            if ($kulupResult) {
                                $kulup_id = $kulupResult['kulup_id'];
                                $basvuruQuery = "
                                    SELECT cm.id, s.student_number, s.first_name, s.last_name, s.gender, s.phone_number, s.gpa, s.cgpa, cm.status
                                    FROM clubmembers cm
                                    JOIN students s ON cm.user_id = s.user_id
                                    WHERE cm.club_id = ? AND cm.status = 'pending'
                                ";
                                $basvurular = $db->getAllRecords($basvuruQuery, [$kulup_id]);
                                if ($basvurular) {
                                    foreach ($basvurular as $row) {
                                        // Duruma göre badge rengi
                                        $badgeClass = 'badge-pending';
                                        $statusText = 'Beklemede';
                                        if ($row['status'] === 'approved') {
                                            $badgeClass = 'badge-approved';
                                            $statusText = 'Onaylandı';
                                        } elseif ($row['status'] === 'rejected') {
                                            $badgeClass = 'badge-rejected';
                                            $statusText = 'Reddedildi';
                                        }
                                        echo "<tr>
                                                <td>{$row['id']}</td>
                                                <td>{$row['student_number']}</td>
                                                <td>{$row['first_name']}</td>
                                                <td>{$row['last_name']}</td>
                                                <td>{$row['gender']}</td>
                                                <td>{$row['phone_number']}</td>
                                                <td>{$row['gpa']}</td>
                                                <td>{$row['cgpa']}</td>
                                                <td><span class='badge badge-status $badgeClass'>$statusText</span></td>
                                                <td>
                                                    <button class='btn btn-success btn-modern onaylaBtn' data-id='{$row['id']}' title='Onayla'><i class='bi bi-check-circle'></i></button>
                                                    <button class='btn btn-danger btn-modern reddetBtn' data-id='{$row['id']}' title='Reddet'><i class='bi bi-x-circle'></i></button>
                                                </td>
                                            </tr>";
                                    }
                                } else {
                                    echo '<tr><td colspan="10" class="text-center">Başvuru bulunamadı.</td></tr>';
                                }
                            } else {
                                echo '<tr><td colspan="10" class="text-center">Başkan olduğunuz bir kulüp bulunamadı.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>
   
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
<script>
    $(document).on('click', '.onaylaBtn, .reddetBtn', function() {
        var basvuruId = $(this).data('id');
        var action = $(this).hasClass('onaylaBtn') ? 'approved' : 'rejected';

        $.post('../backend/update_basvuru_status.php', { id: basvuruId, status: action }, function(response) {
            var data = JSON.parse(response);
            alert(data.message);
            location.reload();
        });
    });
</script>

</body>
</html>
