<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ubicación Requerida</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Tipografía & Toastr -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            color: #fff;
        }

        .bg-animation {
            position: absolute;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.05) 0%, transparent 40%),
                        radial-gradient(circle at 70% 70%, rgba(255, 255, 255, 0.03) 0%, transparent 40%);
            animation: pulse 6s infinite alternate;
            z-index: 0;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(1.1); opacity: 0.9; }
        }

        .card {
            position: relative;
            z-index: 2;
            background-color: #ffffff15;
            backdrop-filter: blur(14px);
            border-radius: 16px;
            padding: 2.5rem;
            max-width: 480px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            animation: fadeIn 1s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo {
            width: 70px;
            margin-bottom: 1.5rem;
        }

        .icon {
            width: 60px;
            height: 60px;
            margin-bottom: 1rem;
        }

        h1 {
            font-size: 1.8rem;
            margin-bottom: 0.8rem;
        }

        p {
            font-size: 1rem;
            margin-bottom: 1.8rem;
            line-height: 1.6;
        }

        .btn {
            background-color: #facc15;
            color: #1e293b;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 1rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn:hover {
            background-color: #eab308;
            transform: translateY(-2px);
        }

        footer {
            margin-top: 2rem;
            font-size: 0.85rem;
            color: #cbd5e1;
        }
    </style>
</head>
<body>

    <div class="bg-animation"></div>

    <div class="card">
        <!-- Logo de sistema (reemplazable) -->
        <img src="https://laravel.com/img/logomark.min.svg" alt="Logo" class="logo">

        <!-- Icono ubicación (SVG) -->
        {{-- <svg class="icon" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"
             xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 10.5a1.5 1.5 0 100 3 1.5 1.5 0 000-3z"/>
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M19.5 12a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0z"/>
        </svg> --}}

        <svg class="icon" xmlns="http://www.w3.org/2000/svg" 
            width="64" height="64" fill="none" stroke="currentColor" stroke-width="2" 
            viewBox="0 0 24 24">
            <path d="M21 10c0 6-9 13-9 13S3 16 3 10a9 9 0 0 1 18 0z"></path>
            <circle cx="12" cy="10" r="3"></circle>
        </svg>


        <h1>GPS Desactivado</h1>
        <p>Este sistema requiere acceso a tu ubicación para funcionar correctamente.<br>
            Por favor, activá el GPS de tu dispositivo y volvé a intentarlo.</p>
        <button class="btn" onclick="verificarGPS()">Reintentar</button>

        <footer>© {{ date('Y') }} CAPRESI</footer>
    </div>

    <!-- Scripts -->
    <script type="text/javascript" src="{{ voyager_asset('js/app.js') }}"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-bottom-center',
            timeOut: 4000,
        };

        function verificarGPS() {
            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        window.location.href = "/admin";
                    },
                    function (error) {
                        toastr.error("El GPS sigue desactivado. Activá la ubicación para continuar.");
                    }
                );
            } else {
                toastr.warning("Tu navegador no soporta geolocalización.");
            }
        }
    </script>
</body>
</html>
