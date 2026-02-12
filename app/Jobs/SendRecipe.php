<?php

namespace App\Jobs;

use App\Models\Loan;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Http;

class SendRecipe implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $url;
    protected $phone;
    protected $type;
    protected $mesage;

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
        
        // Asignar cola según el tipo
        $this->onQueue($type === 'Comprobante' ? 'high' : 'low');
    }

   
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

        if (setting('servidores.image-from-url') && setting('servidores.whatsapp') && setting('servidores.whatsapp-session')) {
            $response = Http::get(setting('servidores.image-from-url').'/generate?url='.$this->url);
            
            if ($response->ok()) {
                $res = json_decode($response->body());
                if($this->phone != Null){
                    $result = Http::post(setting('servidores.whatsapp').'/send?id='.setting('servidores.whatsapp-session'), [
                        // 'phone' => '59167285914',
                        'phone' => '591'.$this->phone,                    
                        'text' => $this->mesage,
                        // 'image_url' => '',
                        'image_url' => $res->url,
                    ]);
                    Log::channel('notificacion')->info($result);
                }
                else
                {
                    Log::channel('errorComprobante')->warning('No tiene numero de celular registrado. URL: '.$this->url);
                }
            } 
            else {                
                Log::channel('errorComprobante')->error('Error al generar la imagen desde la URL. URL: '.$this->url);
            }
        }
    }

    protected function handleNotificationsBatch()
    {
        // En lugar de procesar todo aquí, despachamos Jobs individuales
        foreach ($this->mesage as $data) {
            SendNotification::dispatch($data);
        }
    }
}
