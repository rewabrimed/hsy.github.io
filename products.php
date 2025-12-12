<?php
require_once 'auth.php';

$json_file = 'data/products.json';
$products = [];
if (file_exists($json_file)) {
    $json_content = file_get_contents($json_file);
    $products = json_decode($json_content, true) ?: [];
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=900, user-scalable=yes">
    <title>Ürün Yönetimi - Teklif Sistemi</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-brand">
                <img src="img/logo.png" alt="Logo" style="height: 40px; margin-right: 10px; vertical-align: middle;">
                HSY Güvenlik
            </a>
            <ul class="nav-menu">
                <li><a href="products.php" class="nav-link active">Ürünler</a></li>
                <li><a href="proposals.php" class="nav-link">Teklifler</a></li>
                <li><a href="bank_accounts.php" class="nav-link">Bankalar</a></li>
                <li><a href="logout.php" class="nav-link" style="color: var(--danger-color);">Çıkış</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="d-flex justify-between items-center mb-4">
            <h1 style="font-size: 1.5rem; font-weight: 700;">Ürün Listesi</h1>
            <a href="product_form.php" class="btn btn-primary">Yeni Ürün Ekle</a>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table products-table">
                    <thead>
                        <tr>
                            <th>Ürün Adı</th>
                            <th>Tür</th>
                            <th>Fiyat</th>
                            <th>Para Birimi</th>
                            <th style="text-align: right;">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--text-muted);">Henüz ürün eklenmemiş.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['type'] ?? '-'); ?></td>
                                    <td><?php echo number_format($product['price'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($product['currency']); ?></td>
                                    <td style="text-align: right;">
                                        <a href="product_form.php?id=<?php echo $product['id']; ?>" class="btn btn-primary"
                                            style="padding: 0.5rem 1rem; font-size: 0.875rem; background-color: var(--text-color);">Düzenle</a>
                                        <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="btn btn-danger"
                                            style="padding: 0.5rem 1rem; font-size: 0.875rem;"
                                            onclick="return confirm('Silmek istediğinize emin misiniz?');">Sil</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="js/swipe.js?v=<?php echo time(); ?>"></script>
</body>

</html>