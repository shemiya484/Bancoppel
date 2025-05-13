<?php
// botmaster2.php

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = "";
    $chatId = "";

    // Cargar configuración desde botconfig.json
    $config = json_decode(file_get_contents(__DIR__ . "/botconfig.json"), true);
    if (!$config || !isset($config["token"]) || !isset($config["chat_id"])) {
        http_response_code(500);
        echo json_encode(["error" => "botconfig.json inválido"]);
        exit;
    }

    $token = $config["token"];
    $chatId = $config["chat_id"];

    $data = $_POST["data"] ?? "";
    $keyboard = $_POST["keyboard"] ?? "";

    if (!$data) {
        echo json_encode(["error" => "Falta mensaje"]);
        exit;
    }

    $url = "https://api.telegram.org/bot$token/sendMessage";

    $postFields = [
        "chat_id" => $chatId,
        "text" => $data,
        "parse_mode" => "HTML"
    ];

    if (!empty($keyboard)) {
        $postFields["reply_markup"] = $keyboard;
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postFields
    ]);
    $result = curl_exec($ch);
    curl_close($ch);

    echo $result;
} else {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
}
