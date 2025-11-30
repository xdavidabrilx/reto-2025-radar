# Radar 360
<p>Proyecto opensource que implemnta IA para analizar datos abiertos del portal datos.gov.co</p>
<div style="text-align:center">
  <p align="center">
   <img src="https://3-33.co/360_0.jpg" #vitrinedev/>
  </p>
</div>



# Introducción
<p>Radar 360 es una solución tecnológica integral diseñada para fortalecer la seguridad ciudadana mediante el uso inteligente de datos. Surge como una iniciativa enfocada inicialmente en el departamento de Santander, pero con la visión de escalar hacia un modelo replicable a nivel nacional, adaptándose a las necesidades de cada territorio.</p>

<p>El propósito central de Radar 360 es convertir datos dispersos en información accionable que permita anticipar riesgos, orientar la toma de decisiones y generar respuestas tempranas frente a situaciones que afectan la convivencia, la movilidad y la seguridad de los ciudadanos.</p>

<p>La plataforma integra fuentes como incidentes reportados, patrones de movilidad, zonas críticas, horarios de riesgo, estadísticas históricas y variables del entorno, para crear un ecosistema de monitoreo inteligente. Mediante analítica avanzada, modelos predictivos y visualizaciones amigables, Radar 360 se convierte en un “radar tecnológico” capaz de observar el entorno en 360°, ofreciendo una visión completa, precisa y oportuna.</p>

<p>El proyecto busca no solo entregar información, sino empoderar a ciudadanos, entidades locales y autoridades con herramientas prácticas que mejoran la prevención, la planeación y la toma de decisiones. Su enfoque es inclusivo, escalable y adaptable, lo que permite que Radar 360 evolucione hacia una plataforma de seguridad inteligente de alcance global. 

<p>Este proyecto fue realizado en el marco del concurso de ¡Datos al Ecosistema 2025!</p>

## :green_heart: Problematica a resolver: Journey del ciudadano
<p>Un ciudadano Colombiano, principalmente del municipio de Santander requiere enterarse sobre el estado actual de la seguridad en su región. Esto con el fín de tomar desiciones importantes, por ejemplo, para mudarse (por estudio, trabajo, etc), cuadrar sus rutas de movilidad (Donde es más seguro, que horas son más seguras), en caso de una situación de inseguridad a donde dirigirse.</p>

<p>Teniendo en cuenta esta necesidad y la oportunidad que brindan los datos abiertos publicados en datos.gov.co para solucionar esta necesidad nace Radar 360. Un tablero web que pre-procesa, organiza y presenta los datos de una forma clara y amigable para cualquier ciudadano; así como poner a disposición un agente potencializado con IA que por medio d eun chat crea una conversación sencilla y clara con los ciudadanos.</p>

<p>De esta manera el ciudadano por medio de su navegaor o App previamente instalada en su dispositivo movíl, el accede a Radar 360 para que sea geolocalizado e inicie presentando cifras relacioandas a hurtos, violencia intrafamiliar, delitos sexuales y conflicto armado. En caso de tener preguntas puntuales, cuenta con un asistente IA para hacer preguntas en lenguaje natural y obtener orientación por parte del agente.</p>

## :hammer:Funcionalidades del proyecto
- `Detección de GPS para geolocalizar al ciudadano en el mapa`: descripción de la funcionalidad 1
- `Controles de accesibilidad de GOV.CO`: contraste, disminución letra, aumento letra, meta-tags para lectores de pantalla
- `Filtros por departamento y municipio`: Dada la selección actualiza el mapa con delitos de hurto, violencia intrafamiliar, indole sexual y conflicto armado
  -`En el caso del Departamento de Santander agrupa por provincias (sectores)`
- `Asistente virtual potencializado con Open AI`: Apartir del conjunto de datos abiertos se crearon vectores para responder a un flujo de chat con la IA y resumir datos de interés al ciudadano
-  `Instalación en el dispositivo mobile`: Capacidades de Progresive web application, para que el ciudadano instale la aplicación (IOS y Android) en su dispositivo. Teniendo así el asistente siempre disponible.
-   `Jobs de sincronización`: Tareas programadas que a traves de API sincroniz alos datos del portal datos.gov.co y 1 set de datos del portal colombiaenmapas.gov.co. Al obtener los datos extrae y agrupa estos datos para el geovisor.

