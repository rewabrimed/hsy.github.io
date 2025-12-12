<?php
require_once 'auth.php';

$json_file = 'data/products.json';
$products = [];
if (file_exists($json_file)) {
    $products = json_decode(file_get_contents($json_file), true) ?: [];
}

$id = $_GET['id'] ?? null;
$proposal = null;
$customer_val = '';
$existing_items = [];

if ($id) {
    $prop_file = 'data/proposals.json';
    if (file_exists($prop_file)) {
        $all_props = json_decode(file_get_contents($prop_file), true) ?: [];
        foreach ($all_props as $p) {
            if ($p['id'] === $id) {
                $proposal = $p;
                $customer_val = $p['customer'];
                $existing_items = $p['items'];
                break;
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer = $_POST['customer'] ?? 'Müşteri';
    $raw_items = $_POST['items'] ?? [];

    $items = [];
    $totals_by_currency = [];
    $grand_total_display = "";

    // Parse array columns
    if (isset($raw_items['product_id'])) {
        for ($i = 0; $i < count($raw_items['product_id']); $i++) {
            if (empty($raw_items['product_id'][$i]))
                continue;

            $currency = $raw_items['currency'][$i];
            $total = floatval($raw_items['total'][$i]);

            $items[] = [
                'product_id' => $raw_items['product_id'][$i],
                'product_name' => $raw_items['product_name'][$i],
                'price' => floatval($raw_items['price'][$i]),
                'quantity' => intval($raw_items['quantity'][$i]),
                'currency' => $currency,
                'total' => $total
            ];

            if (!isset($totals_by_currency[$currency]))
                $totals_by_currency[$currency] = 0;
            $totals_by_currency[$currency] += $total;
        }
    }

    foreach ($totals_by_currency as $curr => $amount) {
        $grand_total_display .= number_format($amount, 2) . " " . $curr . " + ";
    }
    $grand_total_display = rtrim($grand_total_display, " + ");

    $proposals_file = 'data/proposals.json';
    $proposals = [];
    if (file_exists($proposals_file)) {
        $proposals = json_decode(file_get_contents($proposals_file), true) ?: [];
    }

    if ($id && $proposal) {
        // Update
        foreach ($proposals as &$p) {
            if ($p['id'] === $id) {
                $p['customer'] = $customer;
                $p['items'] = $items;
                $p['grand_total_display'] = $grand_total_display;
                // Keep original created_at or update updated_at if needed
                break;
            }
        }
    } else {
        // New
        $new_proposal = [
            'id' => uniqid(),
            'created_at' => date('Y-m-d H:i:s'),
            'customer' => $customer,
            'items' => $items,
            'grand_total_display' => $grand_total_display
        ];
        $proposals[] = $new_proposal;
    }

    file_put_contents($proposals_file, json_encode($proposals, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    header('Location: proposals.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=900, user-scalable=yes">
    <title><?php echo $id ? 'Teklifi Düzenle' : 'Yeni Teklif Oluştur'; ?> - Teklif Sistemi</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        window.productsData = <?php echo json_encode($products); ?>;
        window.existingItems = <?php echo json_encode($existing_items); ?>;
    </script>
</head>

<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-brand">
                <img src="img/logo.png" alt="Logo" style="height: 40px; margin-right: 10px; vertical-align: middle;">
                HSY Güvenlik
            </a>
            <ul class="nav-menu">
                <li><a href="products.php" class="nav-link">Ürünler</a></li>
                <li><a href="proposals.php" class="nav-link active">Teklifler</a></li>
                <li><a href="bank_accounts.php" class="nav-link">Bankalar</a></li>
                <li><a href="logout.php" class="nav-link" style="color: var(--danger-color);">Çıkış</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="card" style="padding: 2rem;">
            <h2 class="auth-title" style="text-align: left; margin-bottom: 2rem;">
                <?php echo $id ? 'Teklifi Düzenle' : 'Yeni Teklif Hazırla'; ?>
            </h2>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Müşteri Adı / Firma</label>
                    <input type="text" name="customer" class="form-control" required placeholder="Firma adını girin"
                        value="<?php echo htmlspecialchars($customer_val); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Ürünler</label>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Ürün</th>
                                    <th>Adet</th>
                                    <th>Ara Toplam</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="proposal-items">
                                <!-- JS ile doldurulacak -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2"
                                        style="text-align: right; font-weight: 700; border: none; padding-top: 1.5rem;">
                                        GENEL TOPLAM:</td>
                                    <td colspan="2"
                                        style="font-weight: 700; font-size: 1.125rem; color: var(--primary-color); border: none; padding-top: 1.5rem; text-align: right;"
                                        id="grand-total">0.00</td>
                                </tr>
                            </tfoot>
                        </table>

                        <div style="margin-top: 1rem;">
                            <button type="button" id="add-row-btn" class="btn btn-primary"
                                style="width: 100%; background-color: var(--background-color); color: var(--primary-color); border: 2px dashed var(--border-color); font-weight: 600; padding: 1rem;">
                                + Yeni Satır Ekle
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-between items-center mt-4">
                        <a href="proposals.php" style="color: var(--text-muted); text-decoration: none;">İptal</a>
                        <button type="submit"
                            class="btn btn-primary"><?php echo $id ? 'Değişiklikleri Kaydet' : 'Teklifi Kaydet'; ?></button>
                    </div>
            </form>
        </div>
    </div>
    <script src="js/app.js?v=<?php echo time(); ?>"></script>
</body>

</html>