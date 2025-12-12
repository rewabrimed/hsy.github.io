<?php
// require_once 'auth.php'; // Conditional include below
session_start();
date_default_timezone_set('Europe/Istanbul');

$id = $_GET['id'] ?? null;
$token = $_GET['token'] ?? null;
$proposal = null;
$secret_salt = 'hsy_guvenlik_secure_salt_2024'; // Basit güvenlik anahtarı

// Token Doğrulama
$expected_token = md5($id . $secret_salt);
$is_public_view = ($token === $expected_token);
$is_admin = (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true);

// Erişim Kontrolü
if (!$is_public_view && !$is_admin) {
    header('Location: login.php');
    exit;
}

if ($id) {
    $json_file = 'data/proposals.json';
    if (file_exists($json_file)) {
        $proposals = json_decode(file_get_contents($json_file), true) ?: [];
        foreach ($proposals as $p) {
            if ($p['id'] === $id) {
                $proposal = $p;
                break;
            }
        }
    }
}

if (!$proposal) {
    echo "Teklif bulunamadı!";
    exit;
}

// Kur Bilgilerini Çekme
$rates_url = "https://finans.truncgil.com/today.json";
$usd_rate = 0;
$eur_rate = 0;
$rates_fetched = false;

// Banka Hesaplarını Çekme
$bank_accounts = [];
$bank_json = 'data/bank_accounts.json';
if (file_exists($bank_json)) {
    $all_accounts = json_decode(file_get_contents($bank_json), true) ?: [];
    // Sadece gösterime açık olanlar
    $bank_accounts = array_filter($all_accounts, function ($acc) {
        return isset($acc['show_in_proposal']) && $acc['show_in_proposal'];
    });
}

// Kur çekme fonksiyonu
function fetchRates($url)
{
    $data = null;

    // 1. Yöntem: cURL
    if (function_exists('curl_init')) {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36');
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $json = curl_exec($ch);

            if (!curl_errno($ch) && $json) {
                $data = json_decode($json, true);
            }
            curl_close($ch);
        } catch (Exception $e) {
        }
    }

    // 2. Yöntem: file_get_contents (cURL başarısızsa veya yoksa)
    if (!$data && ini_get('allow_url_fopen')) {
        try {
            $arrContextOptions = array(
                "ssl" => array(
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                ),
                "http" => array(
                    "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36",
                    "timeout" => 5
                )
            );
            $json = @file_get_contents($url, false, stream_context_create($arrContextOptions));
            if ($json) {
                $data = json_decode($json, true);
            }
        } catch (Exception $e) {
        }
    }

    return $data;
}

$data = fetchRates($rates_url);

if ($data) {
    $usd_rate = floatval(str_replace(',', '.', $data['USD']['Satış'] ?? 0));
    $eur_rate = floatval(str_replace(',', '.', $data['EUR']['Satış'] ?? 0));
    $rates_fetched = true;
}

// Genel Toplam Hesaplama (TL Tabanlı)
$total_tl = 0;
foreach ($proposal['items'] as $item) {
    if ($usd_rate > 0 && $eur_rate > 0) {
        $price = floatval($item['total']);
        if ($item['currency'] == 'TL') {
            $total_tl += $price;
        } elseif ($item['currency'] == 'USD') {
            $total_tl += $price * $usd_rate;
        } elseif ($item['currency'] == 'EUR') {
            $total_tl += $price * $eur_rate;
        }
    }
}

