<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kulüpler</title>
        <!-- Harici CSS dosyası -->
        <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../public/giris.php");
    exit();
}

require_once '../backend/database.php'; // Database sınıfı tanımlı olmalı
$db = new Database();


$currentPage = basename($_SERVER['PHP_SELF']);
$excludePages = ['giris.php'];

if (!in_array($currentPage, $excludePages)) {
    $_SESSION['previous_page'] = $_SERVER['REQUEST_URI'];
}
$role = $_SESSION['role']; // Kullanıcının rolünü al
// Sadece "manager" veya "developer" rolüne izin ver
if ($role !== 'student' && $role !== 'developer') {
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
<?php 

?>
    <style>
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 8px 32px rgba(13, 30, 77, 0.22), 0 2px 8px rgba(13,110,253,0.10);
        }
        .card.shadow-lg {
            box-shadow: 0 8px 32px rgba(13, 30, 77, 0.22), 0 2px 8px rgba(13,110,253,0.10) !important;
        }
        footer {
            background-color: #212529;
            color: white;
            padding: 10px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        .club-logo {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #e9ecef; /* İsteğe bağlı çerçeve */
        }
        .btn-custom {
            width: 100px;
            font-size: 14px;
            transition: background-color 0.3s, transform 0.2s;
        }
        .btn-custom:hover {
            transform: scale(1.05);
        }
        table {
            margin-top: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            background-color: #fff;
        }
        thead {
            background-color: #0d6efd;
            color: white;
        }
        th {
            border: none;
            font-weight: 600;
        }
        td {
            vertical-align: middle;
            border: none;
        }
        tr:hover {
            background-color: #f1f1f1;
            border-left: 4px solid #0d6efd;
            border-right: 4px solid #0d6efd;
            border-radius: 8px;
        }
        .status {
            font-weight: bold;
            color: #dc3545;
        }
        .btn-reject {
            background-color: #dc3545;
            color: white;
        }
        .btn-reject:hover {
            background-color: #c82333;
        }
        .btn-apply {
            background-color: #28a745;
            color: white;
        }
        .btn-apply:hover {
            background-color: #218838;
        }
        .kulup-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 2.7rem;
            text-align: center;
            background: linear-gradient(90deg, #0d6efd 60%, #2E49A5 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: 2px;
            margin-bottom: 0.5rem;
            position: relative;
            display: inline-block;
        }
        .kulup-title .bi {
            font-size: 2.1rem;
            vertical-align: middle;
            margin-right: 10px;
            color: #0d6efd;
            filter: drop-shadow(0 2px 8px rgba(13,110,253,0.12));
        }
        .kulup-title-underline {
            display: block;
            width: 110px;
            height: 5px;
            margin: 0.5rem auto 1.5rem auto;
            border-radius: 3px;
            background: linear-gradient(90deg, #0d6efd 60%, #2E49A5 100%);
            opacity: 0.8;
        }
        .kulup-hero {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-top: 32px;
            margin-bottom: 32px;
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
        .kulup-title-modern {
            font-family: 'Poppins', sans-serif;
            font-weight: 800;
            font-size: 3rem;
            background: linear-gradient(90deg, #0d6efd 60%, #2E49A5 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: 2px;
            display: inline-block;
            margin-bottom: 0.2rem;
            text-shadow: 0 2px 12px rgba(13,110,253,0.08);
        }
        .kulup-title-modern .bi {
            font-size: 2.2rem;
            vertical-align: middle;
            margin-right: 12px;
            color: #0d6efd;
            filter: drop-shadow(0 2px 8px rgba(13,110,253,0.12));
        }
        .kulup-title-underline-modern {
            display: block;
            width: 120px;
            height: 6px;
            margin: 0.5rem auto 0 auto;
            border-radius: 3px;
            background: linear-gradient(90deg, #0d6efd 60%, #2E49A5 100%);
            opacity: 0.85;
            box-shadow: 0 2px 8px #0d6efd33;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php include_once '../includes/sidebar.php'; ?>
        <?php include_once '../includes/mobil_menu.php' ?>

        <main class="col-md-9 col-lg-10 p-4">
             <?php include '../includes/ai_chat_widget.php'; ?>
            <div class="card shadow-lg rounded-4 border-0 mb-5" style="background: #fff;">
        <div class="card-body">
            <div class="kulup-hero position-relative">
                <div class="kulup-hero-bg"></div>
                <span class="kulup-title-modern">
                    <i class="bi bi-people-fill"></i>Kulüpler
                </span>
                <span class="kulup-title-underline-modern"></span>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Logo</th>
                            <th>Kulüp Adı</th>
                            <th>Açıklama</th>
                            <th>Başvuru</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $userId = $_SESSION['id'];
                        $clubs = $db->getAllRecords("SELECT * FROM clubs");
                        foreach ($clubs as $club) {
                            $clubId = $club['id'];
                            $membership = $db->getRow("SELECT status FROM clubmembers WHERE user_id = :user_id AND club_id = :club_id", [
                                'user_id' => $userId,
                                'club_id' => $clubId
                            ]);

                            $buttonText = "Başvur";
                            $disabled = "";
                            $buttonClass = "btn-primary btn-apply";
                            $showLeaveButton = false;

                            if ($membership) {
                                if ($membership['status'] === 'approved') {
                                    $buttonText = "Üyesiniz";
                                    $disabled = "disabled";
                                    $buttonClass = "btn-success";
                                    $showLeaveButton = true;
                                } elseif ($membership['status'] === 'pending') {
                                    $buttonText = "Beklemede";
                                    $disabled = "disabled";
                                    $buttonClass = "btn-warning";
                                } elseif ($membership['status'] === 'rejected') {
                                    $buttonText = "Reddedildi";
                                    $disabled = "disabled";
                                    $buttonClass = "btn-danger";
                                }
                            }

                            echo "<tr>";
                            echo "<td><img src='../uploads/" . htmlspecialchars($club['logo']) . "' class='club-logo' style='width: 80px;'></td>";
                            echo "<td>" . htmlspecialchars($club['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($club['description']) . "</td>";
                            echo "<td>";
                            echo "<button class='btn $buttonClass' data-club-id='" . $club['id'] . "' $disabled>$buttonText</button>";
                            if ($showLeaveButton) {
                                echo " <button class='btn btn-danger leave-club-btn' data-club-id='" . $club['id'] . "'>Ayrıl</button>";
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
    </div>
</div>

       
    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function toggleApplication(button) {
            var row = button.closest("tr");
            var statusCell = row.querySelector(".status");
            if (statusCell.innerHTML === "Başvurulmadı") {
                statusCell.innerHTML = "Değerlendiriliyor";
                button.innerHTML = "Reddet";
                button.classList.add("btn-reject");
                button.classList.remove("btn-apply");
            } else if (statusCell.innerHTML === "Değerlendiriliyor") {
                statusCell.innerHTML = "Başvurulmadı";
                button.innerHTML = "Başvur";
                button.classList.add("btn-apply");
                button.classList.remove("btn-reject");
            }
        }
    </script>

    <script>
// SADECE BAŞVURU BUTONU İÇİN
$(document).on('click', '.btn-apply', function(e) {
    e.preventDefault();
    var clubId = $(this).data('club-id');
    $.post('../backend/apply.php', { club_id: clubId }, function(data) {
        alert(data.message);
        location.reload();
    }, 'json');
});

// SADECE AYRIL BUTONU İÇİN
$(document).on('click', '.leave-club-btn', function(e) {
    e.preventDefault();
    var clubId = $(this).data('club-id');
    if (confirm('Bu kulüpten ayrılmak istediğinize emin misiniz?')) {
        $.ajax({
            url: '../backend/leave_club.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ club_id: clubId }),
            success: function(data) {
                if (data.success) {
                    alert('Kulüpten ayrıldınız.');
                    location.reload();
                } else {
                    alert('Bir hata oluştu: ' + data.message);
                }
            },
            error: function() {
                alert('İstek gönderilirken hata oluştu.');
            }
        });
    }
});


</script>
</body>
</html>
