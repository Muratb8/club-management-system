<?php 
$sql = "SELECT cm.id, u.name AS ogrenciAdi, cm.status, cm.created_at
        FROM clubmembers cm
        JOIN users u ON cm.user_id = u.id
        WHERE cm.club_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $kulup_id);  // club_president oturumundan kulüp id'si alınabilir
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['ogrenciAdi']}</td>";
    echo "<td>{$row['created_at']}</td>";
    echo "<td><span class='badge bg-".($row['status'] == 'pending' ? 'warning' : ($row['status'] == 'approved' ? 'success' : 'danger'))."'>" . ucfirst($row['status']) . "</span></td>";
    echo "<td>
            <button class='btn btn-success onaylaBtn' data-id='{$row['id']}'>Onayla</button>
            <button class='btn btn-danger reddetBtn' data-id='{$row['id']}'>Reddet</button>
          </td>";
    echo "</tr>";
}
?>