$display_usd = ($usd_rate > 0) ? ($total_tl / $usd_rate) : 0;
$display_eur = ($eur_rate > 0) ? ($total_tl / $eur_rate) : 0;
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=900, user-scalable=yes">

    <!-- Open Graph / WhatsApp Sharing Tags -->
    <meta property="og:title"
        content="Teklif: <?php echo htmlspecialchars($proposal['customer']); ?> - <?php echo date('d.m.Y'); ?>">
    <meta property="og:description" content="HSY Güvenlik sistemleri tarafından size özel hazırlanan teklif formudur.">
    <meta property="og:image"
        content="<?php echo (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['PHP_SELF']); ?>/img/logo.png">
    <meta property="og:url"
        content="<?php echo (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>">
    <meta property="og:type" content="website">

    <title>Teklif: <?php echo htmlspecialchars($proposal['customer']); ?> - <?php echo date('d.m.Y'); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @media print {
            @page {
                margin: 0.5cm 1cm;
                size: A4;
            }

            .navbar,
            .no-print {
                display: none !important;
            }

            /* Force Desktop Table Layout */
            .table-responsive {
                overflow: visible !important;
            }

            .table {
                width: 100% !important;
                border-collapse: collapse !important;
                display: table !important;
            }

            .table thead {
                display: table-header-group !important;
            }

            .table tbody {
                display: table-row-group !important;
            }

            .table tr {
                display: table-row !important;
                margin-bottom: 0 !important;
                border: none !important;
                box-shadow: none !important;
                background: transparent !important;
            }

            .table th,
            .table td {
                display: table-cell !important;
                width: auto !important;
                border-bottom: 1px solid #ddd !important;
                padding: 0.4rem !important;
                font-size: 0.8rem !important;
                text-align: left !important;
            }

            .table td:last-child {
                text-align: right !important;
            }

            /* ...and other existing rules... */
            .container {
                max-width: 100%;
                padding: 0;
            }

            .card {
                box-shadow: none;
                padding: 0.5rem !important;
                border: none;
            }

            body,
            html {
                height: 100%;
                margin: 0;
                padding: 0;
                background-color: white !important;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .container,
            .card,
            #bank-info,
            #currency-info {
                background-color: white !important;
                box-shadow: none !important;
            }

            .logo-wrapper {
                margin-bottom: 0.5rem !important;
                padding-bottom: 0.5rem !important;
                border-bottom: 1px solid var(--border-color) !important;
            }

            .view-proposal-logo-area img {
                max-height: 60px !important;
            }

            #bank-info {
                position: fixed;
                bottom: 0;
                left: 0;
                width: 100%;
                background: white;
                padding-top: 5px;
                border-top: 1px solid #ccc;
            }

            .content-wrapper {
                margin-bottom: 150px;
            }

            h1 {
                font-size: 1.25rem !important;
                margin-bottom: 0.5rem !important;
                margin-top: 0 !important;
            }

            h3 {
                font-size: 1rem !important;
            }

            .card {
                page-break-inside: avoid;
            }

            #bank-info,
            #currency-info {
                background-color: white !important;
                border: 1px solid #ddd !important;
                margin-top: 0.5rem !important;
            }
        }

        /* Shared Print Styles for HTML2PDF */
        .pdf-mode .navbar,
        .pdf-mode .no-print {
            display: none !important;
        }

        .pdf-mode .table-responsive {
            overflow: visible !important;
        }

        .pdf-mode .table {
            width: 100% !important;
            border-collapse: collapse !important;
            display: table !important;
        }

        .pdf-mode .table thead {
            display: table-header-group !important;
        }

        .pdf-mode .table tbody {
            display: table-row-group !important;
        }

        .pdf-mode .table tr {
            display: table-row !important;
            margin-bottom: 0 !important;
            border: none !important;
            box-shadow: none !important;
            background: transparent !important;
        }

        .pdf-mode .table th,
        .pdf-mode .table td {
            display: table-cell !important;
            width: auto !important;
            border-bottom: 1px solid #ddd !important;
            padding: 0.4rem !important;
            font-size: 0.8rem !important;
            text-align: left !important;
        }

        .pdf-mode .table td:last-child {
            text-align: right !important;
        }

        .pdf-mode .container {
            max-width: none !important;
            width: 750px !important;
            /* Adjusted for better A4 fit */
            padding: 0;
            margin: 0 !important;
            /* Align left to avoid centering shift */
        }

        .pdf-mode.card,
        .pdf-mode .card {
            box-shadow: none;
            padding: 0.5rem !important;
            border: none;
            width: 100% !important;
            /* Fill the forced container */
            min-height: 28.5cm;
            /* Extended to fill A4 page for footer positioning */
            position: relative;
        }

        .pdf-mode #bank-info {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: white !important;
            box-shadow: none !important;
            border: 1px solid #ddd !important;
            margin-top: 0.5rem !important;
            padding-top: 5px;
        }

        .pdf-mode #currency-info {
            background-color: white !important;
            box-shadow: none !important;
            border: 1px solid #ddd !important;
            margin-top: 0.5rem !important;
        }

        .pdf-mode .logo-wrapper {
            margin-bottom: 0.5rem !important;
            padding-bottom: 0.5rem !important;
            border-bottom: 1px solid var(--border-color) !important;
        }

        .pdf-mode .view-proposal-logo-area img {
            max-height: 60px !important;
        }

        .pdf-mode h1 {
            font-size: 1.25rem !important;
            margin-bottom: 0.5rem !important;
            margin-top: 0 !important;
        }

        .pdf-mode h3 {
            font-size: 1rem !important;
        }

        .pdf-mode .table th,
        .pdf-mode .table td {
            padding: 0.2rem !important;
            font-size: 0.75rem !important;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>

<body>
    <?php if ($is_admin): ?>
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
    <?php endif; ?>

    <div class="container">
        <div class="d-flex justify-between items-center mb-4 no-print" style="gap: 0.5rem;">
            <?php if ($is_admin): ?>
                <a href="proposals.php" class="btn btn-primary" style="background-color: var(--text-muted); flex: 1;">Geri
                    Dön</a>
            <?php else: ?>
                <div style="flex: 1;"></div> <!-- Spacer for public view layout balance -->
            <?php endif; ?>

            <div style="display: flex; gap: 0.5rem; flex: 2; justify-content: flex-end;">
                <?php if ($is_admin): ?>
                    <button onclick="shareWhatsapp()" class="btn btn-primary"
                        style="background-color: #25D366;">WhatsApp</button>
                <?php endif; ?>
                <button onclick="downloadPNG()" class="btn btn-primary" style="background-color: #d97706;">PNG
                    İndir</button>
                <button onclick="downloadPDF()" class="btn btn-primary" style="background-color: #dc2626;">PDF
                    İndir</button>
                <button onclick="window.print()" class="btn btn-primary">Yazdır</button>
            </div>
        </div>

        <div class="card" id="proposal-content" style="padding: 3rem;">
            <!-- Logo area -->
            <div class="logo-wrapper"
                style="margin-bottom: 1rem; border-bottom: 2px solid var(--border-color); padding-bottom: 1rem;">
                <div class="view-proposal-logo-area"
                    style="display: flex; justify-content: space-between; align-items: center;">
                    <img src="img/logo.png" alt="HSY Güvenlik Logo" style="max-height: 80px; width: auto;">
                    <div style="text-align: right; color: var(--text-muted); font-size: 0.875rem;">
                        <strong>HSY Güvenlik ve Kamera Sistemleri</strong><br>
                        Güvenliğiniz, Önceliğimizdir.
                    </div>
                </div>
            </div>

            <div>
                <div style="text-align: center; margin-bottom: 0.5rem;">
                    <h1 style="font-size: 1.75rem; color: var(--primary-color);">
                        <?php echo date('d.m.Y', strtotime($proposal['created_at'])); ?> Tarihli Teklif Formu
                    </h1>
                </div>

                <div
                    style="margin-bottom: 0.5rem; border-bottom: 2px solid var(--border-color); padding-bottom: 0.5rem;">
                    <h3 style="margin: 0; font-size: 1.25rem;">Sayın
                        <?php echo htmlspecialchars($proposal['customer']); ?>
                    </h3>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="background: transparent;">Ürün</th>
                                <th style="background: transparent;">Birim Fiyat</th>
                                <th style="background: transparent;">Adet</th>
                                <th style="background: transparent; text-align: right;">Toplam</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($proposal['items'] as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td><?php echo number_format($item['price'], 2) . ' ' . $item['currency']; ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td style="text-align: right;">
                                        <?php echo number_format($item['total'], 2) . ' ' . $item['currency']; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3"
                                    style="text-align: right; font-weight: 700; padding-top: 1rem; font-size: 0.9rem;">
                                    GENEL
                                    TOPLAM (Liste Fiyatı):</td>
                                <td
                                    style="text-align: right; font-weight: 700; padding-top: 1rem; color: var(--primary-color); font-size: 1.1rem;">
                                    <?php echo htmlspecialchars($proposal['grand_total_display']); ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <?php if ($rates_fetched): ?>
                    <div id="currency-info"
                        style="margin-top: 1rem; padding: 1rem; background-color: #f8fafc; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                        <div
                            style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
                            <div style="font-size: 0.875rem; color: var(--text-muted);">
                                <p><strong>Kur Bilgileri (<?php echo date('d.m.Y H:i'); ?>)</strong></p>
                                <p>USD: <?php echo number_format($usd_rate, 4); ?> TL</p>
                                <p>EUR: <?php echo number_format($eur_rate, 4); ?> TL</p>
                                <p style="font-size: 0.75rem; margin-top: 0.5rem;">Veriler finans.truncgil.com üzerinden
                                    alınmıştır.</p>
                            </div>
                            <div style="text-align: right;">
                                <p style="margin-bottom: 0.5rem; font-weight: 600;">Kur Çevrimi Yapılmış Toplamlar</p>
                                <p style="font-size: 1.1rem;"><strong><?php echo number_format($total_tl, 2); ?>
                                        TL</strong>
                                </p>
                                <p style="font-size: 1.1rem; color: #16a34a;">
                                    <strong><?php echo number_format($display_usd, 2); ?> USD</strong>
                                </p>
                                <p style="font-size: 1.1rem; color: #2563eb;">
                                    <strong><?php echo number_format($display_eur, 2); ?> EUR</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($bank_accounts)): ?>
                    <div id="bank-info"
                        style="margin-top: 1rem; padding: 1rem; background-color: #f8fafc; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; row-gap: 1rem;">
                            <?php
                            $counter = 0;
                            foreach ($bank_accounts as $acc):
                                $is_right = ($counter % 2 !== 0);
                                $align = $is_right ? 'right' : 'left';
                                $flex_align = $is_right ? 'flex-end' : 'flex-start';
                                $counter++;
                                ?>
                                <div
                                    style="display: flex; flex-direction: column; gap: 0.25rem; align-items: <?php echo $flex_align; ?>; text-align: <?php echo $align; ?>;">
                                    <div
                                        style="display: flex; align-items: center; gap: 0.5rem; justify-content: <?php echo $flex_align; ?>; flex-wrap: wrap;">
                                        <strong
                                            style="font-size: 0.8rem; color: var(--text-color);"><?php echo htmlspecialchars($acc['bank_name']); ?></strong>
                                        <span
                                            style="font-size: 0.7rem; color: var(--text-muted); padding: 1px 4px; border: 1px solid var(--border-color); border-radius: 4px;"><?php echo htmlspecialchars($acc['currency']); ?></span>
                                        <span
                                            style="font-size: 0.75rem; color: var(--text-muted); border-left: 1px solid var(--border-color); padding-left: 0.5rem;"><?php echo htmlspecialchars($acc['account_holder'] ?? ''); ?></span>
                                    </div>
                                    <span
                                        style="font-family: monospace; font-size: 0.8rem; color: var(--text-color); letter-spacing: 0.5px;">
                                        <?php echo wordwrap(htmlspecialchars($acc['iban']), 4, " ", true); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div> <!-- End padding div -->

            <!-- Footer removed -->
        </div>
    </div>
    <script>
        function downloadPDF() {
            // Buton durumunu güncelle
            const btn = document.querySelector('button[onclick="downloadPDF()"]');
            const originalText = btn.innerText;
            btn.innerText = 'Hazırlanıyor...';
            btn.disabled = true;

            // 1. Elementi Klonla
            const original = document.getElementById('proposal-content');
            const clone = original.cloneNode(true);

            // 2. Klonu izole bir kapsayıcıya al (Off-screen render)
            // A4 Genişliği (96 DPI) ~794px. 
            // Sabit genişlik vererek mobildeki dar ekran sorununu aşıyoruz.
            // Yükseklik: Tam A4 boyutu (~1123px)
            const wrapper = document.createElement('div');
            wrapper.style.position = 'fixed';
            wrapper.style.top = '0';
            wrapper.style.left = '0';
            wrapper.style.width = '794px';
            wrapper.style.minHeight = '1123px'; // Force full A4 height
            wrapper.style.zIndex = '-9999'; // Görünmez yap
            wrapper.style.backgroundColor = '#ffffff';
            wrapper.appendChild(clone);
            document.body.appendChild(wrapper);

            // 3. Klona PDF modunu (Masaüstü/Print stili) uygula
            clone.classList.add('pdf-mode');

            // 4. Ayarlar
            const opt = {
                margin: [0.5, 0.5], // Üst-Alt, Sol-Sağ (cm)
                filename: 'Teklif_<?php echo htmlspecialchars($proposal['customer']); ?>_<?php echo date('d.m.Y'); ?>.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: {
                    scale: 2, // Yüksek çözünürlük
                    useCORS: true,
                    windowWidth: 794, // Kapsayıcı ile aynı
                    width: 794,
                    scrollX: 0,
                    scrollY: 0,
                    x: 0,
                    y: 0
                },
                jsPDF: { unit: 'cm', format: 'a4', orientation: 'portrait' }
            };

            // 5. Oluştur ve Temizle
            html2pdf().set(opt).from(clone).save().then(() => {
                document.body.removeChild(wrapper); // Temizlik
                btn.innerText = originalText;
                btn.disabled = false;
            }).catch(err => {
                console.error(err);
                document.body.removeChild(wrapper);
                btn.innerText = originalText;
                btn.disabled = false;
            });
        }

        function downloadPNG() {
            // Buton durumunu güncelle
            const btn = document.querySelector('button[onclick="downloadPNG()"]');
            const originalText = btn.innerText;
            btn.innerText = 'Hazırlanıyor...';
            btn.disabled = true;

            // 1. Elementi Klonla (PDF mantığı ile aynı)
            const original = document.getElementById('proposal-content');
            const clone = original.cloneNode(true);

            // 2. Kapsayıcı oluştur (Sabit A4 boyutu)
            const wrapper = document.createElement('div');
            wrapper.style.position = 'fixed';
            wrapper.style.top = '0';
            wrapper.style.left = '0';
            wrapper.style.width = '794px';
            wrapper.style.minHeight = '1123px';
            wrapper.style.zIndex = '-9999';
            wrapper.style.backgroundColor = '#ffffff';
            wrapper.appendChild(clone);
            document.body.appendChild(wrapper);

            // 3. Masaüstü stilini uygula
            clone.classList.add('pdf-mode');

            // 4. HTML2Canvas ile yakala
            html2canvas(clone, {
                scale: 2, // 2x kalite
                useCORS: true,
                windowWidth: 794,
                width: 794,
                scrollX: 0,
                scrollY: 0,
                x: 0,
                y: 0,
                backgroundColor: '#ffffff'
            }).then(canvas => {
                // 5. İndir
                const link = document.createElement('a');
                link.download = 'Teklif_<?php echo htmlspecialchars($proposal['customer']); ?>_<?php echo date('d.m.Y'); ?>.png';
                link.href = canvas.toDataURL('image/png');
                link.click();

                // Temizlik
                document.body.removeChild(wrapper);
                btn.innerText = originalText;
                btn.disabled = false;
            });
        }

        function shareWhatsapp() {
            const baseUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            const id = "<?php echo $id; ?>";
            const token = "<?php echo $expected_token; ?>";
            const shareUrl = `${baseUrl}?id=${id}&token=${token}`;
            const text = "Merhaba, size özel teklifimizi inceleyebilirsiniz (<?php echo date('d.m.Y'); ?>): " + shareUrl;

            // WhatsApp URL Şeması
            const waUrl = "https://wa.me/?text=" + encodeURIComponent(text);
            window.open(waUrl, '_blank');
        }
    </script>
</body>

</html>
```