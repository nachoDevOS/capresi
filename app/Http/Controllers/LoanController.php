<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\People;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Loan;
use App\Models\LoanDay;
use App\Models\LoanRoute;

use App\Models\Route;
use Illuminate\Support\Facades\Http;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\FileController;
use App\Models\LoanDayAgent;
// Models
use App\Models\PaymentsPeriod;

use App\Jobs\WhatsappJob;

class LoanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('loans.browse');
    }

    public function list($type, $search = null)
    {
        $user = Auth::user();
        $paginate = request('paginate') ?? 10;

        $status = null;
        $type ? ($status = "status = '$type'") : 1;
        $type == 'pagado' ? ($status = 'debt = 0') : 1;

        $data = Loan::with(['people', 'manager'])
            ->where(function ($query) use ($search) {
                if ($search) {
                    $query
                        ->OrwhereHas('people', function ($query) use ($search) {
                            $query->whereRaw("(ci like '%$search%' or first_name like '%$search%' or last_name1 like '%$search%' or last_name2 like '%$search%' or CONCAT(first_name, ' ', last_name1, ' ', last_name2) like '%$search%')");
                        })
                        ->OrWhereRaw($search ? "typeLoan like '%$search%'" : 1)
                        ->OrWhereRaw($search ? "code like '%$search%'" : 1);
                }
            })
            ->where('deleted_at', null)
            ->whereRaw($status ? $status : 1)
            // ->whereRaw($type=='pagado'? "debt == 0":1)
            ->orderBy('code', 'DESC')
            ->paginate($paginate);

        return view('loans.list', compact('data'));
    }

    public function create()
    {
        $routes = Route::where('deleted_at', null)->where('status', 1)->orderBy('name')->get();

        return view('loans.add', compact('routes'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $loan = Loan::create([
                'people_id' => $request->people_id,
                'guarantor_id' => $request->guarantor_id ? $request->guarantor_id : null,
                'manager_id' => $request->manager_id,
                'date' => $request->date,
                'day' => $request->day,
                'observation' => $request->observation,

                'typeLoan' => $request->optradio,

                'porcentage' => $request->porcentage,
                'amountLoan' => $request->amountLoan,
                'amountPorcentage' => $request->amountPorcentage,

                'debt' => $request->amountTotal,
                'amountTotal' => $request->amountTotal,

                'register_userId' => Auth::user()->id,
                'register_agentType' => Auth::user()->role->name,
                'status' => 'pendiente',
            ]);

            $loan->update([
                'code' => 'CP-' . str_pad($loan->id, 5, '0', STR_PAD_LEFT),
                'status' => 'verificado'
            ]);

            LoanRoute::create([
                'loan_id' => $loan->id,
                'route_id' => $request->route_id,
                'observation' => 'Primer ruta',
                'register_userId' => Auth::user()->id,
                'register_agentType' => Auth::user()->role->name,
            ]);

            DB::commit();
            return redirect()
                ->route('loans.index')
                ->with(['message' => 'Registrado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()
                ->route('loans.index')
                ->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }
   

    

    public function show($id)
    {
        // return view('loans.read');
    }

    public function printCalendar($id)
    {
        $loan = Loan::with(['people', 'loanDay'])
            ->where('deleted_at', null)
            ->where('id', $id)
            ->first();
        // return $loan;

        // Para imprimir el calendario Nuevo
        $id = $id;
        $loan = Loan::with(['loanDay', 'loanRoute', 'people', 'guarantor'])
            ->where('deleted_at', null)
            ->where('id', $id)
            ->first();
        // $loanDay = LoanDay::where('loan_id', $loan->id)->get();

        $loanday = LoanDay::where('loan_id', $id)->where('deleted_at', null)->orderBy('number', 'ASC')->get();

        $cantMes = DB::table('loan_days')->where('loan_id', $id)->select(DB::raw('DATE_FORMAT(date, "%Y-%m") as meses'), DB::raw('DATE_FORMAT(date, "%m") as mes'), DB::raw('DATE_FORMAT(date, "%Y") as ano'))->orderBy('number', 'ASC')->groupBy('meses')->get();

        $route = LoanRoute::with(['route'])
            ->where('loan_id', $id)
            ->where('status', 1)
            ->where('deleted_at', null)
            ->first();

        $register = Auth::user();
        $date = date('Y-m-d');
        return view('loans.print-calendar', compact('loan', 'route', 'loanday', 'register', 'date', 'cantMes'));
    }

    public function destroy(Request $request, $id)
    {
        $loan = Loan::where('deleted_at', null)
            ->where('id', $id)
            ->whereIn('status', ['pendiente', 'verificado', 'aprobado'])
            ->first();
        if (!$loan) {
            return redirect()
                ->route('loans.index')
                ->with(['message' => 'El registro no se encuentra disponible.', 'alert-type' => 'error']);
        }
        DB::beginTransaction();
        try {
            Loan::where('id', $id)->update([
                'deleted_at' => Carbon::now(),
                'deleted_userId' => Auth::user()->id,
                'deleted_agentType' => Auth::user()->role->name,
                'deleteObservation' => $request->deleteObservation,
            ]);
            DB::commit();
            return redirect()
                ->route('loans.index')
                ->with(['message' => 'Eliminado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()
                ->route('loans.index')
                ->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function declineLoan($id)
    {
        $loan = Loan::where('id', $id)
            ->where('deleted_at', null)
            ->whereIn('status', ['pendiente', 'verificado', 'aprobado'])
            ->first();
        if (!$loan) {
            return redirect()
                ->route('loans.index')
                ->with(['message' => 'Error, El registro no se encuentra disponible.', 'alert-type' => 'error']);
        }
        DB::beginTransaction();
        try {
            $loan->update([
                'status' => 'rechazado',
            ]);
            DB::commit();
            return redirect()
                ->route('loans.index')
                ->with(['message' => 'Rechazado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()
                ->route('loans.index')
                ->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    //Para aprobar un prestamo el gerente
    public function approveLoan($loan)
    {
        $loan = Loan::with(['people'])
            ->where('id', $loan)
            ->where('deleted_at', null)
            ->where('status', 'verificado')
            ->first();
        if (!$loan) {
            return redirect()
                ->route('loans.index')
                ->with(['message' => 'Error, El registro no se encuentra disponible.', 'alert-type' => 'error']);
        }

        DB::beginTransaction();
        try {
            $this->sendRandomLoanApprovalMessage($loan->people->cell_phone, $loan->people->first_name . ' ' . $loan->people->last_name1 . ' ' . $loan->people->last_name2);
            $loan->update([
                'status' => 'aprobado',
                'success_userId' => Auth::user()->id,
                'success_agentType' => Auth::user()->role->name,
            ]);
            DB::commit();
            return redirect()
                ->route('loans.index')
                ->with(['message' => 'Prestamo aprobado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()
                ->route('loans.index')
                ->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    /**
     * Sends a random WhatsApp message for loan approval.
     *
     * @param string $phone The recipient's phone number.
     * @param string $fullName The full name of the loan recipient.
     * @return void
     */
    private function sendRandomLoanApprovalMessage($phone, $fullName)
    {
        // 1. Saludo según la hora del día
        $time = now()->format('H:i');
        if ($time >= '06:00' && $time < '12:00') {
            $time_greeting = "Buen día";
        } elseif ($time >= '12:00' && $time < '19:00') {
            $time_greeting = "Buenas tardes";
        } else {
            $time_greeting = "Buenas noches";
        }

        // 2. Partes del mensaje para combinar aleatoriamente
        $salutations = [
            "¡Excelente noticia, *{$fullName}*!",
            "Hola *{$fullName}*,",
            "Felicidades, *{$fullName}*.",
            "Estimado(a) *{$fullName}*,",
            "¡Buenas noticias, *{$fullName}*!",
            "¡Atención, *{$fullName}*!",
            "¡Todo listo *{$fullName}*!",
            "¡Enhorabuena *{$fullName}*!",
            "Saludos *{$fullName}*,"
        ];

        $bodies = [
            "su solicitud de préstamo ha sido *APROBADA*.",
            "nos complace informarle que su préstamo ha sido *APROBADO*.",
            "su solicitud de préstamo ha sido *APROBADA* con éxito.",
            "su préstamo ha sido *APROBADO*.",
            "su solicitud de crédito ha sido *APROBADA*.",
            "hemos aprobado su solicitud.",
            "le confirmamos que su préstamo está *APROBADO*.",
            "su solicitud ha sido procesada y *APROBADA*.",
            "su préstamo está listo para ser entregado.",
            "su crédito ha sido autorizado.",
            "su trámite finalizó con éxito: *APROBADO*."
        ];

        $callsToAction = [
            "Le esperamos en nuestras oficinas para finalizar el proceso.",
            "Pase por nuestras oficinas para la entrega.",
            "Acérquese a nuestras oficinas para la entrega.",
            "Por favor, visite nuestras oficinas para la entrega.",
            "Pase por nuestras oficinas para la entrega de su dinero.",
            "Estamos listos para atenderle en nuestras oficinas.",
            "Acérquese a nuestras instalaciones para completar el proceso.",
            "Venga a recogerlo cuando guste en horario de oficina.",
            "Le aguardamos en nuestras oficinas.",
            "Pase a retirarlo.",
            "ya puede pasar por nuestras oficinas para recibir su préstamo.",
            "Visítenos.",
            "Acérquese a nuestras oficinas para finalizar.",
            "Le esperamos para la entrega.",
            "Venga por su dinero.",
            "Pase por caja en nuestras oficinas.",
            "Le esperamos."
        ];

        $closings = [
            "¡Gracias por su confianza!", "¡Saludos!", "¡Le esperamos!", "¡Gracias!", "¡No se lo pierda!",
            "¡Bienvenido(a)!", "¡Le esperamos con gusto!", "¡Felicidades!", "¡Hasta pronto!", "Que tenga un excelente día."
        ];

        $emojis = ['🤝', '✨', '😊', '🙏', '💰', '🎉', '😃', '✅', '👍', '🌟', '💸', '🏦', '💼', '👋', '😎', '🔥', '💫', '🙌', '👏', '🤩'];

        // 3. Construcción del mensaje final aleatorio
        $randomSalutation = $salutations[array_rand($salutations)];
        $randomBody = $bodies[array_rand($bodies)];
        $randomCallToAction = $callsToAction[array_rand($callsToAction)];
        $randomClosing = $closings[array_rand($closings)];
        $randomEmoji = $emojis[array_rand($emojis)];

        // Combinar todo
        $finalMessage = "{$time_greeting}. {$randomSalutation} {$randomBody} {$randomCallToAction} {$randomClosing} {$randomEmoji}";

        // 4. Envío del mensaje
        $servidor = setting('servidores.whatsapp');
        $session = setting('servidores.whatsapp-session');

        if ($phone && is_numeric($phone) && $servidor && $session) {
            Http::post($servidor . '/send?id=' . $session, [
                'phone' => '591' . $phone,
                'text' => $finalMessage,
                'image_url' => '', // No image for approval
            ]);
            Log::info("WhatsApp approval message sent to {$phone}. Message: {$finalMessage}");
        } else {
            Log::warning("WhatsApp approval message not sent. Invalid phone, server settings, or session for {$phone}.");
        }
    }

    // funcion para entregar dinero al beneficiario
    public function moneyDeliver($loan)
    {
        $global_cashier = $this->availableMoney(Auth::user()->id, 'user')->original;
        if (!$global_cashier['cashier']) {
            return redirect()
                ->route('loans.index')
                ->with(['message' => 'Error, La caja no se encuentra abierta.', 'alert-type' => 'error']);
        }

        $loan = Loan::where('id', $loan)->where('deleted_at', null)->where('status', 'aprobado')->first();
        if (!$loan) {
            return redirect()
                ->route('loans.index')
                ->with(['message' => 'El Registro no se encuentra disponible.', 'alert-type' => 'error']);
        }
        if ($global_cashier['amountCashier'] < $loan->amountLoan) {
            return redirect()
                ->route('voyager.dashboard')
                ->with(['message' => 'No tiene suficiente dinero disponible.', 'alert-type' => 'warning']);
        }
        DB::beginTransaction();
        try {
  
            $date = date('Y-m-d');
            $date = date('d-m-Y', strtotime($date . '+ 1 days'));

            if ($loan->typeLoan == 'diario') {
                for ($i = 1; $i <= $loan->day; $i++) {
                    $fecha = Carbon::parse($date);
                    $fecha = $fecha->format('l');
                    if ($fecha == 'Sunday') {
                        $date = date('Y-m-d', strtotime($date));
                        $date = date('d-m-Y', strtotime($date . '+ 1 days'));
                    }
                    $date = date('Y-m-d', strtotime($date));
                    LoanDay::create([
                        'loan_id' => $loan->id,
                        'number' => $i,
                        'debt' => $loan->amountTotal / $loan->day,
                        'amount' => $loan->amountTotal / $loan->day,

                        'date' => $date,
                    ]);
                    $date = date('d-m-Y', strtotime($date . '+ 1 days'));
                }
            } else {
                $loanDay = $loan->day;
                $amount = $loan->amountTotal;
                $amountDay = $amount / $loanDay;

                $aux = intval($amountDay);

                $dayT = $aux * $loanDay;

                $firstAux = $amount - $dayT;
                $first = $aux + $firstAux;

                if ($amount != $dayT + $firstAux) {
                    DB::rollBack();
                    return redirect()
                        ->route('loans.index')
                        ->with(['message' => 'Ocurrió un error en la distribucion.', 'alert-type' => 'error']);
                }

                for ($i = 1; $i <= $loan->day; $i++) {
                    $fecha = Carbon::parse($date);
                    $fecha = $fecha->format('l');
                    if ($fecha == 'Sunday') {
                        $date = date('Y-m-d', strtotime($date));
                        $date = date('d-m-Y', strtotime($date . '+ 1 days'));
                    }
                    $date = date('Y-m-d', strtotime($date));
                    if ($i == 1) {
                        $debA = $first;
                    } else {
                        $debA = $aux;
                    }

                    LoanDay::create([
                        'loan_id' => $loan->id,
                        'number' => $i,
                        'debt' => $debA,
                        'amount' => $debA,

                        'date' => $date,
                    ]);
                    $date = date('d-m-Y', strtotime($date . '+ 1 days'));
                }
            }

            $loan->update([
                'cashier_id' => $global_cashier['cashier']->id,
                'delivered_userId' => Auth::user()->id,
                'delivered_agentType' => Auth::user()->role->name,
                'status' => 'entregado',
                'delivered' => 'Si',
                'dateDelivered' => Carbon::now(),
            ]);

            if ($loan->typeLoan == 'diario') {
                $cant = LoanDay::where('loan_id', $loan->id)->where('deleted_at', null)->get();
                if (count($cant) > 24) {
                    DB::rollBack();
                    return redirect()
                        ->route('loans.index')
                        ->with(['message' => 'Contactese con Soporte', 'alert-type' => 'error']);
                }
            }
            $loan = Loan::with(['people','agentDelivered', 'loanDay'])
                ->where('id', $loan->id)
                ->where('deleted_at', null)
                ->first();
            DB::commit();

            return redirect()
                ->route('loans.index')
                ->with(['message' => 'Registrado exitosamente.', 'alert-type' => 'success', 'loan' => $loan]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()
                ->route('loans.index')
                ->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    

    // Funcion  para imprimir el comprobante de pago al momento que se le entrega el prestamo al cliente o beneficiario
    public function printLoanComprobante($loan_id)
    {
        $loan = Loan::with(['people','agentDelivered', 'loanDay'])
                ->where('id', $loan_id)
                ->where('deleted_at', null)
                ->first();
        // return $loan;
        return view('loansPrint.print-LoanDelivered', compact('loan'));
    }

    public function printContracDaily($loan)
    {
        $loan = Loan::where('id', $loan)->first();
        return view('loans.print.loanDaily', compact('loan'));
    }

    // para ver el prestamos y poder abonar o pagar el dinero
    public function dailyMoney($loan)
    {
        $loan = Loan::with(['loanDay', 'loanRoute', 'people', 'guarantor'])
            ->where('deleted_at', null)
            ->where('id', $loan)
            ->first();

        $loanday = LoanDay::where('loan_id', $loan->id)->where('deleted_at', null)->orderBy('number', 'ASC')->get();

        $cantMes = DB::table('loan_days')->where('loan_id', $loan->id)->select(DB::raw('DATE_FORMAT(date, "%Y-%m") as meses'), DB::raw('DATE_FORMAT(date, "%m") as mes'), DB::raw('DATE_FORMAT(date, "%Y") as ano'))->orderBy('number', 'ASC')->groupBy('meses')->get();

        $route = LoanRoute::with(['route'])
            ->where('loan_id', $loan->id)
            ->where('status', 1)
            ->where('deleted_at', null)
            ->first();

        $register = Auth::user();
        $date = date('Y-m-d');
        return view('loans.add-dailyMoney', compact('loan', 'route', 'loanday', 'register', 'date', 'cantMes'));
    }

    // funcion para guardar el dinero diario en cada prestamos
    public function dailyMoneyStore(Request $request)
    {
        // return $request;
        $request->merge(['amount' => floatval($request->amount)]);

        $global_cashier = $this->availableMoney(Auth::user()->id, 'user')->original;
        if (!$global_cashier['cashier']) {
            return redirect()
                ->route('loans-daily.money', ['loan' => $request->loan_id])
                ->with(['message' => 'Error, La caja no se encuentra abierta.', 'alert-type' => 'error']);
        }

        $loan = Loan::with(['people'])
            ->where('id', $request->loan_id)
            ->where('deleted_at', null)
            ->first();

        if ($request->amount > $loan->debt) {
            return redirect()
                ->route('loans-daily.money', ['loan' => $request->loan_id])
                ->with(['message' => 'Monto Incorrecto.', 'alert-type' => 'error']);
        }
        DB::beginTransaction();
        try {
            $transaction = Transaction::create([
                'type' => $request->qr,
                'category' => 'prestamos diario',
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'DescriptionPrecision' => $request->precision,
            ]);
            $loan->update(['transaction_id' => $transaction->transaction]);
            $amount = $request->amount;

            $ok = LoanDay::where('loan_id', $request->loan_id)->where('date', $request->date)->where('debt', '>', 0)->first();
            // para la fecha actual del pago si existe numero en el calendario
            if ($ok) {
                $debt = $ok->debt;
                if ($amount > $debt) {
                    $amount = $amount - $debt;
                } else {
                    $debt = $amount;
                    $amount = 0;
                }
                LoanDay::where('id', $ok->id)->decrement('debt', $debt);

                LoanDayAgent::create([
                    'loanDay_id' => $ok->id,
                    'type' => $request->qr,
                    'cashier_id' => $global_cashier['cashier']->id,
                    'transaction_id' => $transaction->id,
                    'amount' => $debt,
                    'agent_id' => Auth::user()->id,
                    'agentType' => Auth::user()->role->name,
                    'recovery' => $loan->recovery,
                ]);
                Loan::where('id', $request->loan_id)->decrement('debt', $debt);
            }

            if ($amount > 0) {
                $day = LoanDay::where('loan_id', $request->loan_id)->where('debt', '>', 0)->orderBy('number', 'ASC')->get();
                foreach ($day as $item) {
                    $debt = $item->debt;
                    if ($amount > $debt) {
                        $amount = $amount - $debt;
                    } else {
                        $debt = $amount;
                        $amount = 0;
                    }
                    LoanDay::where('id', $item->id)->decrement('debt', $debt);
                    LoanDayAgent::create([
                        'loanDay_id' => $item->id,
                        'cashier_id' => $global_cashier['cashier']->id,
                        'type' => $request->qr,
                        'transaction_id' => $transaction->id,
                        'amount' => $debt,
                        'agent_id' => Auth::user()->id,
                        'agentType' => Auth::user()->role->name,
                        'recovery' => $loan->recovery,
                    ]);

                    Loan::where('id', $request->loan_id)->decrement('debt', $debt);
                    if ($amount <= 0) {
                        break;
                    }
                }
            }

            $transaction_id = $transaction->id;
            $data = Loan::with([
                    'people',
                    'loanDay' => function ($query) use ($transaction_id) {
                        $query
                            ->whereHas('loanDayAgents', function ($q) use ($transaction_id) {
                                $q->where('transaction_id', $transaction_id);
                            })
                            ->with([
                                'loanDayAgents' => function ($q) use ($transaction_id) {
                                    $q->where('transaction_id', $transaction_id)->with(['transaction', 'agent']);
                                },
                            ]);
                    },
                ])
                ->where('id', $loan->id)
                ->first();


            $url = route('loans.payment.notification', $transaction->id);

            $servidor = setting('servidores.whatsapp');
            $session = setting('servidores.whatsapp-session');

            // sleep(15); // Esperamos 5 segundos para asegurarnos de que la imagen esté disponible
            if($loan->people->cell_phone && is_numeric($loan->people->cell_phone) && $servidor && $session)
            {
                $this->whatsapp($servidor, $session, '591', $loan->people->cell_phone, $url, 'Automatico - Comprobante de pago', $loan->people->first_name);

            }
            

            DB::commit();
            return redirect()->route('loans-daily.money', ['loan' => $request->loan_id])->with(['message' => 'Pagado exitosamente.', 'alert-type' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('loans-daily.money', ['loan' => $request->loan_id])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }


    public function printDailyMoney($loan_id, $transaction_id)
    {
        $transaction_id = $transaction_id;
        $loan = Loan::where('id', $loan_id)->first();

        $loanDayAgent = DB::table('loan_days as ld')->join('loan_day_agents as la', 'la.loanDay_id', 'ld.id')->join('users as u', 'u.id', 'la.agent_id')->join('transactions as t', 't.id', 'la.transaction_id')->where('ld.loan_id', $loan_id)->where('t.id', $transaction_id)->select('ld.id as loanDay', 'ld.date', 'la.amount', 'u.name', 'la.agentType', 'la.id as loanAgent', 'ld.late')->get();
        $transaction = Transaction::find($transaction_id);
        return view('loansPrint.print-dailyMoneyCash', compact('loan', 'transaction', 'loanDayAgent'));
    }

    public function update_payments_period(Request $request)
    {
        DB::beginTransaction();
        try {
            $loan = Loan::with(['loanDay'])
                ->where('id', $request->id)
                ->first();
            $loan->payments_period_id = $request->payments_period_id ?? null;

            if ($request->payments_period_id) {
                $payment_period = PaymentsPeriod::find($request->payments_period_id);
                if (!$payment_period->days_quantity) {
                    return redirect()
                        ->route('loans.index')
                        ->with(['message' => 'El periodo seleccionado mal registrado', 'alert-type' => 'error']);
                }
                $cont = 1;
                $count_days = $loan->loanDay->count();
                $loan->loanDay->each(function ($day) use ($payment_period, &$cont, $count_days) {
                    if ($cont % $payment_period->days_quantity == 0 || $cont == $count_days) {
                        $day->payment_day = 1;
                    } else {
                        $day->payment_day = 0;
                    }
                    $day->update();
                    $cont++;
                });
            } else {
                $loan->loanDay->each(function ($day) {
                    $day->payment_day = 1;
                    $day->update();
                });
            }

            $loan->update();

            DB::commit();
            return redirect()
                ->route('loans.index')
                ->with(['message' => 'Periodo de pago actualizado', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()
                ->route('loans.index')
                ->with(['message' => 'Ocurrió un error', 'alert-type' => 'error']);
        }
    }

    public function notificationAutomatic($loan)
    {
        // return $loan;
        $loan = Loan::where('id', $loan)->first();
        $loan->update([
            'notification' => $loan->notification == 'si' ? 'no' : 'si',
        ]);
        return redirect()
            ->route('loans.index')
            ->with(['message' => 'Notificación actualizada', 'alert-type' => 'success']);
    }


    // Para enviar de forma manual el comprobante
    public function transactionWhatsapp($loan, $transaction)
    {
        // return $loan;
        $loan = Loan::with(['people'])
            ->where('id', $loan)
            ->first();
        $transaction = Transaction::find($transaction);

        $url = route('loans.payment.notification', $transaction->id);

        $servidor = setting('servidores.whatsapp');
        $session = setting('servidores.whatsapp-session');

        if ($loan->people->cell_phone && is_numeric($loan->people->cell_phone) && $servidor && $session)
        {
            $this->whatsapp($servidor, $session, '591', $loan->people->cell_phone, $url, 'Manual - Comprobante de pago', $loan->people->first_name);
            return redirect()
                ->route('loans-list.transaction', ['loan' => $loan->id])
                ->with(['message' => 'Whatsapp enviado exitosamente.', 'alert-type' => 'success']);
        }
        else
        {
            return redirect()
                ->route('loans-list.transaction', ['loan' => $loan->id])
                ->with(['message' => 'Whatsapp no enviado.', 'alert-type' => 'error']);
        }
    }



    // Meotodo Para envio de mensaje 
    public function whatsapp($servidor, $session, $code, $phone, $url, $type, $name = null)
    {
        Log::channel('whatsappJob')->info("Whatsapp: Iniciando programación de mensajes para {$phone}. Tipo: {$type}");

        // --- 1. MENSAJE DE SALUDO (Aviso de envío) ---
        $nameStr = $name ? " ".ucfirst(strtolower($name)) : "";
        $greetings = [
            "Hola{$nameStr} 👋", 
            "Saludos{$nameStr} ✨", 
            "Estimado cliente{$nameStr} 🤝", 
            "Buen día{$nameStr} ☀️", 
            "Hola{$nameStr}, ¿cómo estás? 😊", 
            "Buenas{$nameStr} 🙋‍♂️",
            "¡Hola{$nameStr}! Espero que estés bien 🌟",
            "Un gusto saludarte{$nameStr} 👋",
            "¡Qué tal{$nameStr}! 😃",
            "Hola{$nameStr}, espero que tengas un excelente día ☀️",
            "¡Buenas! {$nameStr} 👋",
            "Saludos cordiales{$nameStr}",
            "¡Hola! {$nameStr}, un placer saludarte 😊",
            "¡Hola{$nameStr}! Qué bueno saludarte 👋",
            "Espero que te encuentres muy bien, {$nameStr} ✨",
            "¡Buenas buenas{$nameStr}! 😃",
            "Hola{$nameStr}, paso por aquí para saludarte 👋",
            "¡Qué tal todo{$nameStr}! Espero que genial 🌟",
            "Un cordial saludo para ti, {$nameStr} 🤝",
            "¡Hola hola{$nameStr}! 😊",
            "Es un gusto escribirte, {$nameStr} ✨",
            "¡Saludos{$nameStr}! Espero que tu día vaya excelente ☀️",
            "Hola{$nameStr}, deseándote lo mejor hoy 🙌",
            "¡Buenas{$nameStr}! Espero que todo marche bien 👍",
            "¡Hola{$nameStr}! Aquí reportándome 👋",
            "Saludos{$nameStr}, espero no interrumpir 🤝",
            "¡Hola{$nameStr}! Qué alegría saludarte hoy 😊",
            "¡Buenas vibras para ti{$nameStr}! ✨",
            "Hola{$nameStr}, espero que estés teniendo un gran día 🌟",
            "¡Qué tal{$nameStr}! Un saludo especial 👋",
            "Hola{$nameStr}, un gusto contactarte nuevamente 😃",
            "¡Saludos{$nameStr}! Espero que estés de maravilla ✨",
            "Hola{$nameStr}, te envío un cordial saludo 🤝",
            "¡Buenas{$nameStr}! Qué gusto saludarte por aquí 👋",
            "Hola{$nameStr}, espero que todo esté en orden 👍",
            "¡Hola{$nameStr}! Te deseo un feliz día ☀️",
            "Saludos{$nameStr}, espero que te encuentres bien 😊",
            "¡Hola{$nameStr}! Un placer saludarte como siempre ✨",
            "¡Buenas{$nameStr}! Espero que estés disfrutando tu día 🌟",
            "Hola{$nameStr}, paso a dejarte un saludo 👋",
            "¡Qué tal{$nameStr}! Espero que todo vaya excelente 😃",
            "Saludos{$nameStr}, un gusto saludarte hoy 🤝",
            "¡Hola{$nameStr}! Espero que estés teniendo una jornada productiva 🚀",
            "¡Buenas{$nameStr}! Te mando un saludo 👋",
            "Hola{$nameStr}, espero que estés muy bien hoy ✨",
            "¡Hola{$nameStr}! Qué bueno poder saludarte 😊",
            "Saludos{$nameStr}, espero que tengas un día genial 🌟",
            "¡Hola{$nameStr}! Un gusto saludarte nuevamente 👋",
            "¡Buenas{$nameStr}! Espero que todo esté perfecto 👍",
            "Hola{$nameStr}, te deseo mucho éxito hoy 🍀"
        ];
        $preludes = [
            "En unos momentos le enviamos su comprobante de pago ⏳.",
            "Ya estamos procesando su comprobante, se lo envío en breve 📨.",
            "Deme unos minutos y le paso su recibo 🕐.",
            "Su pago fue registrado, enseguida le adjunto el comprobante ✅.",
            "Estamos generando su recibo, aguarde un instante por favor 🔄.",
            "Confirmando su transacción, en breve recibe el comprobante 🧾.",
            "Procesando su pago... un momento por favor ⚙️.",
            "Todo listo con su pago, ya le paso el comprobante 👍.",
            "Estoy preparando su comprobante, un momento por favor 📄.",
            "Su pago ha sido validado, en breve le envío el recibo ✅.",
            "Generando comprobante de pago... ⏳",
            "Enseguida le comparto la constancia de su pago 📨.",
            "Unos segundos y le envío su comprobante 👍.",
            "Su transacción ha sido exitosa, en breve le envío el comprobante 🌟.",
            "Estamos finalizando el registro de su pago, aguarde un momento 🕒.",
            "Recibo en proceso, se lo comparto en unos instantes 📤.",
            "Validando datos del pago, enseguida le paso el comprobante 🔍.",
            "Pago recibido correctamente, ya le envío su constancia ✨.",
            "Un momento mientras genero su recibo digital 💻.",
            "Su pago está siendo procesado, en breve tendrá su comprobante 🚀.",
            "Confirmado, en unos segundos le llega su recibo ✅.",
            "Estamos preparando su documento de pago, gracias por esperar 🙏.",
            "Transacción aprobada, ya le envío el detalle 📝.",
            "Su pago se ha registrado con éxito, en breve le paso el comprobante 💫.",
            "Estamos verificando su transacción, un momento por favor 🧐.",
            "Todo correcto, enseguida le envío su recibo digital 📲.",
            "Procesando... en unos instantes tendrá su comprobante ⏳.",
            "Gracias por su pago, ya le estoy generando el recibo 📃.",
            "Unos instantes y le comparto la imagen del comprobante 🖼️.",
            "Pago validado, procedo a enviarle su constancia ✅.",
            "Estamos listos, en breve recibe su comprobante de pago 📨.",
            "Su operación fue exitosa, aguarde un momento para el recibo 👍.",
            "Generando su constancia de pago, por favor espere 🕒."
        ];
        $msg1 = $greetings[array_rand($greetings)] . " " . $preludes[array_rand($preludes)];
        
        // --- 2. MENSAJE DEL COMPROBANTE (Con la imagen) ---
        $receiptTexts = [
            "Aquí tiene su comprobante 👇",
            "Le adjunto el recibo de su pago 📄:",
            "Listo, aquí está su comprobante ✅:",
            "Comprobante generado exitosamente ✨:",
            "Su recibo digital 📱:",
            "Adjunto encontrará el detalle de su pago 📎:",
            "Aquí está la constancia de su operación 🧾:",
            "Le comparto su comprobante de pago 📩:",
            "Aquí tiene el detalle de su transacción 🧾:",
            "Comprobante listo 👇:",
            "Su constancia de pago digital ✅:",
            "Adjunto el recibo correspondiente 📎:",
            "Su comprobante ha sido generado correctamente 📄:",
            "Aquí le envío la constancia de su pago 📨:",
            "Recibo de pago listo para descargar 📥:",
            "Confirmación de pago adjunta ✅:",
            "Detalle de la transacción completada 📝:",
            "Su documento de pago está listo 👍:",
            "Le paso la imagen de su comprobante 🖼️:",
            "Transacción exitosa, aquí su recibo 🧾:",
            "Comprobante de operación bancaria 👇:",
            "Ya puede ver su recibo digital aquí 📲:",
            "Comprobante de pago generado exitosamente 📄:",
            "Aquí tiene el comprobante de su transacción 👇:",
            "Le envío el detalle de su pago realizado ✅:",
            "Su recibo ha sido emitido correctamente 🧾:",
            "Adjunto la constancia de su pago 📎:",
            "Comprobante listo para su revisión 🧐:",
            "Aquí está su comprobante digital 📱:",
            "Le comparto la imagen de su recibo 🖼️:",
            "Transacción completada, aquí su comprobante 👍:",
            "Su pago ha sido procesado, aquí el recibo 📨:",
            "Su comprobante de pago está disponible aquí 👇:",
            "Hemos generado su recibo con éxito ✅:",
            "Adjunto el comprobante de su abono 📎:",
            "Aquí tiene la prueba de su pago realizado 🧾:",
            "Su transacción ha sido registrada, aquí el comprobante 📝:",
            "Le envío el soporte de su pago 📨:",
            "Comprobante de pago listo para usted 👍:",
            "Aquí está el detalle de su abono 📄:",
            "Su recibo de pago ha sido emitido 📤:",
            "Confirmación de su transacción adjunta 👇:",
            "Le hago entrega de su comprobante digital 📱:",
            "Aquí tiene su constancia de pago actualizada ✅:",
            "Recibo generado, puede verlo aquí 🖼️:",
            "Su pago se procesó correctamente, adjunto recibo 📎:",
            "Detalle del pago realizado exitosamente 🧾:",
            "Aquí le dejo su comprobante de operación 📩:",
            "Su constancia de transacción está lista ✨:",
            "Le comparto el recibo de su última operación 📄:",
            "Comprobante de pago emitido correctamente 👍:",
            "Aquí tiene el respaldo de su transacción 📥:",
            "Su recibo ya está disponible para descarga 👇:",
            "Adjunto la imagen con los detalles del pago 🖼️:",
            "Transacción finalizada, aquí su comprobante ✅:",
            "Le envío la confirmación de su pago 📨:",
            "Su documento de transacción ha sido creado 📝:"
        ];
        $msg2 = $receiptTexts[array_rand($receiptTexts)];

        // --- 3. MENSAJE DE AGRADECIMIENTO (Cierre) ---
        $thanks = [
            "¡Gracias por su preferencia! 🙏",
            "Agradecemos su confianza en nosotros 🤝.",
            "Gracias por ser parte de nuestra comunidad 🌟.",
            "Su operación se realizó con éxito ✅.",
            "Pago registrado correctamente 👍.",
            "¡Muchas gracias por su pago! 😊",
            "Valoramos mucho su puntualidad 👏.",
            "¡Gracias por cumplir con su pago! 🙌",
            "Agradecemos su puntualidad y compromiso 🤝.",
            "¡Excelente! Gracias por su pago 😊.",
            "Su pago nos ayuda a seguir creciendo juntos 🌟.",
            "¡Muchas gracias! Valoramos su preferencia 🙏."
        ];
        $closings = [
            "Atentamente, el equipo.", 
            "Cualquier duda, estamos aquí 📞.", 
            "Nos vemos pronto 👋.", 
            "Que tenga buen resto de jornada 🌤️.", 
            "Gracias por su tiempo ⏳.",
            "¡Que tenga un excelente día! 🌈",
            "Estamos a su disposición 🫡.",
            "Quedamos atentos a cualquier consulta 📞.",
            "¡Hasta la próxima! 👋",
            "Que tenga un día productivo 🚀.",
            "Saludos de parte de todo el equipo 🏢.",
            "Cualquier cosa, no dude en escribirnos 📩."
        ];
        $emojis = ['✅', '👍', '😊', '👋', '✨', '🤝', '🌟', '🎉', '💯'];
        
        $msg3 = $thanks[array_rand($thanks)] . " " . $emojis[array_rand($emojis)] . "\n" . $closings[array_rand($closings)];

        // --- GESTIÓN DE TIEMPOS (Cache para cola secuencial) ---
        
        // Recuperar la última hora programada globalmente
        $lastScheduled = Cache::get('last_whatsapp_schedule');
        $lastScheduled = $lastScheduled ? Carbon::parse($lastScheduled) : now();
        Log::channel('whatsappJob')->info("Whatsapp: Última hora en cache: {$lastScheduled}");

        // Asegurar que trabajamos con el día actual (evitar colas de días anteriores o futuros)
        if (!$lastScheduled->isSameDay(now())) {
            $lastScheduled = now();
            Log::channel('whatsappJob')->info("Whatsapp: Fecha cache distinta a hoy. Reiniciando a NOW.");
        }

        // Si la última hora programada ya pasó, reiniciamos a 'ahora'
        if ($lastScheduled < now()) { 
            $lastScheduled = now(); 
            Log::channel('whatsappJob')->info("Whatsapp: Reiniciando hora base a NOW.");
        }

        // RESTRICCIÓN DE HORARIO (08:00 - 23:30)
        // Si es antes de las 08:00, programar para las 08:00 del mismo día
        if ($lastScheduled->format('H:i') < '08:00') {
            $lastScheduled->setTime(8, 0, 0);
            Log::channel('whatsappJob')->info("Whatsapp: Hora ajustada al inicio de jornada (08:00).");
        }

        // Si la hora base supera las 23:30, omitimos el envío para evitar bloqueos
        if ($lastScheduled->format('H:i') > '23:30') {
            Log::channel('whatsappJob')->info("Whatsapp: Hora límite (23:30) excedida. Se omitió el envío a {$phone}.");
            return;
        }

        // Calculamos el tiempo tentativo (cola secuencial + delay aleatorio)
        $sendAt1 = $lastScheduled->copy()->addMinutes(rand(2, 10));

        // --- ENVÍO 1: Saludo ---
        Cache::put('last_whatsapp_schedule', $sendAt1, now()->addDay());
        WhatsappJob::dispatch($servidor, $session, $code, $phone, null, $msg1, $type)->delay($sendAt1);

        // --- ENVÍO 2: Comprobante (2-5 min después del saludo) ---
        $sendAt2 = $sendAt1->copy()->addMinutes(rand(1, 2));
        Cache::put('last_whatsapp_schedule', $sendAt2, now()->addDay());
        WhatsappJob::dispatch($servidor, $session, $code, $phone, $url, $msg2, $type)->delay($sendAt2);

        // --- ENVÍO 3: Agradecimiento (1-3 min después del comprobante) ---
        $sendAt3 = null;
        if (rand(0, 1)) {
            $sendAt3 = $sendAt2->copy()->addMinutes(rand(1, 2));
            Cache::put('last_whatsapp_schedule', $sendAt3, now()->addDay());
            WhatsappJob::dispatch($servidor, $session, $code, $phone, null, $msg3, $type)->delay($sendAt3);
        }

        // --- LOG UNIFICADO Y ESTRUCTURADO ---
        $clientInfo = $name ? "{$name} ({$phone})" : $phone;
        $border = str_repeat('=', 60);
        $logMessage = "\n" .
            $border . "\n" .
            " [ WHATSAPP JOB PROGRAMADO ]\n" .
            str_repeat('-', 60) . "\n" .
            " Cliente: " . $clientInfo . "\n" .
            " Tipo:    " . $type . "\n" .
            str_repeat('-', 60) . "\n" .
            " 1. Saludo:         " . $sendAt1->format('Y-m-d H:i:s') . "\n" .
            " 2. Comprobante:    " . $sendAt2->format('Y-m-d H:i:s') . "\n" .
            " 3. Agradecimiento: " . ($sendAt3 ? $sendAt3->format('Y-m-d H:i:s') : 'OMITIDO') . "\n" .
            $border;
        Log::channel('whatsappJob')->info($logMessage);
    }

    public function updatePersonPhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'person_id' => 'required|exists:people,id',
            'phone' => 'required|numeric|digits_between:7,8',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Datos inválidos.', 'errors' => $validator->errors()], 422);
        }

        try {
            $person = People::findOrFail($request->person_id);
            $person->cell_phone = $request->phone;
            $person->save();

            return response()->json(['success' => true, 'message' => 'Teléfono actualizado exitosamente.']);

        } catch (\Exception $e) {
            Log::error('Error al actualizar teléfono de persona: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Ocurrió un error en el servidor.'], 500);
        }
    }
}
