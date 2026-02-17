<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<!-- <p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/agustinmejia/farmacia/master/public/img/icon.png" width="150"></a></p> -->

# Sistema de Administración de Prestamos

## Requisitos
- php ^7.3|^8.0
- mysql
- Extensiones de php (mbstring, intl, dom, gd, xml, zip, mbstring, mysql)

## Instalación
```
composer install
cp .env.example .env
php artisan prestamos:install
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data storage bootstrap/cache
```

## Iniciar con docker
```sh
docker build -t prestamos .
docker run -e DB_DATABASE=prestamos -e DB_HOST=host.docker.internal -p 8000:8000 -t prestamos
```

## Despliegue
- Local

```
node server
php artisan queue:work --queue=high,default
php artisan queue:work --queue=high,low
```

- Producción (Usando PM2)

```
pm2 start server.js --name "prestamos-js"
pm2 start worker.yml
```

- Producción (Usando Supervisor)

```
supervisord -c /etc/supervisor/supervisord.conf
supervisorctl status # verificar estado
```

- Producción (Usando Systemd)

Crear el archivo de servicio:

```bash
sudo nano /etc/systemd/system/capresi-worker.service
```

Pegar el siguiente contenido:

```ini
[Unit]
Description=Laravel Queue Worker (Capresi)
After=network.target mysql.service redis.service

[Service]
User=www-data
Group=www-data
Restart=always
RestartSec=3
WorkingDirectory=/var/www/production/capresi

# CAMBIO AQUÍ: Aumentamos timeout a 380 segundos (6 min 20 seg)
ExecStart=/usr/bin/php /var/www/production/capresi/artisan queue:work --sleep=3 --tries=2 --timeout=380

# CAMBIO AQUÍ: Aumentamos TimeoutStopSec acorde al nuevo timeout
TimeoutStopSec=390
StandardOutput=append:/var/www/production/capresi/storage/logs/worker.log
StandardError=inherit
Environment=APP_ENV=production

[Install]
WantedBy=multi-user.target
```

Recargar, reiniciar y verificar estado:

```bash
sudo systemctl daemon-reload
sudo systemctl restart capresi-worker.service
sudo systemctl status capresi-worker.service
```

## Limpiar caché del Job

Para limpiar la caché de la programación de mensajes de WhatsApp (clave `last_whatsapp_schedule`), ejecuta el siguiente comando:

```bash
php artisan whatsapp:clear-cache
```

Esto reiniciará el cálculo de tiempos para los siguientes envíos en cola.
