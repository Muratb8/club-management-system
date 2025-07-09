<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etkinlik Yaratma</title>
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
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
       
        .container {
            max-width: 800px;
            margin-top: 50px;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .form-title {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .form-section-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-top: 30px;
        }
        .form-control {
            margin-bottom: 15px;
        }
        .btn-primary {
            width: 100%;
        }
        .form-check-label {
            font-size: 1.1rem;
        }
        .form-check {
            margin-bottom: 10px;
        }
.etkinlik-main-card {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 8px 32px rgba(13, 30, 77, 0.22), 0 2px 8px rgba(13,110,253,0.10);
    padding: 32px 24px;
    margin-bottom: 40px;
    margin-top: 32px;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}
.etkinlik-hero {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin-bottom: 24px;
    margin-top: 8px;
    position: relative;
    z-index: 1;
}
.etkinlik-hero-bg {
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
.etkinlik-title-modern-main {
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
.etkinlik-title-underline-modern {
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
</head>
<body>
    <div class="container-fluid">
        <div class="row">

        <?php include '../includes/sidebar.php'; ?>
        <?php include '../includes/mobil_menu.php'?>
       <div class="container">
 <?php include '../includes/ai_chat_widget.php'; ?>

        <div class="etkinlik-main-card">
    <div class="etkinlik-hero position-relative">
        <div class="etkinlik-hero-bg"></div>
        <span class="etkinlik-title-modern-main">
            <i class="bi bi-calendar-plus"></i> Etkinlik Yaratma
        </span>
        <span class="etkinlik-title-underline-modern"></span>
    </div>
    <form id="eventForm">
        <div class="mb-3">
            <label for="activityTopic" class="form-label">Aktivitenin Konusu</label>
            <input type="text" class="form-control" id="activityTopic" name="activityTopic" placeholder="Aktivitenin konusu" required>
        </div>
        <div class="mb-3">
            <label for="activityDescription" class="form-label">Aktivitenin açıklaması</label>
            <input type="text" class="form-control" id="activityDescription" name="activityDescription"  placeholder="Aktivitenin Açıklaması" required>
        </div>
        <div class="mb-3">
            <label for="activityDate" class="form-label">Aktivite Tarihi</label>
            <input type="datetime-local" class="form-control" id="activityDate" name="activityDate" required>
        </div>
        <div class="mb-3">
            <label for="participants" class="form-label">Katılımcıların İsmi</label>
            <input type="text" class="form-control" id="participants" name="participants" placeholder="Katılımcıların ismi" required>
        </div>
        <div class="mb-3">
            <label for="eventLocation" class="form-label">Etkinlik Mekanı</label>
            <input type="text" class="form-control" id="eventLocation" name="eventLocation" placeholder="Etkinlik nerede gerçekleşecek?" required>
        </div>
        <button type="submit" class="btn btn-primary mt-4 w-100">Etkinliği Oluştur</button>
    </form>
    <div id="responseMessage" class="alert" style="display: none; margin-top: 20px;"></div>
</div>

    </div>

</div>
</div>


  <!-- jQuery mutlaka en üstte olmalı -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Diğer scriptler -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

<script>
$(document).ready(function() {
    $('#eventForm').submit(function(e) {
        e.preventDefault();

        var activityTopic = $('#activityTopic').val();
        var activityDescription = $('#activityDescription').val();
        var activityDate = $('#activityDate').val();
        var participants = $('#participants').val();
        var eventLocation = $('#eventLocation').val();
        $.ajax({
            url: '../backend/event_application.php',
            type: 'POST',
            data: {
                activityTopic: activityTopic,
                activityDescription:activityDescription,
                activityDate: activityDate,
                participants: participants,
                eventLocation: eventLocation
            },
            success: function(response) {
                try {
                    var json = JSON.parse(response);
                    if (json.success) {
                        $('#responseMessage').text('Etkinlik başarıyla başvuruldu!').css('color', 'green').show();
                    } else {
                        $('#responseMessage').text('Hata oluştu: ' + json.message).css('color', 'red').show();
                    }
                } catch (e) {
                    $('#responseMessage').text('Geçersiz yanıt: ' + response).css('color', 'red').show();
                }
            },
            error: function() {
                $('#responseMessage').text('Sunucu hatası! Lütfen tekrar deneyin.').css('color', 'red').show();
            }
        });
    });
});
</script>

</body>
</html>
