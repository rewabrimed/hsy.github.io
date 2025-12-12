<?php
require_once 'auth.php';

$json_file = 'data/bank_accounts.json';
$accounts = [];
if (file_exists($json_file)) {
    $accounts = json_decode(file_get_contents($json_file), true) ?: [];
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=900, user-scalable=yes">
    <title>Banka Hesapları - HSY Güvenlik</title>
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
                <li><a href="products.php" class="nav-link">Ürünler</a></li>
                <li><a href="proposals.php" class="nav-link">Teklifler</a></li>
                <li><a href="bank_accounts.php" class="nav-link active">Bankalar</a></li>
                <li><a href="logout.php" class="nav-link" style="color: var(--danger-color);">Çıkış</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="d-flex justify-between items-center mb-4">
            <h1 style="font-size: 1.5rem; font-weight: 700;">Banka Hesapları</h1>
            <a href="bank_account_form.php" class="btn btn-primary">Yeni Hesap Ekle</a>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Banka Adı</th>
                            <th>Hesap Sahibi</th>
                            <th>IBAN</th>
                            <th style="text-align: right;">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($accounts)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--text-muted);">Henüz banka hesabı
                                    eklenmemiş.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($accounts as $acc): ?>
                                <?php
                                $rowStyle = '';
                                if (!$acc['show_in_proposal']) {
                                    // Teklifte gösterilmeyenler için kırmızımsı arka plan
                                    // Mobilde bu satır bir "kart" olduğu için kartın rengi değişecek
                                    $rowStyle = 'background-color: #fef2f2;';
                                }
                                ?>
                                <tr style="<?php echo $rowStyle; ?>">
                                    <td style="font-weight: 700; font-size: 1.1rem; color: var(--primary-color);">
                                        <div style="display: flex; align-items: center; justify-content: space-between;">
                                            <span><?php echo htmlspecialchars($acc['bank_name']); ?></span>
                                            <span
                                                style="font-size: 0.8rem; font-weight: 500; color: #64748b; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">
                                                <?php echo htmlspecialchars($acc['currency']); ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($acc['account_holder'] ?? '-'); ?></td>
                                    <td style="font-family: monospace;"><?php echo htmlspecialchars($acc['iban']); ?></td>
                                    <!-- Teklifte Göster sütunu kaldırıldı (Renk ile ifade ediliyor) -->
                                    <td style="text-align: right;">
                                        <!-- Edit/Delete butonları SWIPE için gerekli, mobilde CSS ile gizleniyor -->
                                        <a href="bank_account_form.php?id=<?php echo $acc['id']; ?>" class="btn btn-primary"
                                            style="padding: 0.5rem 1rem; font-size: 0.875rem; background-color: var(--text-color);">Düzenle</a>
                                        <a href="delete_bank_account.php?id=<?php echo $acc['id']; ?>" class="btn btn-danger"
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