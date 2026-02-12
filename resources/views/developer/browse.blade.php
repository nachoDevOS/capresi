@extends('voyager::master')

@section('page_title', 'Viendo Prestamos')


@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0px">
                        <div class="col-md-4" style="padding: 0px">
                            <h1 id="titleHead" class="page-title">
                                <i class="fa-solid fa-hand-holding-dollar"></i> DEV
                            </h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="page-content browse container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <form class="form-submit" action="{{ route('dev.post') }}" method="post">
                            @csrf

                            <input type="text" name="latitude" id="latitudeField">
                            <input type="text" name="longitude" id="longitudeField">


                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Actualizar</button>
                        </form>


                        <button type="button" onclick="requestLocation()" class="btn btn-primary">
                            <i class="voyager-location"></i> Activar Ubicación
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>    
@stop

@section('css')



@stop

@section('javascript')




<script>
    function obtenerUbicacionForzada() {
        // 1. Verificar soporte de geolocalización
        if (!navigator.geolocation) {
            mostrarError("Tu navegador no soporta geolocalización");
            return;
        }
    
        // 2. Configuración estricta del GPS
        const opcionesGPS = {
            enableHighAccuracy: true,  // Forzar alta precisión (GPS)
            timeout: 15000,            // 15 segundos de espera
            maximumAge: 0              // No usar datos cacheados
        };
    
        // 3. Solicitar ubicación
        navigator.geolocation.getCurrentPosition(
            function(posicion) {
                // Validar precisión
                if (posicion.coords.accuracy > 100) {
                    mostrarAdvertencia(`Precisión baja (${Math.round(posicion.coords.accuracy)}m). Usando igualmente los datos.`);
                }
                
                // Asignar valores a los campos
                const campoLat = document.querySelector('[name="latitude"], #latitude, input.latitude');
                const campoLng = document.querySelector('[name="longitude"], #longitude, input.longitude');
                
                if (campoLat && campoLng) {
                    campoLat.value = posicion.coords.latitude.toFixed(6);
                    campoLng.value = posicion.coords.longitude.toFixed(6);
                    mostrarExito("¡Ubicación obtenida correctamente!");
                } else {
                    mostrarError("No se encontraron campos para coordenadas");
                }
            },
            function(error) {
                manejarErrorGPS(error);
            },
            opcionesGPS
        );
    }
    
    // Funciones auxiliares
    function manejarErrorGPS(error) {
        const errores = {
            1: "Permiso denegado. Debes activar la ubicación en los ajustes de tu dispositivo.",
            2: "No se puede obtener la ubicación. Verifica que el GPS esté activado.",
            3: "Tiempo de espera agotado. El GPS está respondiendo lentamente."
        };
        
        mostrarError(errores[error.code] || "Error desconocido al obtener la ubicación");
    }
    
    function mostrarExito(mensaje) {
        alert(mensaje); // Puedes reemplazar con un toast o notificación bonita
        console.log("Éxito: " + mensaje);
    }
    
    function mostrarAdvertencia(mensaje) {
        alert(mensaje);
        console.warn(mensaje);
    }
    
    function mostrarError(mensaje) {
        alert("ERROR: " + mensaje);
        console.error(mensaje);
    }
    
    // Ejecutar al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        // Esperar 1 segundo para que Voyager cargue completamente los campos
        setTimeout(obtenerUbicacionForzada, 1000);
    });
    
    // Opcional: Botón para reintentar
    function agregarBotonReintento() {
        const boton = document.createElement('button');
        boton.textContent = 'Obtener Ubicación';
        boton.className = 'btn btn-primary';
        boton.style.margin = '10px 0';
        boton.onclick = obtenerUbicacionForzada;
        
        const contenedor = document.querySelector('.form-group.latitude') || 
                          document.querySelector('.form-content');
        if (contenedor) {
            contenedor.appendChild(boton);
        }
    }
    
    // Llamar a la función para agregar el botón
    agregarBotonReintento();
</script>
    




@stop