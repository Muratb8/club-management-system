<?php
// Bildirim sayılarını al
$sql = "SELECT type, COUNT(*) as count 
        FROM notifications 
        WHERE user_id = ? AND status = 'unread' 
        GROUP BY type";
        require_once '../backend/database.php';
$db = new Database();
$db->getConnection();
$notification_counts = $db->getAllRecords($sql, [$_SESSION['id']]);

// Her kategori için sayıları ayarla (varsayılan 0)
$event_count = 0;
$announcement_count = 0;
$information_count = 0;

foreach ($notification_counts as $count) {
    switch ($count['type']) {
        case 'event':
            $event_count = $count['count'];
            break;
        case 'announcement':
            $announcement_count = $count['count'];
            break;
        case 'information':
            $information_count = $count['count'];
            break;
    }
}

$show_election = false;
if ($role === 'student') {
    $election = $db->getRow("SELECT start_date, end_date FROM election_settings ORDER BY id DESC LIMIT 1");
    if ($election) {
        $now = date('Y-m-d H:i:s');
        if ($now >= $election['start_date'] && $now <= $election['end_date']) {
            $show_election = true;
        }
    }
}
?>

<style>
    .notification-badge {
        position: absolute;
        top: 8px;
        font-size: 0.6em;
        padding: 3px 6px;
        border-radius: 10px;
        color: white;
        cursor: help;
    }
    .notification-badge.event {
        background-color: #28a745;
        right: 10px;
    }
    .notification-badge.announcement {
        background-color: #0d6efd;
        right: 35px;
    }
    .notification-badge.information {
        background-color: #ffc107;
        right: 60px;
    }
    .notification-badge:hover::after {
        content: attr(data-tooltip);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        padding: 5px;
        background-color: #333;
        color: white;
        border-radius: 4px;
        font-size: 12px;
        white-space: nowrap;
        z-index: 1000;
    }
