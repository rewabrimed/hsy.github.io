<?php
require_once 'auth.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $json_file = 'data/bank_accounts.json';
    if (file_exists($json_file)) {
        $accounts = json_decode(file_get_contents($json_file), true) ?: [];

        $accounts = array_filter($accounts, function ($acc) use ($id) {
            return $acc['id'] !== $id;
        });

        $accounts = array_values($accounts);

        file_put_contents($json_file, json_encode($accounts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}

header('Location: bank_accounts.php');
exit;
?>
