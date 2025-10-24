<?php
include 'condb.php';

$action = $_POST['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {
    switch($action) {
        case 'add':
            $product_name = $_POST['product_name'];
            $type_id = $_POST['type_id'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $stock = $_POST['stock'];

            $filename = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $targetDir = "uploads/";
                if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                $filename = time() . '_' . basename($_FILES['image']['name']);
                move_uploaded_file($_FILES['image']['tmp_name'], $targetDir . $filename);
            }

            $sql = "INSERT INTO products (product_name, type_id, description, price, stock, image)
                    VALUES (:product_name, :type_id, :description, :price, :stock, :image)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':product_name', $product_name);
            $stmt->bindParam(':type_id', $type_id);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':stock', $stock);
            $stmt->bindParam(':image', $filename);

            echo json_encode($stmt->execute() 
                ? ["success" => true, "message" => "เพิ่มสินค้าสำเร็จ"] 
                : ["success" => false, "error" => "เพิ่มสินค้าล้มเหลว"]);
            break;

        case 'update':
            $product_id = $_POST['product_id'];
            $product_name = $_POST['product_name'];
            $type_id = $_POST['type_id'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $stock = $_POST['stock'];

            $imageSQL = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $filename = time() . '_' . basename($_FILES['image']['name']);
                move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $filename);
                $imageSQL = ", image = :image";
            }

            $sql = "UPDATE products SET 
                        product_name = :product_name,
                        type_id = :type_id,
                        description = :description,
                        price = :price,
                        stock = :stock
                        $imageSQL
                    WHERE product_id = :product_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':product_name', $product_name);
            $stmt->bindParam(':type_id', $type_id);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':stock', $stock);
            $stmt->bindParam(':product_id', $product_id);
            if (isset($filename)) $stmt->bindParam(':image', $filename);

            echo json_encode($stmt->execute() 
                ? ["success" => true, "message" => "แก้ไขสินค้าสำเร็จ"] 
                : ["success" => false, "error" => "แก้ไขสินค้าล้มเหลว"]);
            break;

        case 'delete':
            $product_id = $_POST['product_id'];
            $stmt = $conn->prepare("SELECT image FROM products WHERE product_id = :product_id");
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product && !empty($product['image'])) {
                $filePath = "uploads/" . $product['image'];
                if (file_exists($filePath)) unlink($filePath);
            }

            $stmt = $conn->prepare("DELETE FROM products WHERE product_id = :product_id");
            $stmt->bindParam(':product_id', $product_id);

            echo json_encode($stmt->execute() 
                ? ["success" => true, "message" => "ลบสินค้าสำเร็จ"] 
                : ["success" => false, "error" => "ลบสินค้าล้มเหลว"]);
            break;

        default:
            echo json_encode(["success" => false, "error" => "Action ไม่ถูกต้อง"]);
            break;
    }

} else {
    // GET: ดึงข้อมูลสินค้าพร้อมประเภท
    $stmt = $conn->prepare("SELECT p.*, t.type_name 
                            FROM products p
                            LEFT JOIN product_type t ON p.type_id = t.type_id
                            ORDER BY p.product_id DESC");
    if ($stmt->execute()) {
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "data" => $products], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["success" => false, "data" => []]);
    }
}
?>
