<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

$api_key = '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';
$referer = "https://openrouter.ai/api/v1";
$title = 'Mezuniyet Projesi';

$data = json_decode(file_get_contents('php://input'), true);
$user_message = trim($data['message'] ?? '');

$reply = "Sorunuzu anlayamadım. Lütfen daha ayrıntılı yazın.";

// 1. Öğrenci bilgileri
if (
    preg_match('/öğrenci (bilgileri|bilgilerim|profil(im)?|bilgilerimi nerede görebilirim)/iu', $user_message)
) {
    $reply = "Öğrenci bilgilerinizi görmek için ana sayfadaki (index) profil bölümünü kullanabilirsiniz.";
    echo json_encode(['reply' => $reply]);
    exit;
}

// 2. Etkinlik oluşturma/yaratma
if (
    preg_match('/etkinlik (nasıl )?(oluştur|yarat|ekle|başlat)/iu', $user_message) ||
    preg_match('/yeni etkinlik/iu', $user_message)
) {
    $reply = "Yeni bir etkinlik oluşturmak için bu sayfadaki formu eksiksiz doldurup 'Etkinliği Oluştur' butonuna basmanız yeterlidir. Etkinlik konusu, açıklaması, tarihi, katılımcılar ve mekan bilgilerini girmeniz gerekmektedir.";
    echo json_encode(['reply' => $reply]);
    exit;
}

// 3. Duyuru oluşturma/yaratma
if (
    preg_match('/duyuru (nasıl )?(oluştur|yarat|ekle|paylaş)/iu', $user_message) ||
    preg_match('/yeni duyuru/iu', $user_message)
) {
    $reply = "Yeni bir duyuru oluşturmak için bu sayfadaki formu doldurup 'Duyuruyu Gönder' butonuna basmanız yeterlidir. Başlık, içerik, kategori ve tarih gibi bilgileri girmeniz gerekmektedir.";
    echo json_encode(['reply' => $reply]);
    exit;
}

// 4. Kulüp oluşturma/başkan atama
if (
    preg_match('/kulüp (nasıl )?(oluştur|yarat|ekle)/iu', $user_message) ||
    preg_match('/yeni kulüp/iu', $user_message) ||
    preg_match('/kulüp başkanı (ata|atama|güncelle|nasıl atanır)/iu', $user_message)
) {
    $reply = "Yeni bir kulüp oluşturmak için bu sayfadaki formu doldurup 'Kulüp Oluştur' butonuna basmanız yeterlidir. Kulüp başkanı atamak veya güncellemek için ise ilgili sekmeye geçip gerekli bilgileri doldurup 'Başkan Ata' butonunu kullanabilirsiniz.";
    echo json_encode(['reply' => $reply]);
    exit;
}

// 5. Başvuru işlemleri
if (
    preg_match('/başvur(u|um|ular|uları|usu|usu nasıl|u nasıl|u onayla|u reddet|u durumu|um ne oldu|onay|reddi)/iu', $user_message) ||
    preg_match('/kulübe başvur/iu', $user_message)
) {
    $reply = "Kulübe yapılan başvuruları bu sayfada görebilir, başvuruları onaylayabilir veya reddedebilirsiniz. Başvurunun durumunu ilgili satırdaki butonları kullanarak değiştirebilirsiniz. Öğrenciler başvurularının durumunu bu sayfadan takip edebilir.";
    echo json_encode(['reply' => $reply]);
    exit;
}

// 6. Etkinlik başvurusu ve onay/ret işlemleri
if (
    preg_match('/etkinlik başvurusu( nasıl)? (onaylanır|reddedilir)?/iu', $user_message) ||
    preg_match('/etkinlik (onayla|reddet)/iu', $user_message) ||
    preg_match('/etkinlik başvurularını nerede görebilirim/iu', $user_message)
) {
    $reply = "Bu sayfada tüm etkinlik başvurularını görebilir, başvuruları inceleyip uygun bulduklarınızı 'Onayla' butonuyla onaylayabilir veya 'Reddet' butonuyla reddedebilirsiniz. Her başvurunun detaylarını kutucuğa tıklayarak görüntüleyebilirsiniz.";
    echo json_encode(['reply' => $reply]);
    exit;
}

// 7. Kulüp üyeleri, başkan bilgisi, üye çıkarma
if (
    preg_match('/kulüp üyeleri|üye listesi|kulüp başkanı|başkan bilgisi|üye çıkar(ma)?|üyeyi çıkar|başkanın görevleri|kulüp üye bilgileri/iu', $user_message)
) {
    $reply = "Bu sayfada kulüp üyelerinin listesini görebilir, üyeleri arayabilir ve gerektiğinde üyeleri kulüpten çıkarabilirsiniz. Ayrıca kulüp başkanı olarak kendi bilgilerinizi ve kulübünüzle ilgili detayları da burada inceleyebilirsiniz.";
    echo json_encode(['reply' => $reply]);
    exit;
}

// 8. Kulüp bilgileri (yönetici/moderatör)
if (
    preg_match('/kulüp bilgileri|kulüp üye sayısı|başkan iletişim|kulüp başkanları|kulüp listesi|başkan listesi/iu', $user_message)
) {
    $reply = "Bu sayfada tüm kulüplerin üye sayılarını, kulüp başkanlarının iletişim bilgilerini ve kulüp başkanları listesini görebilirsiniz. Ayrıca kulüp üyeleriyle ilgili detaylı bilgilere de ulaşabilirsiniz.";
    echo json_encode(['reply' => $reply]);
    exit;
}

