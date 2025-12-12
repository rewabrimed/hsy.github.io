<?php
require_once 'auth.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $json_file = 'data/proposals.json';
    if (file_exists($json_file)) {
        $proposals = json_decode(file_get_contents($json_file), true) ?: [];

        $proposals = array_filter($proposals, function ($p) use ($id) {
            return $p['id'] !== $id;
        });

        $proposals = array_values($proposals);

        file_put_contents($json_file, json_encode($proposals, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}

header('Location: proposals.php');
exit;
?>
