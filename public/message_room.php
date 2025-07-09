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
    <?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: giris.php");
    exit();
}
$role = $_SESSION['role']; // Kullanıcının rolünü al

// Veritabanı bağlantısı ve kulüpleri çekme
require_once '../backend/database.php';
$db = new Database();
$db->getConnection();
$clubs = $db->getData('clubs', 'id, name, clubpresident_id');
?>
    <!-- Custom CSS -->
    <style>
        .club-container {
            position: absolute;
            top: 50px;
            left: 320px; /* Biraz daha sağa kaydırıldı */
            display: flex;
            flex-direction: column;
        }
        .club-box {
            background-color: #ffffff;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 15px 60px; /* Dikey küçültüldü, yatay büyütüldü */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, background-color 0.3s;
            font-size: 1.2rem;
            text-align: center;
            cursor: pointer;
        }
        .club-box + .club-box {
            margin-top: 0; /* Aradaki boşluk kaldırıldı */
        }
        .club-box:hover {
            transform: scale(1.05);
            background-color: #f8f9fa;
        }
        .club-box.active {
            background-color: #0d6efd;
            color: white;
        }
        footer {
            background-color: #212529;
            color: white;
            padding: 10px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        .notification-dot {
            position: absolute;
            top: 5px;
            right: 5px;
            width: 20px;
            height: 20px;
            background-color: red;
            color: white;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            line-height: 20px;
            border-radius: 50%;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
        }

        .club-box {
            position: relative; /* Kareyi doğru konumlandırmak için relative konumlandırma */
        }

        .club-box.active::after {
            background-color: #0d6efd; /* Tıklandığında kare görünür hale gelir */
        }

        .selected-box {
            position: absolute;
            width: 200px;
            height: 200px;
            background-color: rgba(0, 123, 255, 0.5);
            border: 2px solid #007bff;
            display: none; /* Başlangıçta görünmez */
            z-index: 10;
            transition: all 0.3s ease-in-out;
        }
        .chat-box {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60%;
            height: 70%;
            background-color: #ffffff;
            border: 2px solid #0d6efd;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            display: none; /* Başlangıçta görünmez */
            overflow: hidden;
        }

        .chat-box-header {
            background-color: #0d6efd;
            color: white;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            font-size: 1.5rem;
        }

        .chat-box-content {
            padding: 20px;
            overflow-y: auto;
            height: calc(100% - 60px);
            font-size: 1rem;
        }
        .modern-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(13, 30, 77, 0.22), 0 2px 8px rgba(13,110,253,0.10);
            padding: 32px 24px;
            margin-top: 40px;
            margin-bottom: 40px;
        }
        .kulup-hero {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
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
        .kulup-title-modern .bi {
            font-size: 2rem;
            vertical-align: middle;
            margin-right: 10px;
            color: #0d6efd;
            filter: drop-shadow(0 2px 8px rgba(13,110,253,0.12));
        }
        .kulup-title-underline-modern {
            display: block;
            width: 110px;
            height: 5px;
            margin: 0.5rem auto 1.5rem auto;
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
        <?php include '../includes/sidebar.php'; ?>
    
       
           
            <main class="col-md-9 col-lg-10 p-4">
                 <?php include '../includes/ai_chat_widget.php'; ?>
    <div class="modern-card">
        <div class="kulup-hero position-relative">
            <div class="kulup-hero-bg"></div>
            <span class="kulup-title-modern">
                <i class="bi bi-chat-dots-fill"></i> Kulüple ilgili istek/dilek/şikayet
            </span>
            <span class="kulup-title-underline-modern"></span>
        </div>
        <form>
            <div class="mb-3">
                <label for="clubName" class="form-label">Kulüp Adı</label>
                <select class="form-select" id="clubName">
                    <option value="" disabled selected>Bir kulüp seçin</option>
                    <?php foreach ($clubs as $club): ?>
                        <option value="<?php echo $club['id']; ?>"  data-president-id="<?php echo $club['clubpresident_id']; ?>"><?php echo htmlspecialchars($club['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <div id="clubNameHelp" class="form-text">Kulüp adını doğru seçtiğinizden emin olun.</div>
            </div>
            <div class="mb-3">
                <label for="clubDescription" class="form-label">Açıklama</label>
                <textarea class="form-control" id="clubDescription" rows="3" placeholder="Kulüple ilgili şikayetlerinizi ve fikirlerinizi belirtin"></textarea>
                <div id="clubDescriptionHelp" class="form-text">Kulüple ilgili şikayetlerinizi ve fikirlerinizi belirtin</div>
            </div>
            <button type="button" class="btn btn-primary" id="sendMessage" style="margin-left: 0;">Gönder</button>
            <div id="messageResponse" class="mt-3"></div>
        </form>
    </div>
</main>
        </div>
    </div>

    <!-- Bootstrap JS (Optional for some functionality) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>

        document.getElementById("sendMessage").addEventListener("click", function () {
            const clubId = document.getElementById("clubName").value;
            const message = document.getElementById("clubDescription").value;
            const responseDiv = document.getElementById("messageResponse");
            const selectedOption = document.getElementById("clubName").options[document.getElementById("clubName").selectedIndex];
            const clubpresident_id = selectedOption.getAttribute("data-president-id");

            if (!clubId || !message) {
                responseDiv.innerHTML = '<div class="alert alert-danger">Lütfen tüm alanları doldurun!</div>';
                return;
            }
            // AJAX isteği gönder
            $.ajax({
                url: '../backend/message_send.php',
                type: 'POST',
                data: {
                    club_id: clubId,
                    message: message,
                    clubpresident_id: clubpresident_id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        responseDiv.innerHTML = '<div class="alert alert-success">' + response.message + '</div>';
                        document.getElementById("clubDescription").value = '';
                        document.getElementById("clubName").value = '';
                    } else {
                        responseDiv.innerHTML = '<div class="alert alert-danger">' + response.message + '</div>';
                    }
                    
                },
                error: function(xhr, status, error) {
                    console.log('Error:', error);
                    responseDiv.innerHTML = '<div class="alert alert-danger">Bir hata oluştu. Lütfen tekrar deneyin.</div>';
                }
            });
        });
    </script>
</body>
</html>
