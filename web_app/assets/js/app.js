function preguntarUbicacion() {
    Swal.fire({
        title: '¿Desea filtrar por su ubicación actual?, para ello debe tener habilitado su GPS en el smartphone',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí',
        cancelButtonText: 'No',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // El usuario dio click en Sí
            showLoading();
            filtrarPorUbicacion();
        } else {
            showLoading();
            cargarDepartamentosSelect();
        }
    });
}

// Ejemplo de función que se llama si acepta
function filtrarPorUbicacion() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            onUbicacion(position);
        }, function(error) {

        Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "No se pudo obtener la ubicación, no hay lio igual puedes usar la herramienta",
        });
            
            console.error("No se pudo obtener la ubicación:", error.message);
        });
    } else {
         Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "Geolocalización no soportada en este equipo, no hay lio igual puedes usar la herramienta",
        });
    }
}

window.onload = function() {
    preguntarUbicacion();
};

$(document).ready(function () {
  

    $("#departamento").on("change", function () {
        showLoading();
        cargarMunicipiosSelect($(this).val());
        if( $("#departamento").val()=="SANTANDER"){
            $(".zona").css("display","block");
        }else{
           $(".zona").css("display","none");
        }
    });
    
    
    $("#zona").on("change", function () {
        /*showLoading();
        cargaMunicipioxZona();*/
         if($("#zona").val()==""){
            cargarMunicipiosSelect($("#departamento").val());
        }
    });
    
    
    $("#municipio").on("change", function () {
       /* showLoading();
        cargaxMunicipio();*/
    });
    
    

    // Llamar estadísticas cuando todo cambia
    $(".form-control, #desde, #hasta").on("change", function(){
        showLoading();
        if($("#departamento").val()!="" && $("#municipio").val()==""){
            if($("#zona").val()==""){
             cargaxDepartamento();
            }else{
               cargaMunicipioxZona(); 
            }
        }
        
        if($("#departamento").val()!="" && $("#municipio").val()!=""){
            cargaxMunicipio();
        }
        
        if($("#departamento").val()=="" && $("#municipio").val()==""){
            cargaGeneral();
        }
        //cargarEstadisticas();
    });
    
    
     $("#msg").on("keydown", function(event) {
            if (event.keyCode === 13) {
              // Prevent the default action (e.g., form submission)
              event.preventDefault(); 
              // Trigger the click event on the button
              if($("#msg").val()!=""){
                $("#sendChat").trigger("click"); 
              }
            }
          });

});


function showLoading(){
    $("#loading").fadeIn(200);
}

function hideLoading(){
    $("#loading").fadeOut(200);
}

function mostrarGeoLoader() {
    $("#geoLoader").fadeIn(200);
}

function ocultarGeoLoader() {
    $("#geoLoader").fadeOut(200);
}

/* ---- SERVICIOS AJAX ---- */

function cargarDepartamentosSelect() {
    $.getJSON("api/departamentos.php?v=2", function (data) {
        $("#departamento").html('<option value="">-- Seleccionar una opción --</option>');
        $.each(data, function(_, item){
            $("#departamento").append(`<option value="${item}">${item}</option>`);
        });
    });
}

function cargarMunicipiosSelect(dep, clearLayer) {
    $.getJSON("api/municipios.php?v=2&departamento=" + dep, function (data) {
        $("#municipio").html('<option value="">-- Seleccionar una opción --</option>');
        
        $.each(data, function(_, item){
        addToOption = true;    
        if($("#zona").val()!="" && dep=="SANTANDER"){
            addToOption = false; 
            municipiosZona = zonas[$("#zona").val()];
            if(existsInZona(municipiosZona,item)){
                addToOption = true; 
            }
        }
            if(addToOption){
                $("#municipio").append(`<option value="${item}">${item}</option>`);
            }
        });
        
        if(typeof clearLayer=="undefined" || clearLayer){
            capaMunicipios.clearLayers();
            capaHurto.clearLayers();
            capaViolencia.clearLayers();
            capaSexual.clearLayers();
            pintarLimitesMunicipios(municipios, dep);
            //cargaxDepartamento();
            hideLoading();
        }
    });
}



