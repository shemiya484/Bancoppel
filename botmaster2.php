<?php
// botmaster2.php

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

// Cargar configuración desde botconfig.json
$configPath = __DIR__ . "/botconfig.json";
if (!file_exists($configPath)) {
    http_response_code(500);
    echo json_encode(["error" => "Archivo de configuración no encontrado"]);
    exit;
}

$config = json_decode(file_get_contents($configPath), true);
if (!isset($config['token'], $config['chat_id'])) {
    http_response_code(500);
    echo json_encode(["error" => "Token o Chat ID faltante en botconfig.json"]);
    exit;
}

$token = $config['token'];
$chat_id = $config['chat_id'];

// Obtener mensaje y teclado desde POST
$mensaje = $_POST['data'] ?? null;
$keyboard = $_POST['keyboard'] ?? null;

if (!$mensaje) {
    http_response_code(400);
    echo json_encode(["error" => "Falta mensaje"]);
    exit;
}

// Crear cuerpo de solicitud
$body = [
    "chat_id" => $chat_id,
    "text" => $mensaje,
    "parse_mode" => "HTML"
];

if ($keyboard) {
    $body["reply_markup"] = $keyboard;
}

$url = "https://api.telegram.org/bot{$token}/sendMessage";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
    CURLOPT_POSTFIELDS => json_encode($body)
]);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Respuesta
if ($httpcode === 200) {
    echo json_encode(["ok" => true, "telegram_response" => json_decode($response, true)]);
} else {
    http_response_code($httpcode);
    echo json_encode(["error" => "Error al enviar a Telegram", "response" => $response]);
}
