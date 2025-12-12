<?php
$rates_url = "https://finans.truncgil.com/today.json";

$arrContextOptions = array(
    "ssl" => array(
        "verify_peer" => false,
        "verify_peer_name" => false,
    ),
    "http" => array(
        "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36"
    )
);

$json = file_get_contents($rates_url, false, stream_context_create($arrContextOptions));

if ($json === false) {
    echo "Error fetching URL";
    exit;
}

$data = json_decode($json, true);

echo "Data type: " . gettype($data) . "\n";
if ($data) {
    if (isset($data['USD'])) {
        echo "USD data found:\n";
        print_r($data['USD']);
    } else {
        echo "USD key missing.\n";
        print_r($data);
    }
} else {
    echo "JSON decode failed or empty.\n";
    echo "Raw JSON: " . substr($json, 0, 500) . "...\n";
}
?>
