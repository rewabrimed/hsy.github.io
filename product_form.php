<?php
require_once 'auth.php';

$json_file = 'data/products.json';
$products = [];
if (file_exists($json_file)) {
    $products = json_decode(file_get_contents($json_file), true) ?: [];
}

$id = $_GET['id'] ?? null;
$product = ['name' => '', 'price' => '', 'currency' => 'TL', 'type' => 'AHD'];
$is_edit = false;

if ($id) {
    foreach ($products as $p) {
        if ($p['id'] === $id) {
            $product = $p;
            $is_edit = true;
            break;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $price = floatval($_POST['price'] ?? 0);
    $currency = $_POST['currency'] ?? 'TL';
    $type = $_POST['type'] ?? 'AHD';

    if ($name && $price) {
        if ($is_edit) {
            // Update existing
            foreach ($products as &$p) {
                if ($p['id'] === $id) {
                    $p['name'] = $name;
                    $p['price'] = $price;
                    $p['currency'] = $currency;
                    $p['type'] = $type;
                    break;
                }
            }
        } else {
            // Add new
            $products[] = [
                'id' => uniqid(),
                'name' => $name,
                'price' => $price,
                'currency' => $currency,
                'type' => $type
            ];
        }

        file_put_contents($json_file, json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header('Location: products.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1100, user-scalable=yes">
    <title><?php echo $is_edit ? 'Ürün Düzenle' : 'Yeni Ürün Ekle'; ?> - Teklif Sistemi</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-brand">HSY Güvenlik</a>
            <ul class="nav-menu">
                <li><a href="products.php" class="nav-link active">Ürünler</a></li>
                <li><a href="proposals.php" class="nav-link">Teklifler</a></li>
                <li><a href="bank_accounts.php" class="nav-link">Bankalar</a></li>
                <li><a href="logout.php" class="nav-link" style="color: var(--danger-color);">Çıkış</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="auth-card" style="margin: 0 auto; max-width: 600px;">
            <h2 class="auth-title" style="text-align: left; margin-bottom: 1.5rem;">
                <?php echo $is_edit ? 'Ürün Düzenle' : 'Yeni Ürün Ekle'; ?>
            </h2>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Ürün Adı</label>
                    <input type="text" name="name" class="form-control"
                        value="<?php echo htmlspecialchars($product['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Ürün Türü</label>
                    <select name="type" class="form-control">
                        <option value="AHD" <?php echo ($product['type'] ?? '') == 'AHD' ? 'selected' : ''; ?>>AHD (Analog
                            Kamera)</option>
                        <option value="IP" <?php echo ($product['type'] ?? '') == 'IP' ? 'selected' : ''; ?>>IP Kamera
                        </option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <div class="form-group" style="flex: 2;">
                        <label class="form-label">Fiyat</label>
                        <input type="number" step="0.01" name="price" class="form-control"
                            value="<?php echo htmlspecialchars($product['price']); ?>" required>
                    </div>

                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Para Birimi</label>
                        <select name="currency" class="form-control">
                            <option value="TL" <?php echo $product['currency'] == 'TL' ? 'selected' : ''; ?>>TL</option>
                            <option value="USD" <?php echo $product['currency'] == 'USD' ? 'selected' : ''; ?>>USD
                            </option>
                            <option value="EUR" <?php echo $product['currency'] == 'EUR' ? 'selected' : ''; ?>>EUR
                            </option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-between items-center mt-4">
                    <a href="products.php" style="color: var(--text-muted); text-decoration: none;">İptal</a>
                    <button type="submit"
                        class="btn btn-primary"><?php echo $is_edit ? 'Güncelle' : 'Kaydet'; ?></button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
