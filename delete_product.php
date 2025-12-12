<?php
require_once 'auth.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $json_file = 'data/products.json';
    if (file_exists($json_file)) {
        $products = json_decode(file_get_contents($json_file), true) ?: [];

        $products = array_filter($products, function ($p) use ($id) {
            return $p['id'] !== $id;
        });

        // Re-index array needed for json_encode to output array, not object if keys are missing
        $products = array_values($products);

        file_put_contents($json_file, json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}

header('Location: products.php');
exit;
?>