function cargarEstadisticas() {
    const params = {
        departamento: $("#departamento").val(),
        municipio: $("#municipio").val(),
        cuadrante: $("#cuadrante").val(),
        desde: $("#desde").val(),
        hasta: $("#hasta").val()
    };

    $.getJSON("api/estadisticas.php?v=2", params, function(data){
        $("#delitos_sexuales").text(data.delitos_sexuales);
        $("#violencia").text(data.violencia);
        $("#hurto").text(data.hurto);

        if(data.lat && data.lng){
            map.setView([data.lat, data.lng], 14);
        }
        hideLoading();
    });
}

function procesarCentroides(geojson) {
    deptos = [];
    geojson.features.forEach(function(f){

        // Obtener centroide
        const centro = turf.centroid(f);

        const lat = centro.geometry.coordinates[1];
        const lng = centro.geometry.coordinates[0];

        var nombreDepto = f.properties.dpto_cnmbr || f.properties.NOMBRE || f.properties.name;
        
         if(nombreDepto=="LA GUAJIRA"){
                nombreDepto = "GUAJIRA";
         }
         
         if(nombreDepto=="QUINDIO"){
                nombreDepto = "QUINDÍO";
         }
         
         if(nombreDepto=="ARCHIPIÉLAGO DE SAN ANDRÉS, PROVIDENCIA Y SANTA CATALINA"){
                nombreDepto = "SAN ANDRÉS";
         }
         
         if(nombreDepto=="VALLE DEL CAUCA"){
                nombreDepto = "VALLE";
         }
         
        deptos.push({
           "departamento": nombreDepto,
           "lat": lat,
            "lng": lng
        })

    });
  return deptos;
}

async function cargarDepartamentos() {
    const response = await fetch(
        'https://gist.githubusercontent.com/nestorandrespe/2220a8f9d4095a2983f87504603d6ef1/raw/9bf7b991e80e21156cd04349df061cd2e44ef099/departamentos.json'
    );

    if (!response.ok) {
        throw new Error("Error cargando JSON de departamentos");
    }

    return await response.json();
}

async function cargarMunicipios() {
    const response = await fetch(
        'https://gist.githubusercontent.com/nestorandrespe/f2533e9bf810ee42ca9fa617ce138530/raw/b6bb3903e78c1a831ca442f94741fb4feef52921/municipios.json'
    );

    if (!response.ok) {
        throw new Error("Error cargando JSON de departamentos");
    }

    return await response.json();
}

async function cargarZonas() {
    const response = await fetch(
        'assets/json/zonas.json'
    );

    if (!response.ok) {
        throw new Error("Error cargando JSON de departamentos");
    }

    return await response.json();
}




function pintarLimitesMunicipios(municipios, filtro, filtroMunicipio){
    //console.log("depto"+filtro);
    municipios.features.map(f=>{
    //console.log(f.properties);

    if((typeof filtro =="undefined" || filtro=="") || (filtro==f.properties.dpto_cnmbr || (f.properties.dpto_cnmbr=="BOGOTÁ" && filtro=="CUNDINAMARCA"))){    
        var addToMap = true;
        
        if(typeof filtroMunicipio !="undefined"){
            if(filtroMunicipio != f.properties.mpio_cnmbr){
               addToMap = false; 
            }
        }
        
        //para delimitar zona en el depto de Santander
        if($("#zona").val()!="" && filtro=="SANTANDER"){
            municipiosZona = zonas[$("#zona").val()];
            if(existsInZona(municipiosZona,f.properties.mpio_cnmbr)){
                addToMap = true; 
            }
        }
        
        if(addToMap){
        L.geoJson(f.geometry, {
          weight: 0.2,
          color: '#000',//'#d98e01',
          fillColor: '#d98e01',
          fillOpacity: 0.5,
          opacity: 1
        }).addTo(capaMunicipios);
        }
    }
    });
    if(typeof filtro !="undefined"){
        try{
        map.fitBounds(capaMunicipios.getBounds());
        }catch(error) {
          console.error("An error occurred:", error.message);
        }
    }
}

function existsInZona(zonasArr, municipioTxt) {
    zonasArr = zonasArr.map(m => m.toUpperCase());
    return zonasArr.includes(municipioTxt.toUpperCase());
}

