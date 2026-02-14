<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ setting('admin.title') }} | Notificación de pago</title>
        
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
        <!-- Favicon -->
        <?php $admin_favicon = Voyager::setting('admin.icon_image', ''); ?>
        @if($admin_favicon == '')
            <link rel="shortcut icon" href="{{ asset('images/icon.png') }}" type="image/png">
        @else
            <link rel="shortcut icon" href="{{ Voyager::image($admin_favicon) }}" type="image/png">
        @endif
    </head>
    <body>
        <style>
            *{
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            body{
                font-family: 'Roboto', sans-serif;
                background-color: #f0f2f5;
                color: #333;
            }
            .container{
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 20px;
                min-height: 100vh;
            }
            .card{
                max-width: 450px;
                width: 100%;
                background-color: #fff;
                border-radius: 16px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                overflow: hidden;
                position: relative;
            }
            .card-header{
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 20px;
                background-color: #fff;
                border-bottom: 2px dashed #f0f2f5;
                position: relative;
                z-index: 1;
            }
            .card-header h3 {
                margin: 0;
                font-size: 20px;
                font-weight: 700;
                color: #495057;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            .card::before {
                content: "";
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-image: url('{{ $admin_favicon == '' ? asset('images/icon.png') : Voyager::image($admin_favicon) }}');
                background-repeat: no-repeat;
                background-position: center;
                background-size: 250px;
                opacity: 0.3;
                /* transform: rotate(-15deg); */
                z-index: 0;
                pointer-events: none;
            }
            .card-body{
                padding: 25px;
                position: relative;
                z-index: 1;
            }
            .card-body .body-main{
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                text-align: center;
            }
            .body-main .msj {
                font-size: 24px;
                font-weight: 700;
                color: #28a745;
                margin-bottom: 8px;
            }
            .body-main .money{
                font-size: 36px;
                font-weight: 700;
                color: #212529;
                margin-bottom: 20px;
            }
            .body-main .money span{
                font-size: 20px;
                font-weight: 500;
                color: #6c757d;
            }
            .separator {
                border-top: 1px dashed #ced4da;
                margin: 20px 0;
            }
            .logo{
                height: 40px;
                margin-right: 10px;
            }
            .group-table{
                margin-bottom: 15px;
            }
            .group-table p{
                margin: 0;
                line-height: 1.6;
            }
            .group-table .account{
                font-size: 12px;
                color: #6c757d;
                text-transform: uppercase;
            }
            .group-table .name{
                font-size: 15px;
                font-weight: 500;
                color: #343a40;
            }
            .group-table .number-account{
                font-size: 14px;
                color: #495057;
            }

            .card-footer{
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                padding: 20px;
                background-color: #fff;
                text-align: center;
                border-top: 2px dashed #f0f2f5;
                position: relative;
                z-index: 1;
            }
            .card-footer p {
                margin: 0;
                font-size: 12px;
                color: #6c757d;
            }
            .table-details {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }

            .table-details th, .table-details td{
                padding: 5px;
                font-size: 13px;
                text-align: left;
                border-bottom: 1px solid #dee2e6;
            }
            .table-details th {
                font-weight: 500;
                color: #6c757d;
                border-bottom-width: 2px;
            }
            @media print {
                body {
                    background-color: #fff;
                }
                .card {
                    box-shadow: none;
                }
            }
        </style>
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h3>{{ $title }}</h3>
                </div>
                <div class="card-body">
                    <div class="body-main">
                        @yield('body')
                    </div>
                    <hr class="separator">
                    @yield('info')
                </div>
                <div class="card-footer">
                    @yield('footer')
                </div>
            </div>
        </div>
    </body>
</html>