</style>
<!-- Sidebar -->
<nav class="col-md-3 col-lg-2 sidebar d-none d-md-block position-relative" id="sidebar">
<h4 class="text-center py-4">
        <a href="../public/index.php">
            <img src="../uploads/DAÜ.png" alt="DAÜ Logo">
        </a>
    </h4>
    
    <?php if ($role === 'student'): ?>
        <a href="index.php"><i class="bi bi-house-fill"></i> Ana Sayfa</a>
        <a href="kulup.php"><i class="bi bi-people-fill"></i> Kulüpler</a>
        <a href="message_room.php"><i class="bi bi-chat-left-text-fill"></i> İletişim</a>
        <a href="bildirim.php" class="position-relative">
            <i class="bi bi-bell-fill"></i> Bildirimler
            <?php if ($event_count > 0): ?>
                <span class="notification-badge event" data-tooltip="Etkinlik Bildirimleri"><?php echo $event_count; ?></span>
            <?php endif; ?>
            <?php if ($announcement_count > 0): ?>
                <span class="notification-badge announcement" data-tooltip="Duyuru Bildirimleri"><?php echo $announcement_count; ?></span>
            <?php endif; ?>
            <?php if ($information_count > 0): ?>
                <span class="notification-badge information" data-tooltip="Bilgi Bildirimleri"><?php echo $information_count; ?></span>
            <?php endif; ?>
        </a>
        <a href="etkinlik.php"><i class="bi bi-calendar-event-fill"></i> Etkinlikler</a>
        <?php if ($show_election): ?>
        <a href="secim.php"><i class="bi bi-clipboard-data-fill"></i> Seçim</a>
    <?php endif; ?>

    <?php elseif ($role === 'club_president'): ?>
        <a href="index.php"><i class="bi bi-house-fill"></i> Ana Sayfa</a>
        <a href="bildirim.php" class="position-relative">
            <i class="bi bi-bell-fill"></i> Bildirimler
            <?php if ($event_count > 0): ?>
                <span class="notification-badge event" data-tooltip="Etkinlik Bildirimleri"><?php echo $event_count; ?></span>
            <?php endif; ?>
            <?php if ($announcement_count > 0): ?>
                <span class="notification-badge announcement" data-tooltip="Duyuru Bildirimleri"><?php echo $announcement_count; ?></span>
            <?php endif; ?>
            <?php if ($information_count > 0): ?>
                <span class="notification-badge information" data-tooltip="Bilgi Bildirimleri"><?php echo $information_count; ?></span>
            <?php endif; ?>
        </a>
        <a href="etkinlik.php"><i class="bi bi-calendar-event-fill"></i> Etkinlikler</a>
        <a href="Etkinlik_yaratma.php"><i class="bi bi-plus-circle-fill"></i> Etkinlik Yaratma</a>
        <a href="duyuru_yaratma.php"><i class="bi bi-megaphone-fill"></i> Duyuru Yaratma</a>
        <a href="kulup_bilgileri(baskan).php"><i class="bi bi-info-circle-fill"></i> Kulüp Bilgileri</a>
        <a href="basvuru.php"><i class="bi bi-file-earmark-person-fill"></i> Başvurular</a>
        <a href="messages.php"><i class="bi bi-clipboard-data-fill"></i> Mesajlar</a>
         <a href="secim.php"><i class="bi bi-clipboard-data-fill"></i> Seçim</a>
          <a href="iletisim.php"><i class="bi bi-clipboard-data-fill"></i> Görevliler</a>

    <?php elseif ($role === 'manager'): ?>
        <a href="index.php"><i class="bi bi-house-fill"></i> Ana Sayfa</a>
        <a href="etkinlik.php"><i class="bi bi-calendar-event-fill"></i> Etkinlikler</a>
        <a href="kulup_yaratma.php"><i class="bi bi-clipboard-data-fill"></i> Kulüp Yaratma</a>
        <a href="aktivite_merkezi_sorumlusu.php"><i class="bi bi-clipboard-data-fill"></i> Aktivite Merkezi Sorumlusunun, Etkinlik Gözlem yeri</a>
        <a href="istek.php"><i class="bi bi-box-arrow-in-down"></i> İstek</a>
        <a href="kulub_bilgileri.php"><i class="bi bi-info-circle-fill"></i> Kulüp Bilgileri</a>
        <a href="secim.php"><i class="bi bi-clipboard-data-fill"></i> Seçim</a>
         <a href="iletisim.php"><i class="bi bi-clipboard-data-fill"></i> Görevliler</a>
    <?php elseif ($role === 'developer'): ?>
        <a href="index.php"><i class="bi bi-house-fill"></i> Ana Sayfa</a>
        <a href="kulup.php"><i class="bi bi-people-fill"></i> Kulüpler</a>
        <a href="message_room.php"><i class="bi bi-chat-left-text-fill"></i> İletişim</a>
        <a href="bildirim.php" class="position-relative">
            <i class="bi bi-bell-fill"></i> Bildirimler
            <?php if ($event_count > 0): ?>
                <span class="notification-badge event" data-tooltip="Etkinlik Bildirimleri"><?php echo $event_count; ?></span>
            <?php endif; ?>
            <?php if ($announcement_count > 0): ?>
                <span class="notification-badge announcement" data-tooltip="Duyuru Bildirimleri"><?php echo $announcement_count; ?></span>
            <?php endif; ?>
            <?php if ($information_count > 0): ?>
                <span class="notification-badge information" data-tooltip="Bilgi Bildirimleri"><?php echo $information_count; ?></span>
            <?php endif; ?>
        </a>
        <a href="etkinlik.php"><i class="bi bi-calendar-event-fill"></i> Etkinlikler</a>
        <a href="Etkinlik_yaratma.php"><i class="bi bi-plus-circle-fill"></i> Etkinlik Yaratma</a>
        <a href="duyuru_yaratma.php"><i class="bi bi-megaphone-fill"></i> Duyuru Yaratma</a>
        <a href="kulup_bilgileri(baskan).php"><i class="bi bi-info-circle-fill"></i> Kulüp Bilgileri</a>
        <a href="basvuru.php"><i class="bi bi-file-earmark-person-fill"></i> Başvurular</a>
        <a href="kulub_bilgileri.php"><i class="bi bi-clipboard-data-fill"></i> Aktivite Merkezi Kulüp Bilgileri</a>
        <a href="kulup_yaratma.php"><i class="bi bi-clipboard-data-fill"></i> Kulüp Yaratma</a>
        <a href="aktivite_merkezi_sorumlusu.php"><i class="bi bi-clipboard-data-fill"></i> Aktivite Merkezi Sorumlusunun, Etkinlik Gözlem yeri</a>
        <a href="secim.php"><i class="bi bi-clipboard-data-fill"></i> Seçim</a>
        <a href="istek.php"><i class="bi bi-clipboard-data-fill"></i> İstekler</a>
        <a href="iletisim.php"><i class="bi bi-clipboard-data-fill"></i> İletişim</a>
        <a href="messages.php"><i class="bi bi-clipboard-data-fill"></i> Mesajlar</a>

    <?php endif; ?>
     <!-- Çıkış Yap Butonu -->
 <a href="../backend/logout.php" class="btn btn-outline-danger d-flex align-items-center">
        <i class="bi bi-box-arrow-right me-2"></i>Çıkış Yap
    </a>
</nav>