/* ---------------------------
        MAPA LEAFLET
----------------------------*/
var userMarker;
var map = L.map('map').setView([4.5709, -74.2973], 6); // centro de Colombia, zoom general

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18
}).addTo(map);



departamentos = [];
municipios = [];
//Para SANTANDER
zonas = [];

cargarDepartamentos().then(data => {
    departamentos = data;
    
    departamentos.features.forEach((element) => {
       if(element.departamento=="LA GUAJIRA"){
                element.departamento = "GUAJIRA";
         }
         
         if(element.departamento=="QUINDIO"){
                element.departamento = "QUINDÍO";
         }
         
         if(element.departamento=="ARCHIPIÉLAGO DE SAN ANDRÉS, PROVIDENCIA Y SANTA CATALINA"){
                element.departamento = "SAN ANDRÉS";
         }
         
         if(element.departamento=="VALLE DEL CAUCA"){
             element.departamento = "VALLE";
         }
    });
    
 
         
    
    //console.log("Departamentos cargados:", departamentos);
    
    departamentos = procesarCentroides(departamentos);
    cargaGeneral();
});



cargarMunicipios().then(data => {
    municipios = data;
    //console.log("municipios cargados:", municipios);
    pintarLimitesMunicipios(municipios);
    
  });

cargarZonas().then(data => {
    zonas = data;
});



  
  let departamentosBind = (feature, layer) => {
      layer.bindPopup(feature.properties.NOMBRE_DPT)
      layer.getPopup().on('remove', () => {
        layer.setStyle({fillOpacity: 0})
      })
  }
  
  
  // departamentos.features.map(f=>{
  //   L.geoJson(f, {
  //     weight: 0.7,
  //     color: '#999',
  //     fillColor: '#fff',
  //     fillOpacity: 0,
  //     onEachFeature: departamentosBind
  //   }).on('mouseover', (e) => {
  //     e.layer.setStyle({fillOpacity: 0.4})
  //   }).on('mouseout', (e) => {
  //     e.layer.setStyle({fillOpacity: 0})
  //     // map.closePopup()
  //   }).on('click', (e) => {
  //     e.layer.setStyle({fillOpacity: 1})
  //     // map.closePopup()
  //   }).addTo(map);
  // }

var capaHurto = L.layerGroup().addTo(map);
var capaViolencia = L.layerGroup().addTo(map);
var capaSexual = L.layerGroup().addTo(map);
var capaConflicto = L.layerGroup().addTo(map);
var capaMunicipios = L.featureGroup().addTo(map);

var overlayMaps = {
    "Hurto": capaHurto,
    "Violencia Intrafamiliar": capaViolencia,
    "Delitos Sexuales": capaSexual,
    "Conflicto Armado": capaConflicto
};

L.control.layers(null, overlayMaps, { collapsed: false }).addTo(map);


function onUbicacion(pos) {
    let lat = pos.coords.latitude;
    let lng = pos.coords.longitude;

    // Si ya había marcador lo eliminamos
    if (userMarker) {
        map.removeLayer(userMarker);
    }

    userMarker = L.marker([lat, lng]).addTo(map);
    map.setView([lat, lng], 12);

    // Llamar reverse geocoding para saber el DEPARTAMENTO
    obtenerDepartamento(lat, lng);
}

