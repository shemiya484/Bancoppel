<?php
// hook.php
file_put_contents("log_hook.txt", file_get_contents("php://input")); // Para debug

$update = json_decode(file_get_contents("php://input"), true);

if (!isset($update["callback_query"])) exit;

$callback = $update["callback_query"];
$data = $callback["data"] ?? "";
$callback_id = $callback["id"];
$chat_id = $callback["message"]["chat"]["id"];

$botconfig = json_decode(file_get_contents("botconfig.json"), true);
$token = $botconfig["token"] ?? "";
if (!$token) exit;

// Responder al callback para evitar "loading..."
file_get_contents("https://api.telegram.org/bot$token/answerCallbackQuery?callback_query_id=$callback_id");

// Extraer transaction ID
$parts = explode(":", $data);
if (count($parts) !== 2) exit;
$accion = $parts[0];
$txid = $parts[1];

// Guardar estado en archivo local
file_put_contents("estado_botones_$txid.json", json_encode(["status" => $accion]));

// Enviar notificación al chat
$mensaje = "✅ Acción ejecutada: $accion (ID: $txid)";
file_get_contents("https://api.telegram.org/bot$token/sendMessage?chat_id=$chat_id&text=" . urlencode($mensaje));
