<?php
$botToken = "7844799050:AAEr7wChEkAp31ktChjaTlguv1aUykSbaxw";
$chatId = "-1002658316321";

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
