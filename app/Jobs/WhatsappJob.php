<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;          
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\SendWhatsapp;
use Illuminate\Support\Facades\Http;


class WhatsappJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $server;
    protected $session;
    protected $code;
    protected $phone;
    protected $url;
    protected $message;
    protected $type;

    public function __construct($server, $session, $code, $phone, $url, $message, $type)
    {
        $this->server = $server;
        $this->session = $session;
        $this->code = $code;
        $this->phone = $phone;
        $this->url = $url;
        $this->message = $message;
        $this->type = $type;
    }

    public function handle()
    {
        $urlStatus = $this->server.'/status?id='.$this->session;
        $response = Http::get($urlStatus)->json();
        $baseUrlImage = setting('servidores.image-from-url');

        $url_image = '';
        if (!empty($this->url)) {
            try {
                $responseImage = Http::get($baseUrlImage . '/generate', [
                    'url' => $this->url
                ]);
                if ($responseImage->successful() && ($obj = $responseImage->object()) && isset($obj->url)) {
                    $url_image = 'https://' . $obj->url;
                }
            } catch (\Exception $e) {}
        }

        if($response['success'] == true) {
            if($response['status'] == true) {        
                    // $res = $url_image->object();
                    $url = $this->server.'/send?id='.$this->session.'&token='.null;
                    $responseSend = Http::post($url, [
                        'phone' => '+'.$this->code.''.$this->phone,
                        'text' => $this->message,
                        'image_url' => $url_image,
                    ])->json();

                    if($responseSend['success'] == true) {
                        $this->bd($this->server, $this->session, $this->code, $this->phone, $this->url, $this->message, $this->type, 'Enviado');
                    } else {
                        $this->bd($this->server, $this->session, $this->code, $this->phone, $this->url, $this->message, $this->type, 'No Enviado');
                    }
            } 
            else
            {
                $this->bd($this->server, $this->session, $this->code, $this->phone, $this->url, $this->message, $this->type, 'Whatsapp Desconectado');
            }
        }
        else
        {
            $this->bd($this->server, $this->session, $this->code, $this->phone, $this->url, $this->message, $this->type, 'Servidor Fuera de Servicio');
        }
    }

    public function bd($server, $session, $code, $phone, $url, $message, $type, $status)
    {
        SendWhatsapp::create([
            'server' => $server,
            'session' => $session,
            'country_code' => $code,
            'phone' => $phone,
            'url' => $url,
            'message' => $message,
            'type' => $type,
            'status' => $status,
        ]);
    }
}
