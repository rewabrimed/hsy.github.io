<?php
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=900, user-scalable=yes">
    <title>Ana Sayfa - Teklif Sistemi</title>
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
                <li><a href="bank_accounts.php" class="nav-link">Bankalar</a></li>
                <li><a href="logout.php" class="nav-link" style="color: var(--danger-color);">Çıkış</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Hoşgeldiniz</h2>
            </div>
            <div style="padding: 2rem; text-align: center;">
                <p style="margin-bottom: 2rem; color: var(--text-muted);">Lütfen yapmak istediğiniz işlemi seçin.</p>
                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <a href="products.php" class="btn btn-primary">Ürün Yönetimi</a>
                    <a href="proposals.php" class="btn btn-primary" style="background-color: var(--text-color);">Teklif
                        Hazırla</a>
                    <a href="bank_accounts.php" class="btn btn-primary" style="background-color: #10b981;">Banka
                        Hesapları</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>