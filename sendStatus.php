<?php
// sendStatus.php

$config = json_decode(file_get_contents(__DIR__ . "/botconfig.json"), true);
$token = $config["token"] ?? "";
$chatId = $config["chat_id"] ?? "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input = json_decode(file_get_contents("php://input"), true);
    $status = $input["status"] ?? "Sin estado";

    $msg = "✅ Acceso aprobado tras acción del botón: " . $status;

    $url = "https://api.telegram.org/bot$token/sendMessage";
    $post = http_build_query([
        "chat_id" => $chatId,
        "text" => $msg
    ]);

    $context = stream_context_create([
        "http" => [
            "method" => "POST",
            "header" => "Content-type: application/x-www-form-urlencoded",
            "content" => $post
        ]
    ]);

    $response = file_get_contents($url, false, $context);
    echo json_encode(["ok" => true, "response" => $response]);
} else {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
}
