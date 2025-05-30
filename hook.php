<?php
// Guardar todo el contenido recibido para depuración
file_put_contents("log_hook.txt", file_get_contents("php://input"));

// Decodificar el JSON recibido
$update = json_decode(file_get_contents("php://input"), true);
if (!$update) exit;

// Leer token desde config
$config = json_decode(file_get_contents("botconfig.json"), true);
$token = $config["token"] ?? "";
if (!$token) exit;

$api = "https://api.telegram.org/bot$token";

// Si es un callback_query (botón)
if (isset($update["callback_query"])) {
    $callback = $update["callback_query"];
    $data = $callback["data"] ?? "";
    $callback_id = $callback["id"];
    $chat_id = $callback["message"]["chat"]["id"];

    // Confirmar el botón (eliminar loading)
    file_get_contents("$api/answerCallbackQuery?callback_query_id=$callback_id");

    // Separar la acción del ID
    $parts = explode(":", $data);
    if (count($parts) !== 2) exit;

    $accion = $parts[0];
    $txid = $parts[1];

    // Guardar estado en archivo local
    file_put_contents("estado_botones_$txid.json", json_encode(["status" => $accion]));

    // Enviar mensaje al usuario
    $mensaje = "✅ Acción ejecutada: $accion (ID: $txid)";
    file_get_contents("$api/sendMessage?chat_id=$chat_id&text=" . urlencode($mensaje));

    exit;
}

// Si es un mensaje normal
if (isset($update["message"])) {
    $chat_id = $update["message"]["chat"]["id"];
    $text = $update["message"]["text"] ?? "";

    // Enviar mensaje de confirmación
    $respuesta = "👋 Hola, escribiste: \"$text\"";
    file_get_contents("$api/sendMessage?chat_id=$chat_id&text=" . urlencode($respuesta));

    exit;
}
?>
