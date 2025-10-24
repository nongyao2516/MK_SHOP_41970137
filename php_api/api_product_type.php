<?php
// เชื่อมต่อฐานข้อมูล
include 'condb.php';

try {
    // เตรียม SQL ดึงข้อมูลประเภทสินค้า
    $stmt = $conn->prepare("SELECT type_id, type_name FROM product_type ORDER BY type_name ASC");
    $stmt->execute();
    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $types
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
?>
