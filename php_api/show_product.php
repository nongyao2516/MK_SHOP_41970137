<?php
// เชื่อมต่อฐานข้อมูล
include 'condb.php';

try {
    // ตรวจสอบคำขอจาก Client
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method == 'GET') {
        // ✅ ดึงข้อมูลสินค้าพร้อมชื่อประเภทสินค้า
        $stmt = $conn->prepare("
            SELECT 
                p.product_id,
                p.product_name,
                p.description,
                p.price,
                p.stock,
                p.image,
                p.type_id,
                t.type_name
            FROM products p
            LEFT JOIN product_type t ON p.type_id = t.type_id
            ORDER BY p.product_id ASC
        ");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(["success" => true, "data" => $data]);

    } else {
        echo json_encode(["success" => false, "message" => "Invalid request method"]);
    }

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
