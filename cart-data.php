<?php
require_once 'config/functions.php';

header('Content-Type: application/json; charset=utf-8');

$rawIds = $_GET['ids'] ?? '';
$ids = array_values(array_unique(array_filter(array_map('intval', explode(',', $rawIds)), static function ($id) {
    return $id > 0;
})));

if (empty($ids)) {
    echo json_encode(['items' => []]);
    exit;
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT l.listing_id, l.title, l.description, l.price, l.condition_status, l.image_paths,
                       c.category_name,
                       u.username as seller_name,
                       u.township
                       FROM listings l
                       JOIN categories c ON l.category_id = c.category_id
                       JOIN users u ON l.seller_id = u.user_id
                       WHERE l.status = 'active' AND l.listing_id IN ($placeholders)");
$stmt->execute($ids);

$items = [];
while ($row = $stmt->fetch()) {
    $images = json_decode($row['image_paths'] ?? '[]', true);
    if (!is_array($images) || empty($images)) {
        $images = ['assets/images/no-image.jpg'];
    }

    $items[] = [
        'listing_id' => (int) $row['listing_id'],
        'title' => $row['title'],
        'description' => $row['description'],
        'price' => (float) $row['price'],
        'condition_status' => $row['condition_status'],
        'condition_label' => ucfirst($row['condition_status']),
        'category_name' => $row['category_name'],
        'seller_name' => $row['seller_name'],
        'township' => $row['township'],
        'image_url' => $images[0],
    ];
}

echo json_encode(['items' => $items]);