function distancia(lat1, lon1, lat2, lon2) {
    const R = 6371; // radio de la tierra en km
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(lat1 * Math.PI / 180) *
        Math.cos(lat2 * Math.PI / 180) *
        Math.sin(dLon / 2) * Math.sin(dLon / 2);

    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function obtenerDepartamento(lat, lng) {
    let url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=10&addressdetails=1`;

    fetch(url, { headers: { "User-Agent": "MiApp/1.0" }})
        .then(res => res.json())
        .then(data => {

            if (!data.address) return;

            let depto = data.address.state || "";
            console.log("DEPARTAMENTO DETECTADO:", depto);
            if(depto==""){
               seleccionarDepartamento(lat,lng);
            }
        });
}

function distancia(lat1, lon1, lat2, lon2) {
    const R = 6371; // radio de la tierra en km
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(lat1 * Math.PI / 180) *
        Math.cos(lat2 * Math.PI / 180) *
        Math.sin(dLon / 2) * Math.sin(dLon / 2);

    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function seleccionarDepartamento(lat,lng) {
    
    
    let mejorDepto = null;
    let mejorDistancia = Infinity;

    departamentos.forEach(dep => {
        let d = distancia(lat, lng, dep.lat, dep.lng);
        if (d < mejorDistancia) {
            mejorDistancia = d;
            mejorDepto = dep.departamento;
        }
    });
    if(mejorDepto!=null && 2<1){
        let sel = document.getElementById("departamento");
        for (let opt of sel.options) {
            if (opt.value.toLowerCase() === mejorDepto.toLowerCase()) {
                sel.value = opt.value;
                console.log("Departamento seleccionado:", sel.value);
                sel.dispatchEvent(new Event("change")); // para disparar tu filtro
                break;
            }
        }
    }
}

function onError(err) {
    console.warn("Error obteniendo ubicación:", err.message);
}


function refrescarMapa(data,type){
     countDelitos_sexuales = 0;
     countViolencia = 0;
     countHurto = 0;
     countconflicto = 0;
     data.forEach(function(item){
            
            var offsetHurto = L.point(-45, 0);
            var offsetViolencia = L.point(0, 0);
            var offsetSexual = L.point(45, 0);
            var offsetArmado= L.point(0, 45);
            
           switch(type){
            case "departamento":
                var tmpItem = departamentos.find(depto => depto.departamento === item.departamento);
                
                if(tmpItem==null){
                    console.log(item.departamento);
                }else{
                item.lat = tmpItem.lat;
                item.lng = tmpItem.lng;
                }
            break;
             case "municipio":
                 if(item.lat==null || item.lat==0){
                    var tmpItem =  buscarMunicipio(item.municipio, municipios);
                    if(tmpItem!=null){
                         item.lat = tmpItem.lat;
                         item.lng = tmpItem.lng;
                    }
                 }
            break;
           }
            
    
            // HURTO
            if(item.hurto > 0){
                if(item.lat!=null && item.lng!=null){
                L.marker([item.lat, item.lng], {
                    icon: L.icon({
                        iconUrl: "icons/thief-insurance.png",
                        iconSize: [32, 32],
                        iconAnchor: offsetHurto,
                        popupAnchor: [0, -15]
                    })
                })
                .bindPopup("<b>"+(typeof item.departamento!="undefined"? item.departamento : item.municipio)+"</b><br>Hurto: "+item.hurto)
                .addTo(capaHurto);
                }
            }
            

            countHurto = countHurto+parseInt(item.hurto);
    
            // VIOLENCIA INTRAFAMILIAR
            if(item.violencia > 0){
                if(item.lat!=null && item.lng!=null){
                L.marker([item.lat, item.lng], {
                    icon: L.icon({
                        iconUrl: "icons/no-aggression.png",
                        iconSize: [32, 32],
                        iconAnchor: offsetViolencia,
                        popupAnchor: [0, -15]
                    })
                })
                .bindPopup("<b>"+(typeof item.departamento!="undefined"? item.departamento : item.municipio)+"</b><br>Violencia: "+item.violencia)
                .addTo(capaViolencia);
                 }
            }
            countViolencia = countViolencia+parseInt(item.violencia);
    
            // DELITOS SEXUALES
            if(item.delitos_sexuales > 0){
                if(item.lat!=null && item.lng!=null){
                    L.marker([item.lat, item.lng], {
                        icon: L.icon({
                            iconUrl: "icons/aggression.png",
                            iconSize: [32, 32],
                            iconAnchor: offsetSexual,
                            popupAnchor: [0, -15]
                        })
                    })
                    .bindPopup("<b>"+(typeof item.departamento!="undefined"? item.departamento : item.municipio)+"</b><br>Delitos Sexuales: "+item.delitos_sexuales)
                    .addTo(capaSexual);
                }
            }
            countDelitos_sexuales = countDelitos_sexuales+parseInt(item.delitos_sexuales);

        
        //Conflicto 
        if(item.conflicto_armado > 0){
                if(item.lat!=null && item.lng!=null){
                    L.marker([item.lat, item.lng], {
                        icon: L.icon({
                            iconUrl: "icons/rifle.png",
                            iconSize: [32, 32],
                            iconAnchor: offsetArmado,
                            popupAnchor: [0, -15]
                        })
                    })
                    .bindPopup("<b>"+(typeof item.departamento!="undefined"? item.departamento : item.municipio)+"</b><br>Conflicto armado: "+item.delitos_sexuales)
                    .addTo(capaConflicto);
                }
            }
            countconflicto = countconflicto+parseInt(item.conflicto_armado);
    
        });
        
        $("#delitos_sexuales").text(countDelitos_sexuales);
        $("#violencia").text(countViolencia);
        $("#hurto").text(countHurto);
        $("#conflicto").text(countconflicto);
}

function buscarMunicipio(nombre, geojson) {
    nombre = nombre.trim().toUpperCase();

    for (let f of geojson.features) {
        if (f.properties.mpio_cnmbr.toUpperCase() === nombre) {

            // Obtener centro del polígono
            let bounds = L.geoJSON(f.geometry).getBounds();
            let center = bounds.getCenter();

            return {
                nombre: f.properties.mpio_cnmbr,
                lat: center.lat,
                lng: center.lng,
                properties: f.properties,
                geometry: f.geometry
            };
        }
    }

    return null; // si no se encontró
}

function cargaGeneral(){
    capaHurto.clearLayers();
    capaViolencia.clearLayers();
    capaSexual.clearLayers();
    
    if(typeof capaMunicipios!="undefined" && capaMunicipios!=null){
         capaMunicipios.clearLayers();
    }
     mostrarGeoLoader(); 
    $.getJSON("api/general.php?desde="+$("#desde").val()+"&hasta="+$("#hasta").val(), function(data) {
        hideLoading();
        refrescarMapa(data,"departamento")
       ocultarGeoLoader();
    });
}


function cargaxDepartamento(){
    if($("#departamento").val()!=""){
    capaHurto.clearLayers();
    capaViolencia.clearLayers();
    capaSexual.clearLayers();
    
    if(typeof capaMunicipios!="undefined" && capaMunicipios!=null){
         capaMunicipios.clearLayers();
    }
    
    mostrarGeoLoader(); 
    $.getJSON("api/filtro.php?departamento="+$("#departamento").val()+"&desde="+$("#desde").val()+"&hasta="+$("#hasta").val(),  function(data) {
        hideLoading();
        refrescarMapa(data,"municipio")
        ocultarGeoLoader();
    });
    }else{
        cargaGeneral();
    }
}

function cargaxMunicipio(){
    if($("#municipio").val()!=""){
        capaHurto.clearLayers();
        capaViolencia.clearLayers();
        capaSexual.clearLayers();
         mostrarGeoLoader(); 
        $.getJSON("api/filtromunicipio.php?departamento="+$("#departamento").val()+"&desde="+$("#desde").val()+"&hasta="+$("#hasta").val()+"&municipio="+$("#municipio").val(),  function(data) {
           
            capaMunicipios.clearLayers();
            pintarLimitesMunicipios(municipios, $("#departamento").val(),$("#municipio").val().toUpperCase());
            refrescarMapa(data,"municipio")
             hideLoading();
             ocultarGeoLoader();
        });
    }else{
      if($("#departamento").val()!=""){
          pintarLimitesMunicipios(municipios, $("#departamento").val());
       }
       cargaxDepartamento(); 
    }
}

function cargaMunicipioxZona(){
    if($("#departamento").val()=="SANTANDER"){
        if($("#zona").val()!=""){
             capaHurto.clearLayers();
            capaViolencia.clearLayers();
            capaSexual.clearLayers();
            mostrarGeoLoader(); 
            $.getJSON("api/filtrozona.php?departamento="+$("#departamento").val()+"&desde="+$("#desde").val()+"&hasta="+$("#hasta").val()+"&zona="+$("#zona").val(),  function(data) {
                cargarMunicipiosSelect($("#departamento").val(),false);
                capaMunicipios.clearLayers();
                pintarLimitesMunicipios(municipios, $("#departamento").val(),$("#municipio").val().toUpperCase());
                refrescarMapa(data,"municipio")
                 hideLoading();
                 ocultarGeoLoader();
            });
        }else{
            cargaxDepartamento();
        }
    }else{
        $(".zona").css("display","none");
    }
}


