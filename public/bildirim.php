<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bildirimler</title>
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
    if ($role !== 'student' && $role !=='club_president' && $role !== 'developer') {
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

    
    // Bildirimleri veritabanından çek
    $sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
    $notifications = $db->getAllRecords($sql, [$_SESSION['id']]);

    // Bildirimleri okundu olarak işaretle
    $updateSql = "UPDATE notifications SET status = 'read' WHERE user_id = ? AND status = 'unread'";
    $db->execute($updateSql, [$_SESSION['id']]);

    // Bildirim silme işlemi
    if (isset($_POST['delete_notification']) && isset($_POST['notification_id'])) {
        $notification_id = $_POST['notification_id'];
        $delete_sql = "DELETE FROM notifications WHERE id = ? AND user_id = ?";
        $db->execute($delete_sql, [$notification_id, $_SESSION['id']]);
        
        // Sayfayı yeniden yükle
        header("Location: bildirim.php");
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

    

        .notification-card {
            background-color: #ffffff;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        /* Etkinlik bildirimleri için stil */
        .notification-card.event {
            border-left: 4px solid #28a745; /* Yeşil */
        }
        .notification-card.event .notification-dot {
            background-color: #28a745;
        }
        .notification-card.event .join-button {
            background-color: #28a745;
        }

        /* Duyuru bildirimleri için stil */
        .notification-card.announcement {
            border-left: 4px solid #0d6efd; /* Mavi */
        }
        .notification-card.announcement .notification-dot {
            background-color: #0d6efd;
        }

        /* Bilgi bildirimleri için stil */
        .notification-card.information {
            border-left: 4px solid #ffc107; /* Sarı */
        }
        .notification-card.information .notification-dot {
            background-color: #ffc107;
        }

        .notification-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }
        .notification-card .notification-header {
            font-size: 1.2rem;
            font-weight: bold;
        }
        .notification-card .notification-body {
            font-size: 1rem;
            color: #555;
        }
        .notification-card .notification-time {
            font-size: 0.9rem;
            color: #777;
            text-align: right;
        }
        .notification-card .notification-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 10px;
        }
        .notification-card .join-button {
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .notification-card .join-button:hover {
            opacity: 0.9;
        }
        .notification-card.read {
            opacity: 0.7;
        }
        .notification-card.read .notification-dot {
            opacity: 0.7;
        }
        
        .notification-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 10px;
        }
        
        .delete-button {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 0.9rem;
        }
        
        .delete-button:hover {
            background-color: #c82333;
        }
        
        .delete-button i {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
        <?php include_once '../includes/sidebar.php'; ?>
            <!-- Mobil Hamburger Menü -->
            <?php include_once '../includes/mobil_menu.php'; ?>
           

            <!-- Bildirimler Sayfası -->
            <main class="col-md-9 col-lg-10 p-4">
                            <?php include '../includes/ai_chat_widget.php'; ?>
                <h2>Bildirimler</h2>
                
                <?php if (empty($notifications)): ?>
                    <div class="alert alert-info">
                        Henüz bildiriminiz bulunmamaktadır.
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification-card <?php echo $notification['status'] === 'read' ? 'read' : ''; ?> <?php echo $notification['type']; ?>" 
                             id="notification-<?php echo $notification['id']; ?>">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <span class="notification-dot"></span>
                                    <span class="notification-header"><?php echo htmlspecialchars($notification['title']); ?></span>
                                </div>
                                <div class="notification-time">
                                    <?php 
                                        $date = new DateTime($notification['created_at']);
                                        echo $date->format('d.m.Y H:i');
                                    ?>
                                </div>
                            </div>
                            <div class="notification-body">
                                <?php echo htmlspecialchars($notification['content']); ?>
                            </div>
                            <div class="notification-actions">
                                <?php if ($notification['type'] === 'event'): ?>
                                    <button class="join-button">
                                        Katıl
                                    </button>
                                <?php endif; ?>
                                <form method="POST" style="display: inline; margin-left: 10px;" onsubmit="return confirm('Bu bildirimi silmek istediğinizden emin misiniz?');">
                                    <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                    <button type="submit" name="delete_notification" class="delete-button">
                                        <i class="bi bi-trash"></i> Sil
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </main>
        </div>
    </div>
    
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


</body>
</html>
