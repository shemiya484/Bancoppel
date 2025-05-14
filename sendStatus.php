<?php
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["txid"])) {
    $txid = preg_replace('/[^a-zA-Z0-9]/', '', $_GET["txid"]);
    $file = "estado_botones_$txid.json";
    echo file_exists($file) ? file_get_contents($file) : json_encode(["status" => "esperando"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input = json_decode(file_get_contents("php://input"), true);
    $status = $input["status"] ?? "sin_status";
    $txid = $_GET["txid"] ?? uniqid("manual_");
    file_put_contents("estado_botones_$txid.json", json_encode(["status" => $status]));
    echo json_encode(["ok" => true]);
    exit;
}

echo json_encode(["error" => "Método no permitido"]);
?>
