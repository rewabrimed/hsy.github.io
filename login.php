<?php
session_start();

// Şifre kontrolü (Basitlik için hardcoded)
$stored_password = 'admin'; // Geliştirme kolaylığı için 'admin' olarak güncelledim.

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'] ?? '';

    if ($password === $stored_password) {
        $_SESSION['loggedin'] = true;
        header("Location: index.php");
        exit;
    } else {
        $error = "Hatalı şifre!";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1100, user-scalable=yes">
    <title>Giriş Yap - Teklif Sistemi</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="auth-wrapper">
    <div class="auth-card">
        <h1 class="auth-title">Giriş Yap</h1>
        
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label" for="password">Yönetici Şifresi</label>
                <input type="password" id="password" name="password" class="form-control" required placeholder="Şifrenizi girin">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Giriş Yap</button>
        </form>
    </div>
</body>
</html>
