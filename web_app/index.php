<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Reto: Tablero Web Inteligente de Seguridad Ciudadana</title>

    <!-- PWA meta -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#0a3d62" />

    <!-- iOS standalone mode -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />

    <!-- ICONOS PARA PWA -->
    <link rel="apple-touch-icon" href="icons/icon-192.png" />
    <link rel="icon" sizes="192x192" href="icons/icon-192.png">

    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@turf/turf@6/turf.min.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Estilos -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/gov.css?ver=1">
    <link rel="stylesheet" href="assets/css/style.css?ver=1">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css">

</head>
<body>
    
<div class="bg-primario">
        <div class="container">
            <img src="assets/images/logo_gov.png" alt="Logo Gov.co" class="img-logo">
        </div>
        
    </div>
    
 <header class="bg-light">
        <div class="container">
        <div class="row px-1 py-4">
            <div class="col-12 col-md-6  col-sm-12 d-flex text-light pre-nav">
                <div class="logo-republica m-auto">
                    <img src="assets/images/logo_republica.png" alt="Logo Republica de Colombia" class="img-fluid2_ logopresi">
                </div>
            </div>
            <div class="col-12 col-md-6 col-sm-12">
                <div class="row">
                 <h1>Tablero Web Inteligente de Seguridad Ciudadana</h1>
                </div>
            </div>
        </div>
        </div>
        <nav class="navbar navbar-expand-lg navbar-light bg-light nav-desktop" style="display:none !important">
            <div class="container-fluid">
              <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarToggler2" aria-controls="navbarToggler1" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
              </button>
              <div class="collapse navbar-collapse text-center" id="navbarToggler2">
               </div>
            </div>
          </nav>

          
    </header>



<div id="loading" class="loading-overlay">
  <div class="spinner"></div>
</div>

<div class="block block--gov-accessibility -accesibilidad-menu">
    <div class="block-options navbar-expanded -accesibilidad-items">
        <a class="contrast-ref" href="javascript: void(0)">
            <span class="govco-icon govco-icon-contrast-n"></span>
            <label> Contraste </label>
        </a>
        <a class="min-fontsize" href="javascript: void(0)">
            <span class="govco-icon govco-icon-less-size-n"></span>
            <label class="align-middle"> Reducir letra </label>
        </a>
        <a class="max-fontsize" href="javascript: void(0)">
            <span class="govco-icon govco-icon-more-size-n"></span>
            <label class="align-middle"> Aumentar letra </label>
        </a>
        <a target="_blank" href="https://centroderelevo.gov.co/632/w3-channel.html">
            <span class="govco-icon govco-icon-relief-n"></span>
            <label class="align-middle"> Centro de relevo </label>
        </a>
    </div>
</div>

