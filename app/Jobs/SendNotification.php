<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Loan;
use App\Models\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SendNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
        $this->onQueue('low'); // Cola de baja prioridad
    }

    public function handle()
    {
        $data = $this->data;
        
        $aux = Loan::where('id', $data->loan)->first();
        $message = '*Sr. (a) '.$data->last_name1.' '.$data->last_name2.' '.$data->first_name.'*, informarle que tiene dias de atraso en su prestamo diario, pase por oficina para cancelar porfavor.
            *Dias Atrasados:* '.$data->cant.'
            *Monto Total: Bs.* '.$data->amount.'
            Gracias🤝😊';

        if ($data->cell_phone != null) {
            $result = Http::post(setting('servidores.whatsapp').'/send?id='.setting('servidores.whatsapp-session'),  [
                // 'phone' => '59167285914',
                // 'text' => $message.'-'.Carbon::now(),

                'phone' => '591'.$data->cell_phone,
                'text' => $message,

                'image_url' => '',
            ]);
            Log::channel('notificacion')->info($result);
        }
        else
        {
            Log::channel('errorNotificacion')->warning('No tiene numero de celular registrado. Cliente: '.$data->first_name.' '.$data->last_name1.' '.$data->last_name2.' CI: '.$data->ci);
        }

        $aux->update(['notificationDate' => date('Y-m-d')]);
        
        Notification::create([
            'type' => 'diario',
            'details' => json_encode([
                'loan_id' => $data->loan,
                'notificationDate' => $data->notificationDate,
                'dateDelivered' => $data->dateDelivered,
                'people' => $data->people,
                'first_name' => $data->first_name,
                'last_name1' => $data->last_name1,
                'last_name2' => $data->last_name2,
                'cell_phone' => $data->cell_phone?$data->cell_phone:'',
                'code' => $data->code,
                'cantDay' => $data->cant,
                'amount' => $data->amount,
                'message' => $message
            ]),
            'dateTime' => Carbon::now()
        ]);
        
        sleep(rand(10, 20)); // Pausa aleatoria entre 10 y 20 segundos
    }
}
