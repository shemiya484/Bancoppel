<?php
$botToken = "7100847504:AAEx_w_mugzLVQp8HgfPxBmlhIBXzD11H_k";
$chatId = "-4729682252";

if (!isset($_POST['data'])) {
    file_put_contents("debug.log", "Falta 'data'\n", FILE_APPEND);
    exit("No se recibió 'data'");
}

$message = $_POST['data'];
$keyboard = isset($_POST['keyboard']) ? json_decode($_POST['keyboard'], true) : null;

// Armar payload base
$postFields = [
    'chat_id' => $chatId,
    'text' => $message,
    'parse_mode' => 'HTML'
];

// Si se proporcionó un teclado, lo agregamos como reply_markup
if ($keyboard) {
    $postFields['reply_markup'] = json_encode($keyboard);
}

// Enviar a Telegram con cURL
$ch = curl_init("https://api.telegram.org/bot$botToken/sendMessage");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

// Registrar respuesta para debug
file_put_contents("debug.log", "Mensaje:\n$message\nRespuesta Telegram:\n$response\n\n", FILE_APPEND);

// Devolver respuesta
echo "Mensaje enviado.";
?>
