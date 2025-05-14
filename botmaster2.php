<?php
// botmaster2.php

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo "Método no permitido";
    exit;
}

$data = $_POST["data"] ?? "";
$keyboard = $_POST["keyboard"] ?? "";

$configPath = "botconfig.json";

if (!file_exists($configPath)) {
    http_response_code(500);
    echo "Archivo de configuración no encontrado";
    exit;
}

$config = json_decode(file_get_contents($configPath), true);
$token = $config["token"] ?? null;
$chat_id = $config["chat_id"] ?? null;

if (!$token || !$chat_id || !$data) {
    http_response_code(400);
    echo "Faltan datos necesarios (token, chat_id o data)";
    exit;
}

$mensaje = [
    "chat_id" => $chat_id,
    "text" => $data,
    "parse_mode" => "HTML"
];

if ($keyboard) {
    $mensaje["reply_markup"] = json_decode($keyboard, true);
}

$ch = curl_init("https://api.telegram.org/bot$token/sendMessage");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($mensaje));
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

$response = curl_exec($ch);
curl_close($ch);

echo $response;
