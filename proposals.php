<?php
require_once 'auth.php';

$json_file = 'data/proposals.json';
$proposals = [];
if (file_exists($json_file)) {
    $proposals = json_decode(file_get_contents($json_file), true) ?: [];
}
// Sort by date desc
usort($proposals, function ($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=900, user-scalable=yes">
    <title>Teklifler - Teklif Sistemi</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
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
                <li><a href="proposals.php" class="nav-link active">Teklifler</a></li>
                <li><a href="bank_accounts.php" class="nav-link">Bankalar</a></li>
                <li><a href="logout.php" class="nav-link" style="color: var(--danger-color);">Çıkış</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="d-flex justify-between items-center mb-4">
            <h1 style="font-size: 1.5rem; font-weight: 700;">Teklif Geçmişi</h1>
            <a href="proposal_form.php" class="btn btn-primary">Yeni Teklif Oluştur</a>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Müşteri</th>
                            <th>Toplam Tutar</th>
                            <th style="text-align: right;">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($proposals)): ?>
                            <tr>
                                <td colspan="3" style="text-align: center; color: var(--text-muted);">Henüz teklif
                                    oluşturulmamış.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($proposals as $prop): ?>
                                <tr>
                                    <td>
                                        <a href="view_proposal.php?id=<?php echo $prop['id']; ?>"
                                            style="text-decoration: none; color: inherit; font-weight: 700; font-size: 1.0rem; display: block; width: 100%;">
                                            <?php echo htmlspecialchars($prop['customer']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; justify-content: space-between;">
                                            <span><?php echo htmlspecialchars($prop['grand_total_display']); ?></span>
                                            <span style="font-size: 0.55rem; color: var(--text-muted); white-space: nowrap;">
                                                <?php echo date('d.m.Y H:i', strtotime($prop['created_at'])); ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td style="text-align: right;">
                                        <!-- Görüntüle butonu kaldırıldı, isme tıklayınca gidiyor -->
                                        <!-- Edit/Delete butonları SWIPE için gerekli, mobilde CSS ile gizli -->
                                        <div class="d-flex gap-2" style="justify-content: flex-end;">
                                            <a href="view_proposal.php?id=<?php echo $prop['id']; ?>" class="btn btn-primary"
                                                style="padding: 0.5rem 1rem; font-size: 0.875rem; background-color: #2563eb;">Görüntüle</a>
                                            <a href="proposal_form.php?id=<?php echo $prop['id']; ?>" class="btn btn-primary"
                                                style="padding: 0.5rem 1rem; font-size: 0.875rem; background-color: var(--text-color);">Düzenle</a>
                                            <a href="delete_proposal.php?id=<?php echo $prop['id']; ?>" class="btn btn-danger"
                                                style="padding: 0.5rem 1rem; font-size: 0.875rem;"
                                                onclick="return confirm('Bu teklifi silmek istediğinize emin misiniz?');">Sil</a>
                                        </div>
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