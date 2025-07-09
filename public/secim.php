<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kulüp Başkan Oylaması</title>
      <!-- Harici CSS dosyası -->
      <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
.member-card.selected {
    background-color: #0d6efd !important;
    color: #fff;
    border-color: #0a58ca;
}
.member-card.selected .card-title,
.member-card.selected .text-muted,
.member-card.selected label {
    color: #fff !important;
}
</style>
    
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

//veritabanı bağlantısı
require_once '../backend/database.php';
$db = new Database();


if ($role === 'student'){

$student_id=$_SESSION['id'];

    $clubs = $db->getAllRecords("
    SELECT c.id, c.name AS club_name 
    FROM clubs c 
    INNER JOIN clubmembers cm ON cm.club_id = c.id 
    WHERE cm.user_id = ?
", [$student_id]);

}

// Sadece "manager" veya "developer" rolüne izin ver
if ($role !== 'student' && $role !== 'club_president' && $role !== 'manager' && $role !== 'developer') {
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

</head>
<body>
   <div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        <?php include '../includes/mobil_menu.php' ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
         <?php include '../includes/ai_chat_widget.php'; ?>

             <?php if ($role === 'student'): ?>
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                <h1 class="h2">Kulüp Başkan Oylaması</h1>
            </div>
<?php if (empty($clubs)): ?>
    <div class="alert alert-warning">Henüz üyesi olduğunuz bir kulüp bulunmamaktadır.</div>
<?php else: ?>
    <div class="mb-3">
        <label for="clubSelect" class="form-label">Kulüp Seçin</label>
        <select class="form-select" id="clubSelect" name="id" required>
            <option value="" selected disabled>Kulüp Seçiniz</option>
            <?php foreach ($clubs as $club): ?>
                <option value="<?php echo $club['id']; ?>">
                    <?php echo htmlspecialchars($club['club_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
<?php endif; ?>

         <div class="card">
    <div class="card-body">
        <h5 class="card-title">Başkan Adaylarını Oyla</h5>
        <p>En fazla 5 başkan adayı seçebilirsiniz.</p>
        <form id="votingForm" method="POST ">
            <div class="d-flex flex-wrap gap-3 justify-content-start" id="candidateContainer">
                <!-- Aday kartları buraya AJAX ile eklenecek -->
            </div>
            <p id="selectionInfo" class="text-danger mt-3">Seçtiğiniz aday sayısı: 0/5</p>
            <button type="submit" class="btn btn-primary" disabled id="submitButton">Oy Ver</button>
        </form>
    </div>
</div>

<?php elseif ($role === 'club_president'): ?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="h2">Başkan Adayı Belirleme</h1>
    </div>

 <?php
$president_id = $_SESSION['id'];

// Başkan olduğu kulüp
$presidentClub = $db->getRow("
    SELECT c.id, c.name AS club_name 
    FROM clubs c 
    INNER JOIN clubpresidents cp ON cp.kulup_id = c.id 
    WHERE cp.user_id = ?
", [$president_id]);

if (!$presidentClub) {
    echo '<div class="alert alert-warning">Şu an başkanı olduğunuz bir kulüp bulunmamaktadır.</div>';
} else {
    $club_id = $presidentClub['id'];

    // Onaylı kulüp üyeleri
    $approvedMembers = $db->getAllRecords("
        SELECT s.student_id, s.first_name, s.last_name, u.email 
        FROM clubmembers cm
        INNER JOIN students s ON cm.user_id = s.user_id
        INNER JOIN users u ON s.user_id = u.id
        WHERE cm.club_id = ? AND cm.status = 'approved'
    ", [$club_id]);
    ?>
    
    <form id="candidateForm" method="POST" enctype="multipart/form-data" action="../backend/add_candidates_bulk.php">
        <input type="hidden" name="club_id" value="<?= $club_id ?>">

        <div class="mb-3">
            <label class="form-label">Kulüp</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($presidentClub['club_name']) ?>" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Aday Olarak Belirlemek İstediğiniz Üyeleri Seçiniz:</label>
            <?php if (empty($approvedMembers)): ?>
                <div class="alert alert-secondary">Henüz onaylı kulüp üyeniz bulunmamaktadır.</div>
            <?php else: ?>
                <input type="text" id="searchInput" class="form-control mb-3" placeholder="Üye ara...">
                <div class="row" id="membersList">
                    <?php foreach ($approvedMembers as $member): ?>
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3 member-item">
                            <div class="card h-100 shadow-sm member-card" data-user-id="<?= $member['student_id'] ?>" style="cursor:pointer;">
                                <div class="card-body d-flex flex-column align-items-center">
                                    <i class="bi bi-person-circle" style="font-size:2.5rem; color:#6c757d;"></i>
                                    <h6 class="card-title mt-2 mb-1 text-center" style="font-size:1rem;">
                                        <?= htmlspecialchars($member['first_name']) ?> <?= htmlspecialchars($member['last_name']) ?>
                                    </h6>
                                    <span class="text-muted" style="font-size:0.85rem;"><?= htmlspecialchars($member['student_id']) ?></span>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input candidate-checkbox" type="checkbox" name="candidate_ids[]" value="<?= $member['student_id'] ?>" id="member<?= $member['student_id'] ?>">
                                        <label class="form-check-label" for="member<?= $member['student_id'] ?>">Aday Yap</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-success">Seçilenleri Aday Yap</button>
    </form>
<?php } ?>

  <?php elseif ($role === 'manager'): ?>
    <div class="container mt-4">
        <h4>Seçim Ayarları</h4>
        <form id="electionSettingsForm" method="POST" action="../backend/set_election_dates.php">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="start_date" class="form-label">Seçim Başlangıç Tarihi</label>
                    <input type="datetime-local" class="form-control" id="start_date" name="start_date" required>
                </div>
                <div class="col-md-6">
                    <label for="end_date" class="form-label">Seçim Bitiş Tarihi</label>
                    <input type="datetime-local" class="form-control" id="end_date" name="end_date" required>
                </div>
            </div>
            <button type="submit" class="btn btn-success">Tarihi Kaydet</button>
        </form>

        <hr class="my-4">

        <h4>Kulüp Başkanlarına Bildirim Gönder</h4>
        <form id="notifyPresidentsForm" method="POST" action="../backend/add_notification_for_presidents.php">
            <div class="mb-3">
                <label for="notification_message" class="form-label">Bildirim Mesajı</label>
                <textarea class="form-control" id="notification_message" name="message" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Bildirim Gönder</button>
        </form>
        <hr class="my-4">
         <h4 class="mb-3">Kulüplerde Kullanılan Oylar ve Adayların Oy Dağılımı</h4>
        <?php
        // Tüm kulüpleri çek
        $clubs = $db->getAllRecords("SELECT id, name FROM clubs");

        foreach ($clubs as $club):
            // Kulüpte kullanılan toplam oy
            $total_votes = $db->getRow(
                "SELECT COUNT(*) as total FROM votes WHERE club_id = :club_id",
                ['club_id' => $club['id']]
            )['total'];

            // Kulüpteki adaylar ve aldıkları oylar
            $candidates = $db->getAllRecords(
                "SELECT c.candidate_id, s.first_name, s.last_name, 
                        (SELECT COUNT(*) FROM votes v WHERE v.candidate_id = c.candidate_id) as vote_count
                 FROM candidates c
                 INNER JOIN students s ON c.student_id = s.student_id
                 WHERE c.club_id = :club_id",
                ['club_id' => $club['id']]
            );
        ?>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <strong><?= htmlspecialchars($club['name']) ?></strong>
                <span class="badge bg-light text-dark ms-2">Toplam Oy: <?= $total_votes ?></span>
            </div>
            <div class="card-body">
                <?php if (count($candidates) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Aday</th>
                                    <th>Aldığı Oy</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($candidates as $cand): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($cand['first_name'] . ' ' . $cand['last_name']) ?></td>
                                        <td><?= $cand['vote_count'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning mb-0">Bu kulüpte aday bulunmamaktadır.</div>
                <?php endif; ?>
            </div>
             </div>
        <?php endforeach; ?>
        <hr class="my-4">

<!-- Seçim Sonuçlarını Uygula Butonu -->
<form id="applyElectionResultsForm" method="POST" action="../backend/assign_presidents.php">
    <button type="submit" class="btn btn-danger">
        <i class="bi bi-award"></i> Seçim Sonuçlarını Uygula (Başkanları Ata)
    </button>
</form>
    </div>
<?php endif; ?>
        </main>
    </div>
</div>
   
<script>
document.addEventListener('DOMContentLoaded', () => {
    const clubSelect = document.getElementById('clubSelect');
    const container = document.getElementById('candidateContainer');
    const selectionInfo = document.getElementById('selectionInfo');
    const submitButton = document.getElementById('submitButton');
    const votingForm = document.getElementById('votingForm');

    if (clubSelect) {
        clubSelect.addEventListener('change', function () {
            const clubId = this.value;
            fetch('../backend/get_candidates.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'club_id=' + clubId
            })
            .then(response => response.json())
            .then(data => {
                container.innerHTML = '';
                let index = 0;

                const votedIds = data.voted_ids || [];
                const hasVoted = data.has_voted === true;

                // Oy ver butonunu disable et
                submitButton.disabled = hasVoted;

                if (hasVoted) {
                    selectionInfo.textContent = "Bu kulüpte oy verdiniz. Adayları değiştiremezsiniz.";
                    selectionInfo.classList.add('text-danger');
                } else {
                    selectionInfo.textContent = "En fazla 5 aday seçebilirsiniz.";
                    selectionInfo.classList.remove('text-danger');
                }

                // Aday kartlarını oluştur
                data.candidates.forEach(candidate => {
                    const isChecked = votedIds.includes(candidate.id) ? 'checked' : '';
                    const card = document.createElement('div');
                    card.className = 'card text-center candidate-card';
                    card.style.width = '150px';
                    card.innerHTML = `
                        <img src="../uploads/${candidate.image}" class="card-img-top" style="height: 150px; object-fit: cover;">
                        <div class="card-body p-2">
                            <h6 class="card-title mb-2" style="font-size: 0.9rem;">${candidate.name}</h6>
                            <input class="form-check-input candidate-checkbox" type="checkbox" name="candidates[]" value="${candidate.id}" id="candidate${++index}" ${isChecked} ${hasVoted ? 'disabled' : ''}>
                            <label class="form-check-label" for="candidate${index}">Seç</label>
                        </div>
                    `;
                    container.appendChild(card);
                });

                updateCheckboxEvents(hasVoted);
            });
        });
    }

    function updateCheckboxEvents(isDisabled) {
        const checkboxes = document.querySelectorAll('.candidate-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.disabled = isDisabled;
            if (!isDisabled) {
                checkbox.addEventListener('change', () => updateSelection(checkboxes));
            }
            const card = checkbox.closest('.candidate-card');
            if (card) {
                if (!isDisabled) {
                    card.addEventListener('click', (e) => {
                        if (e.target.tagName === 'INPUT' || e.target.tagName === 'LABEL') return;
                        checkbox.checked = !checkbox.checked;
                        checkbox.dispatchEvent(new Event('change'));
                    });
                } else {
                    card.style.cursor = "not-allowed";
                    card.title = "Bu kulüpte zaten oy verdiniz.";
                }
            }
        });
        updateSelection(checkboxes);
    }

    function updateSelection(checkboxes) {
        const selectedCount = [...checkboxes].filter(cb => cb.checked).length;
        selectionInfo.textContent = `Seçtiğiniz aday sayısı: ${selectedCount}/5`;
        // Buton zaten hasVoted ile disable edildiği için burada tekrar kontrol etmeye gerek yok
    }

    if (votingForm) {
        votingForm.addEventListener('submit', function (e) {
            e.preventDefault();
            if (submitButton.disabled) return;
            const selectedCount = document.querySelectorAll('.candidate-checkbox:checked').length;
            if (selectedCount === 0 || selectedCount > 5) {
                alert('Lütfen en fazla 5 aday seçin.');
                return;
            }
            if (!clubSelect || !clubSelect.value) {
                alert('Lütfen bir kulüp seçin.');
                return;
            }
            const formData = new FormData(votingForm);
            formData.append('club_id', clubSelect.value);
            fetch('../backend/submit_vote.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                alert(data);
                if (data.includes('başarıyla')) {
                    document.querySelectorAll('.candidate-checkbox').forEach(cb => cb.checked = false);
                    updateSelection(document.querySelectorAll('.candidate-checkbox'));
                }
            })
            .catch(() => alert('Oy gönderilirken bir hata oluştu.'));
        });
    }
});
</script>


<script>
    //kulüp başkanı script
document.getElementById("searchInput").addEventListener("keyup", function () {
    let filter = this.value.toLowerCase();
    let members = document.querySelectorAll(".member-item");

    members.forEach(function (member) {
        let text = member.textContent.toLowerCase();
        member.style.display = text.includes(filter) ? "" : "none";
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function updateCardSelection() {
        document.querySelectorAll('.member-card').forEach(function(card) {
            const checkbox = card.querySelector('.candidate-checkbox');
            if (checkbox.checked) {
                card.classList.add('selected');
            } else {
                card.classList.remove('selected');
            }
        });
    }

    document.querySelectorAll('.member-card').forEach(function(card) {
        card.addEventListener('click', function(e) {
            // Sadece checkbox veya label'a tıklanmadıysa çalışsın
            if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'LABEL') {
                const checkbox = card.querySelector('.candidate-checkbox');
                checkbox.checked = !checkbox.checked;
                checkbox.dispatchEvent(new Event('change'));
            }
        });
        // Checkbox değiştiğinde arka planı güncelle
        const checkbox = card.querySelector('.candidate-checkbox');
        checkbox.addEventListener('change', updateCardSelection);
    });

    updateCardSelection();
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const candidateForm = document.getElementById('candidateForm');
    if (candidateForm) {
        candidateForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(candidateForm);

            fetch(candidateForm.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                alert(result.message);
                if (result.success) {
                    window.location.reload(); // Başarılıysa sayfayı yenile
                }
            })
            .catch(() => {
                alert('Bir hata oluştu.');
            });
        });
    }
});
</script>
 <script>
    // Seçim tarihleri formu için AJAX
    document.getElementById('electionSettingsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.success) window.location.reload();
        })
        .catch(() => alert('Bir hata oluştu.'));
    });

    // Bildirim gönderme formu için AJAX
    document.getElementById('notifyPresidentsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.success) this.reset();
        })
        .catch(() => alert('Bir hata oluştu.'));
    });
    </script>

    <script>
document.getElementById('applyElectionResultsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    if (!confirm('Seçim sonuçlarını uygulamak istediğinize emin misiniz? Bu işlem kulüp başkanlarını değiştirecek!')) return;
    fetch(this.action, { method: 'POST' })
        .then(res => res.text())
        .then(msg => alert(msg))
        .catch(() => alert('Bir hata oluştu.'));
});
</script>
</body>
</html>
