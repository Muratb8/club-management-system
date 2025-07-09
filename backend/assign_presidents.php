<?php
require_once 'database.php';
$db = new Database();
$db->getConnection();

// 1. Seçim bitiş tarihi kontrolü
$election = $db->getRow("SELECT end_date FROM election_settings ORDER BY id DESC LIMIT 1");
if (!$election || strtotime($election['end_date']) > time()) {
    echo "Seçim henüz bitmedi veya seçim tarihi bulunamadı.";
    exit;
}

// 2. Tüm kulüpler için işlemleri başlat
$clubs = $db->getAllRecords("SELECT id, clubpresident_id FROM clubs");
foreach ($clubs as $club) {
    $club_id = $club['id'];

    // 3. Kulüpte aday var mı?
    $candidate_count = $db->getRow("SELECT COUNT(*) as cnt FROM candidates WHERE club_id = :club_id", ['club_id' => $club_id]);
    if (!$candidate_count || $candidate_count['cnt'] == 0) {
        // Aday yoksa başkan atanmaz
        continue;
    }

    // 4. En çok oyu alan adayı ve oy sayısını bul
    $top_candidate = $db->getRow(
        "SELECT c.candidate_id, c.student_id, COUNT(v.vote_id) as vote_count
         FROM candidates c
         LEFT JOIN votes v ON v.candidate_id = c.candidate_id
         WHERE c.club_id = :club_id
         GROUP BY c.candidate_id
         ORDER BY vote_count DESC
         LIMIT 1",
        ['club_id' => $club_id]
    );

    // 5. Hiç oy verilmemişse başkan atanmaz
    if (!$top_candidate || $top_candidate['vote_count'] == 0) {
        continue;
    }

    // 6. En yüksek oyu alan birden fazla aday var mı?
    $equal_votes_count = $db->getRow(
        "SELECT COUNT(*) as cnt
         FROM (
            SELECT COUNT(v.vote_id) as vote_count
            FROM candidates c
            LEFT JOIN votes v ON v.candidate_id = c.candidate_id
            WHERE c.club_id = :club_id
            GROUP BY c.candidate_id
            HAVING vote_count = :vote_count
         ) as sub",
        ['club_id' => $club_id, 'vote_count' => $top_candidate['vote_count']]
    );
    if ($equal_votes_count && $equal_votes_count['cnt'] > 1) {
        // En yüksek oyu alan birden fazla aday varsa başkan atanmaz
        continue;
    }

    // 7. Adayın user_id'sini ve öğrenci numarasını bul
    $student = $db->getRow("SELECT user_id, student_number FROM students WHERE student_id = :student_id", [
        'student_id' => $top_candidate['student_id']
    ]);
    if ($student) {
        // 8. Yeni başkan için yeni bir kullanıcı oluştur
        $president_email = $student['student_number'] . '_president@emu.com';
        $president_password = '1';
        $existingPresidentUser = $db->getRow("SELECT id FROM users WHERE email = :email", ['email' => $president_email]);
        if (!$existingPresidentUser) {
            $db->insertData('users', [
                'email' => $president_email,
                'PASSWORD' => password_hash($president_password, PASSWORD_DEFAULT),
                'role' => 'club_president'
            ]);
            $president_user_id = $db->conn->lastInsertId();
        } else {
            $president_user_id = $existingPresidentUser['id'];
        }

        // 9. Mevcut başkan(lar)ı sil
        $oldPresidents = $db->getAllRecords("SELECT president_id, user_id FROM clubpresidents WHERE kulup_id = :kulup_id", ['kulup_id' => $club_id]);
        foreach ($oldPresidents as $oldPresident) {
            // Önce bu başkana ait mesajları sil
            $db->deleteData('messages', 'president_id = :president_id', ['president_id' => $oldPresident['president_id']]);
            $db->deleteData('notifications', 'user_id = :user_id', ['user_id' => $oldPresident['user_id']]);
            // Önce event_application tablosundaki başkan başvurularını sil
            $db->deleteData('event_application', 'club_president_id = :club_president_id', ['club_president_id' => $oldPresident['user_id']]);
            $db->deleteData('users', 'id = :id', ['id' => $oldPresident['user_id']]);
            $db->deleteData('clubpresidents', 'president_id = :president_id', ['president_id' => $oldPresident['president_id']]);
        }
        // Yeni başkan kaydı ekle
        $db->insertData('clubpresidents', [
            'user_id' => $president_user_id,
            'kulup_id' => $club_id,
            'student_number' => $student['student_number']
        ]);
        $president_id = $db->conn->lastInsertId();

        // clubs tablosunda clubpresident_id'yi yeni başkanın id'si ile güncelle
        $db->updateData('clubs', ['clubpresident_id' => $president_id], 'id = :id', ['id' => $club_id]);

        // Bildirim gönder (yeni başkan hesabı bilgileri)
        // 1. Öğrencinin kendi kullanıcı id'sini bul
        $student_user_id = $student['user_id'];
        $db->insertData('notifications', [
            'user_id' => $student_user_id,
            'title' => 'Kulüp Başkanlığı Hesabınız Oluşturuldu',
            'content' => "Kulüp başkanı hesabınız oluşturuldu.\nE-posta: $president_email\nŞifre: $president_password",
            'status' => 'unread',
            'type' => 'announcement'
        ]);
        // Ayrıca başkan hesabına da tebrik bildirimi gönder
        $db->insertData('notifications', [
            'user_id' => $president_user_id,
            'title' => 'Kulüp Başkanlığı',
            'content' => 'Tebrikler! Seçim sonucunda kulüp başkanı oldunuz.',
            'status' => 'unread',
            'type' => 'announcement'
        ]);
    }
}

// Tüm oyları sil
$db->execute("DELETE FROM votes", []);

// Tüm adayları sil
$db->execute("DELETE FROM candidates", []);

echo "Seçim sonuçları başarıyla uygulandı. Tüm adaylar ve oylar silindi.";