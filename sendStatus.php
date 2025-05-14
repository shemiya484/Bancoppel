<?php
// sendStatus.php
$txId = $_GET['txid'] ?? '';
if (!$txId) {
    echo json_encode(["status" => "noid"]);
    exit;
}

$file = "estado_botones_$txId.json";
if (!file_exists($file)) {
    echo json_encode(["status" => "esperando"]);
    exit;
}

$data = json_decode(file_get_contents($file), true);
echo json_encode(["status" => $data["accion"] ?? "desconocido"]);
?>
