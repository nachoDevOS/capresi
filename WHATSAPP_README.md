# Comandos de WhatsApp

## Limpiar caché del Job

Para limpiar la caché de la programación de mensajes de WhatsApp (clave `last_whatsapp_schedule`), ejecuta el siguiente comando:

```bash
php artisan whatsapp:clear-cache
```

Esto reiniciará el cálculo de tiempos para los siguientes envíos en cola.