<div class="container">

    <div class="filters">
        <div class="panel">
            <label>Departamento</label>
            <select id="departamento" class="form-control">
                 <option value="">-- Seleccionar una opción --</option>
            </select>
        </div>
        
        <div class="panel zona" style="display:none">
            <label>Zonas</label>
            <select id="zona" class="form-control">
                <option value="">Todas</option>
                <option value="Comunera">Comunera</option>
                <option value="García Rovira">García Rovira</option>
                <option value="Guanentá">Guanentá</option>
                <option value="Metropolitana">Metropolitana</option>
                <option value="Soto Norte">Soto Norte</option>
                <option value="Vélez">Vélez</option>
                <option value="Yariguíes">Yariguíes</option>
            </select>
        </div>

        <div class="panel">
            <label>Municipio</label>
            <select id="municipio" class="form-control">
                 <option value="">-- Seleccionar una opción --</option>
            </select>
        </div>

        

        <div class="panel">
            <label class="full">Rango de fechas</label>
            <input type="date" id="desde" value="<?php echo date('Y')."-01-01"; ?>">
            <input type="date" id="hasta"  value="<?php echo date('Y-m-d'); ?>">
        </div>
    </div>
    
    <div id="geoLoader">
        <span class="msg">Procesando datos geolocalizados</span>
        <span class="dots"></span>
    </div>
    
        <div class="container stats-box">
              <div class="row">
                  <div class="stat-item col-12 col-md-3  col-sm-12">
                     <div class="title">
                     <strong>Delitos sexuales</strong>
                     <img src="icons/aggression.png"></img> 
                     </div>
                     <span id="delitos_sexuales">0</span>
                 </div>
            <div class="stat-item col-12 col-md-3  col-sm-12">
                <div class="title">
                <strong>Violencia Intrafamiliar</strong> 
                <img src="icons/no-aggression.png"></img>
                </div>
                <span id="violencia">0</span>
            </div>
            <div class="stat-item col-12 col-md-3  col-sm-12">
                <div class="title">
                <strong>Hurto</strong> 
                <img src="icons/thief-insurance.png"></img>
                <span id="hurto">0</span>
                </div>
            </div>
             <div class="stat-item  col-12 col-md-3  col-sm-12">
                 <div class="title">
                 <strong>Conflicto Armado</strong>
                 <img src="icons/rifle.png"></img>
                 </div>
                 <span id="conflicto">0</span>
                 </div> 
              </div>
       
    </div>

    <div id="map"></div>
    
    <div class="container no-pading">
        <div class="row">
            
            <div class="chat-container">
                    <div class="chat-messages" id="chatMessages">
                        <!-- Mensajes aquí -->
                        <p>Te ayudo analizar estos datos, y brindarte recomendaciones de seguridad?, Hablemos!</p>
                    </div>
                    <div class="chat-input">
                        <input type="text" id="msg" placeholder="Escribe un mensaje...">
                        <button id="sendChat" onclick="enviar()">Enviar</button>
                    </div>
                </div>
                
                <script>
                async function enviar() {
                    const input = document.getElementById("msg");
                    const msg = input.value.trim();
                    if(!msg) return;
                
                    // Mostrar mensaje del usuario
                    const chatMessages = document.getElementById("chatMessages");
                    const userMsgDiv = document.createElement("div");
                    userMsgDiv.className = "message user-msg";
                    userMsgDiv.textContent = msg;
                    chatMessages.appendChild(userMsgDiv);
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                    input.value = "";
                
                    // Mostrar efecto de "procesando"
                    const botMsgDiv = document.createElement("div");
                    botMsgDiv.className = "message bot-msg";
                    botMsgDiv.innerHTML = `
                        <span>Procesando</span>
                        <span class="processing"></span>
                        <span class="processing"></span>
                        <span class="processing"></span>
                    `;
                    chatMessages.appendChild(botMsgDiv);
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                
                    // Llamada al backend
                    try {
                        const res = await fetch("api/chat.php", {
                            method: "POST",
                            headers: {"Content-Type": "application/json"},
                            body: JSON.stringify({ mensaje: msg })
                        });
                        const data = await res.json();
                
                        // Eliminar efecto "procesando"
                        botMsgDiv.innerHTML = "";
                
                        const respuesta = data.output?.respuesta || "Sin respuesta";
                
                        // Escribir la respuesta poco a poco
                        let i = 0;
                        const interval = setInterval(() => {
                            botMsgDiv.textContent += respuesta[i];
                            i++;
                            chatMessages.scrollTop = chatMessages.scrollHeight;
                            if(i >= respuesta.length) clearInterval(interval);
                        }, 20); // 20ms por caracter
                    } catch(err) {
                        botMsgDiv.textContent = "Error al conectar con el servidor.";
                    }
                }
                </script>
            
        </div>
    </div>
    



</div>

  <footer>
      <div class="row">
        <div class="col-12 col-md-4 logo_govco border-footer p-2">

        </div>
        <div class="col-12 col-md-4 text-center border-footer p-2">
          <p class="fw-bold">¡De la ciudadanía para la ciudadanía!</p>
         
        </div>
        <div class="col-12 col-md-4 contacto p-2">
         
        </div>
      </div>
    </footer>

<script>
// Registrar service worker
if ("serviceWorker" in navigator) {
    navigator.serviceWorker.register("sw.js");
}
</script>
<script src="assets/js/app.js"></script>
<script src="assets/js/gov.js"></script>

</body>
</html>