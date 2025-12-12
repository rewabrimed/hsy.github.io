<?php
require_once 'auth.php';

$json_file = 'data/bank_accounts.json';
$accounts = [];
if (file_exists($json_file)) {
    $accounts = json_decode(file_get_contents($json_file), true) ?: [];
}

$id = $_GET['id'] ?? null;
$account = ['bank_name' => '', 'account_holder' => '', 'iban' => 'TR', 'currency' => 'TL', 'show_in_proposal' => true];
$is_edit = false;

if ($id) {
    foreach ($accounts as $acc) {
        if ($acc['id'] === $id) {
            $account = $acc;
            $is_edit = true;
            break;
        }
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bank_name = $_POST['bank_name'] ?? '';
    $account_holder = $_POST['account_holder'] ?? '';
    $iban = strtoupper(str_replace(' ', '', $_POST['iban'] ?? ''));
    $currency = $_POST['currency'] ?? 'TL';
    $show_in_proposal = isset($_POST['show_in_proposal']);

    // Basit Validasyon
    if (substr($iban, 0, 2) !== 'TR') {
        $error = 'IBAN TR ile başlamalıdır.';
    } elseif (strlen($iban) != 26) {
        $error = 'IBAN 26 karakter olmalıdır (TR dahil).';
    }

    if (!$error && $bank_name && $iban) {
        if ($is_edit) {
            foreach ($accounts as &$acc) {
                if ($acc['id'] === $id) {
                    $acc['bank_name'] = $bank_name;
                    $acc['account_holder'] = $account_holder;
                    $acc['iban'] = $iban;
                    $acc['currency'] = $currency;
                    $acc['show_in_proposal'] = $show_in_proposal;
                    break;
                }
            }
        } else {
            $accounts[] = [
                'id' => uniqid(),
                'bank_name' => $bank_name,
                'account_holder' => $account_holder,
                'iban' => $iban,
                'currency' => $currency,
                'show_in_proposal' => $show_in_proposal
            ];
        }

        file_put_contents($json_file, json_encode($accounts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header('Location: bank_accounts.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1100, user-scalable=yes">
    <title>Banka Hesabı <?php echo $is_edit ? 'Düzenle' : 'Ekle'; ?> - HSY Güvenlik</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-brand">HSY Güvenlik</a>
            <ul class="nav-menu">
                <li><a href="products.php" class="nav-link">Ürünler</a></li>
                <li><a href="proposals.php" class="nav-link">Teklifler</a></li>
                <li><a href="bank_accounts.php" class="nav-link active">Bankalar</a></li>
                <li><a href="logout.php" class="nav-link" style="color: var(--danger-color);">Çıkış</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="auth-card" style="margin: 0 auto; max-width: 600px;">
            <h2 class="auth-title" style="text-align: left; margin-bottom: 1.5rem;">
                <?php echo $is_edit ? 'Hesap Düzenle' : 'Yeni Hesap Ekle'; ?>
            </h2>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Banka Adı</label>
                    <input type="text" name="bank_name" class="form-control"
                        value="<?php echo htmlspecialchars($account['bank_name']); ?>" required
                        placeholder="Örn: Ziraat Bankası">
                </div>

                <div class="form-group">
                    <label class="form-label">Hesap Sahibi (Ad Soyad)</label>
                    <input type="text" name="account_holder" class="form-control"
                        value="<?php echo htmlspecialchars($account['account_holder'] ?? ''); ?>" required
                        placeholder="Örn: HSY Güvenlik Ltd. Şti.">
                </div>

                <div class="form-group">
                    <label class="form-label">IBAN (TR ile başlayıp 26 hane olmalı)</label>
                    <input type="text" name="iban" class="form-control"
                        value="<?php echo htmlspecialchars($account['iban']); ?>" required placeholder="TR..."
                        maxlength="32">
                    <small style="color: var(--text-muted);">Boşluksuz yazınız.</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Para Birimi</label>
                    <select name="currency" class="form-control">
                        <option value="TL" <?php echo $account['currency'] == 'TL' ? 'selected' : ''; ?>>TL</option>
                        <option value="USD" <?php echo $account['currency'] == 'USD' ? 'selected' : ''; ?>>USD</option>
                        <option value="EUR" <?php echo $account['currency'] == 'EUR' ? 'selected' : ''; ?>>EUR</option>
                    </select>
                </div>

                <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem; margin-top: 1rem;">
                    <input type="checkbox" id="show_in_proposal" name="show_in_proposal" <?php echo $account['show_in_proposal'] ? 'checked' : ''; ?>>
                    <label for="show_in_proposal" style="cursor: pointer;">Bu hesabı teklif formunda göster</label>
                </div>

                <div class="d-flex justify-between items-center mt-4">
                    <a href="bank_accounts.php" style="color: var(--text-muted); text-decoration: none;">İptal</a>
                    <button type="submit"
                        class="btn btn-primary"><?php echo $is_edit ? 'Güncelle' : 'Kaydet'; ?></button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