// 9. Bildirimler ve duyurular
if (
    preg_match('/bildirim(ler|im|im nerede|im nasıl)?/iu', $user_message) ||
    preg_match('/duyuru(lar)?/iu', $user_message) ||
    preg_match('/etkinlik bildirimi/iu', $user_message)
) {
    $reply = "Tüm bildirimlerinizi ve duyurularınızı bu sayfada görebilirsiniz. Bildirimleri okumak, etkinliklere katılmak veya duyuruları incelemek için ilgili butonları kullanabilirsiniz. Bildirimleri silmek için ise 'Sil' butonunu kullanabilirsiniz.";
    echo json_encode(['reply' => $reply]);
    exit;
}

// 10. Mesajlar ve kulüp başkanıyla iletişim
if (
    preg_match('/mesaj(lar| kutusu| sil| filtrele| gönder|laşma)/iu', $user_message) ||
    preg_match('/başkan ile mesajlaşma/iu', $user_message) ||
    preg_match('/kulüp başkanı(yla| ile) (iletişim|konuşabilirim)/iu', $user_message) ||
    preg_match('/kulüp (mesaj|sohbet|öneri|şikayet|istek|ile iletişim)/iu', $user_message)
) {
    $reply = "Kulüp başkanıyla iletişim kurmak, öneri, istek veya şikayet göndermek için bu sayfadaki formu doldurup 'Gönder' butonuna basabilirsiniz. Mesajınız ilgili kulüp başkanına iletilecektir. Ayrıca bu sayfada kulüp başkanı ile yapılan tüm mesajlaşmaları görebilir, mesajları filtreleyebilir ve isterseniz mesajları silebilirsiniz.";
    echo json_encode(['reply' => $reply]);
    exit;
}

// 11. Seçim ve oylama
if (
    preg_match('/oylama|başkan seçimi|oy kullan|oy verdim mi|oy sonuçları|aday belirleme|seçim tarihi|oy nasıl verilir/iu', $user_message)
) {
    $reply = "Kulüp başkan oylaması için bu sayfada üyesi olduğunuz kulübü seçip adaylara oy verebilirsiniz. Başkan adaylarını belirlemek için kulüp başkanı olarak üyeleri aday gösterebilirsiniz. Seçim tarihlerini ve oy sonuçlarını ise yine bu sayfadan takip edebilirsiniz.";
    echo json_encode(['reply' => $reply]);
    exit;
}

// 12. İletişim ve yönetici işlemleri
if (
    preg_match('/iletişim|yönetici (ekle|sil|güncelle)|önemli kişiler|yönetici bilgileri|yönetici listesi/iu', $user_message)
) {
    $reply = "Bu sayfada yöneticilerin iletişim bilgilerini görebilir, yeni yönetici ekleyebilir, mevcut yöneticileri güncelleyebilir veya silebilirsiniz. Yönetici eklemek için formu doldurup 'Ekle' butonuna basmanız yeterlidir. Sadece rütbeli yöneticiler bu işlemleri yapabilir.";
    echo json_encode(['reply' => $reply]);
    exit;
}

// 13. Aktivite merkezi ve etkinlik çizelgesi
if (
    preg_match('/etkinlik çizelgesi|aktivite merkezi|etkinlik saatleri|etkinlik programı|etkinlik takvimi|etkinlik tablosu|etkinlikler hangi gün|etkinlikler hangi saatte/iu', $user_message)
) {
    $reply = "Bu sayfada tüm onaylanmış etkinliklerin tarih ve saatlerini tablo halinde görebilirsiniz. Haftalık veya aylık olarak etkinlik programını inceleyebilir, etkinliklerin hangi gün ve saatte olduğunu kolayca öğrenebilirsiniz.";
    echo json_encode(['reply' => $reply]);
    exit;
}

// 14. Genel etkinliklerle ilgili yönlendirme (en sona bırakın!)
if (
    (preg_match('/etkinlik(ler)?/iu', $user_message) ||
    preg_match('/etkinlikler hakkında bilgi/iu', $user_message) ||
    preg_match('/etkinlik bilgisi/iu', $user_message))
    // ve yukarıdaki özel kelimelerden hiçbiri yoksa
    && !preg_match('/etkinlik (nasıl )?(oluştur|yarat|ekle|başlat)/iu', $user_message)
    && !preg_match('/yeni etkinlik/iu', $user_message)
) {
    $reply = "Tüm etkinlikleri görmek ve katılmak için 'Etkinlikler' sayfasını ziyaret edebilirsiniz. Burada tarih, mekan ve açıklama gibi tüm detayları bulabilirsiniz. Katılmak istediğiniz etkinliğin yanında bulunan 'Katıl' butonunu kullanabilirsiniz.";
    echo json_encode(['reply' => $reply]);
    exit;
}

// 15. Diğer tüm sorular için yapay zekaya gönder
if (!empty($user_message)) {
    $endpoint = 'https://openrouter.ai/api/v1/chat/completions';

    $prompt = "Sen bir üniversite kulüp yönetim sistemi asistanısın. Yanıtlarını kısa, öz ve sade tut. Gereksiz açıklama yapma, sadece soruya odaklan. Kulübe üye olmak için 'Kulüpler' sayfasında başvur butonuna tıklanır.\n\n" . $user_message;

    $post_fields = [
        "model" => "google/gemma-3n-e4b-it:free",
        "messages" => [
            [
                "role" => "user",
                "content" => $prompt
            ]
        ]
    ];

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key,
        'HTTP-Referer: ' . $referer,
        'X-Title: ' . $title
    ]);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_fields));

    $result = curl_exec($ch);
    file_put_contents(__DIR__.'/ai_debug.log', $result);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        $reply = "Yapay zeka servisine ulaşılamadı.";
    } else {
        $response = json_decode($result, true);
        $reply = $response['choices'][0]['message']['content'] ?? $reply;
    }
}

echo json_encode(['reply' => $reply]);