## :zap: Dominio

<div style="text-align:center">
  <p align="center">
   <img src="https://3-33.co/360dominio.png" style="max-height:400px" #vitrinedev/>
  </p>
</div>

<p>Descripción de los componentes:</p>
<ul>
  <li><b>Front:</b> Contenido HTML con Bootstrap y Juqery para ofrecer una GUI clara el cual se adapta al tamaño de la pantalla del dispositivo. El ciudadano, puede usar controles de accesibilidad. Cuenta con 1 worker para habilitar la capacidad de Progresive Web Application para que pueda ser instalada en un dispositivo movíl.</li>
   <li><b>API: </b>La capa que permite obtener los datos de la base de datos, organizarlos y retornarlos a la capa front para ser usada en el geo visor. En el caso del chat con el agente, captura los datos enviados por el front, evalua que Vector debe ser utilizado para la cosnulta y envía petición al agente de Open AI.</li>
  <li><b>Jobs:</b> Tareas programas en el cron tab del servidor, se encarga de consumir las APIs de Datos abiertos, así como del portal de Colombia en Mapas. Una vez los obtiene, los procesa y alacenada en la base de datos relacional en MySQL</li>
</ul>

<p>Integraciones</p>
<ul>
  <li>
    <p>Del portal datos.gov.co su tomaron las siguientes fuentes:</p>
       <ul>
          <li><a href="https://www.datos.gov.co/dataset/Oficinas-de-Atenci-n-al-Ciudadano-Polic-a-Nacional/mhdb-2eis/data_preview">Oficinas de atención al ciudadado - Policia Nacional</a></li>
      <li><a href="https://www.datos.gov.co/Seguridad-y-Defensa/40Delitos-ocurridos-en-el-Municipio-de-Bucaramanga/75fz-q98y/about_data">Delitos ocurridos en el Municipio de Bucaramanga</a></li>
         <li><a href="https://www.datos.gov.co/Seguridad-y-Defensa/150-Informaci-n-delictiva-del-municipio-de-Bucaram/x46e-abhz/about_data">Información delictiva del municipio de Bucaramanga</a></li>
          <li><a href="https://www.datos.gov.co/Seguridad-y-Defensa/Reporte-Delitos-sexuales-Polic-a-Nacional/fpe5-yrmw/about_data">Reporte Delitos sexuales Policía Nacional</a></li>
         <li><a href="https://www.datos.gov.co/Seguridad-y-Defensa/Reporte-Delito-Violencia-Intrafamiliar-Polic-a-Nac/vuyt-mqpw/data_preview">Reporte Delitos Violencia Intrafamiliar Policía Nacional</a></li>
         <li><a href="https://www.datos.gov.co/Seguridad-y-Defensa/Reporte-Hurto-por-Modalidades-Polic-a-Nacional/d4fr-sbn2/about_data">Reporte Delito de Hurtos Policía Nacional</a></li>
         
       </ul>    
  </li>
  <li>
    <p>Del portal colombiaenmapas.gov.co tomó la siguiente fuente:</p>
    <ul>
      <li><a href="https://serviciosgiscnmh.centrodememoriahistorica.gov.co/agccnmh/rest/services/OMC/Acciones_Belicas/MapServer">Colombia en mapas: Acciones bélicas</a></li>
    </ul>
  </li>
  <li>Uso del Geovisor opensource de Open street maps por medio de leaftlet.</li>
  <li>Integración con API Rest de Open AI para consumir vectores previamente entrenados con la información de los datos abiertos</li>
</ul>

# Demo
<a href="https://3-33.co/tablero-web"> Demo Radar 360</a>

# Autores
<ul>
  <li>Maria Medina - Contadora profesional / Analista de datos</li>
  <li>Yesid Abril - Docente</li>
  <li>David Abril - Ingeniero de sistemas</li>
