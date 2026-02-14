<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendRecipe implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $url;
    protected $phone;
    protected $type;
    protected $mesage;

    /**
     * El número de segundos que el trabajo puede ejecutarse antes de un timeout.
     * Dado que usas APIs externas, 120s es un margen seguro.
     */
    public $timeout = 120;

    /**
     * Número de veces que el job se reintentará si falla.
     */
    public $tries = 3;

    /**
     * Segundos a esperar antes de reintentar un job fallido.
     */
    public $backoff = 10;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($url, $phone, $mesage, $type)
    {
        $this->url = $url;
        $this->phone = $phone;
        $this->type = $type;
        $this->mesage = $mesage;
        
        // Asignar cola según el tipo al momento de crearlo
        $this->onQueue($type === 'Comprobante' ? 'high' : 'low');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->type == 'Comprobante') {
            $this->handleComprobante();
        } else {
            $this->handleNotificationsBatch();
        }
    }

    protected function handleComprobante()
    {
        // Validamos que existan las configuraciones necesarias
        $baseUrlImage = setting('servidores.image-from-url1');
        $baseUrlWhatsapp = setting('servidores.whatsapp1');
        $sessionWhatsapp = setting('servidores.whatsapp-session1');

        if ($baseUrlImage && $baseUrlWhatsapp && $sessionWhatsapp) {
            
            // 1. Intentar generar la imagen (con timeout de 60s)
            try {
                $response = Http::timeout(60)->get($baseUrlImage . '/generate?url=' . $this->url);
                
                if ($response->ok()) {
                    $res = $response->object(); // Más limpio que json_decode

                    if ($this->phone != null && isset($res->url)) {
                        // 2. Enviar por WhatsApp
                        $result = Http::timeout(30)->post($baseUrlWhatsapp . '/send?id=' . $sessionWhatsapp, [
                            'phone' => '591' . $this->phone,                    
                            'text' => $this->mesage,
                            'image_url' => $res->url,
                        ]);

                        Log::channel('notificacion')->info("WhatsApp enviado a {$this->phone}: " . $result->body());
                    } else {
                        Log::channel('errorComprobante')->warning('No tiene número de celular o la URL de imagen no se generó. URL: ' . $this->url);
                    }
                } else {
                    Log::channel('errorComprobante')->error('Error en servicio de imagen: ' . $response->status());
                }
            } catch (\Exception $e) {
                Log::channel('errorComprobante')->error('Excepción en SendRecipe: ' . $e->getMessage());
                // Lanzamos la excepción para que el Job se reintente si falla por red
                throw $e; 
            }
        }
    }

    protected function handleNotificationsBatch()
    {
        // Validamos que mesage sea iterable para evitar errores
        if (is_iterable($this->mesage)) {
            foreach ($this->mesage as $data) {
                // Despachamos Jobs individuales para no sobrecargar este Job
                SendNotification::dispatch($data);
            }
        }
    }
}