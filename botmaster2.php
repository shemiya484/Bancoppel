<?php
$botToken = "TU_BOT_TOKEN";
$chatId = "TU_CHAT_ID";

if (!isset($_POST['data'])) {
    file_put_contents("debug.log", "Falta 'data'\n", FILE_APPEND);
    exit("No se recibió 'data'");
}

$message = $_POST['data'];

// URL para enviar a Telegram
$url = "https://api.telegram.org/bot$botToken/sendMessage";

// Enviar
$response = file_get_contents($url . "?chat_id=$chatId&text=" . urlencode($message));

// Guardar respuesta para debug
file_put_contents("debug.log", "\nRespuesta Telegram: $response\n", FILE_APPEND);
?>
