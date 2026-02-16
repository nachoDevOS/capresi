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
use App\Models\LoanRequirement;
use App\Models\User;
use Psy\CodeCleaner\ReturnTypePass;
use Psy\TabCompletion\Matcher\FunctionsMatcher;
use TCG\Voyager\Models\Role;
use App\Models\Route;
use Illuminate\Support\Facades\Http;
use App\Models\Transaction;
use Illuminate\Support\Facades\Cache;

use App\Http\Controllers\FileController;
use App\Models\LoanDayAgent;
use Illuminate\Support\Composer;
use PhpParser\Node\Stmt\TryCatch;
use App\Models\Cashier;
use App\Models\CashierMovement;
use PhpParser\Node\Stmt\Return_;
use PHPUnit\Framework\MockObject\Stub\ReturnReference;
use ReturnTypeWillChange;

use function PHPSTORM_META\type;
use function PHPUnit\Framework\returnSelf;

// Models
use App\Models\PaymentsPeriod;

// Queues
use App\Jobs\SendRecipe;
use App\Jobs\WhatsappJob;

class LoanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $collector = User::with([
            'role' => function ($q) {
                $q->where('name', 'cobrador');
            },
        ])->get();
        return view('loans.browse', compact('collector'));
    }

    public function list($type, $search = null)
    {
        $user = Auth::user();
        $paginate = request('paginate') ?? 10;

        $status = null;
        $type ? ($status = "status = '$type'") : 1;
        $type == 'pagado' ? ($status = 'debt = 0') : 1;

        $data = Loan::with(['loanDay', 'loanRoute', 'loanRequirement', 'people', 'manager', 'agentDelivered'])
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

    public function createDaily($id)
    {
        $requirement = LoanRequirement::where('loan_id', $id)->first();

        $ok = LoanRequirement::where('loan_id', $id)->where('ci', '!=', null)->where('luz', '!=', null)->where('croquis', '!=', null)->where('business', '!=', null)->select('*')->first();
        return view('requirement.daily.add', compact('requirement', 'ok'));
    }

    public function storeRequirement(Request $request, $loan)
    {
        DB::beginTransaction();
        try {
            $imageObj = new FileController();
            $ok = LoanRequirement::where('loan_id', $loan)->first();
            $file = $request->file('ci');
            if ($file) {
                if ($file->getClientOriginalExtension() == 'pdf') {
                    $ci = $imageObj->file($file, $loan, 'Loan/requirement/daily/ci');
                    // return 0;
                } else {
                    $ci = $imageObj->image($file, $loan, 'Loan/requirement/daily/ci');
                    // return 1;
                }
                $ok->update(['ci' => $ci]);
            }

            $file = $request->file('luz');
            if ($file) {
                if ($file->getClientOriginalExtension() == 'pdf') {
                    $luz = $imageObj->file($file, $loan, 'Loan/requirement/daily/luz');
                } else {
                    $luz = $imageObj->image($file, $loan, 'Loan/requirement/daily/luz');
                }
                $ok->update(['luz' => $luz]);
            }

            $file = $request->file('croquis');
            if ($file) {
                if ($file->getClientOriginalExtension() == 'pdf') {
                    $croquis = $imageObj->file($file, $loan, 'Loan/requirement/daily/croquis');
                } else {
                    $croquis = $imageObj->image($file, $loan, 'Loan/requirement/daily/croquis');
                }

                $ok->update(['croquis' => $croquis]);
            }

            $file = $request->file('business');
            if ($file) {
                if ($file->getClientOriginalExtension() == 'pdf') {
                    $business = $imageObj->file($file, $loan, 'Loan/requirement/daily/business');
                } else {
                    $business = $imageObj->image($file, $loan, 'Loan/requirement/daily/business');
                }

                $ok->update(['business' => $business]);
            }

            if ($request->lat) {
                $ok->update(['latitude' => $request->lat]);
            }
            if ($request->lng) {
                $ok->update(['longitude' => $request->lng]);
            }

            DB::commit();
            return redirect()
                ->route('loans-requirement-daily.create', ['loan' => $loan])
                ->with(['message' => 'Requisitos registrado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $th;
            return redirect()
                ->route('loans-requirement-daily.create', ['loan' => $loan])
                ->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function deleteRequirement($loan, $col)
    {
        DB::beginTransaction();
        try {
            $requirement = LoanRequirement::where('loan_id', $loan)->first();

            if ($col == 0) {
                $requirement->update(['ci' => null]);
            }
            if ($col == 1) {
                $requirement->update(['luz' => null]);
            }
            if ($col == 2) {
                $requirement->update(['croquis' => null]);
            }
            if ($col == 3) {
                $requirement->update(['business' => null]);
            }
            DB::commit();
            return redirect()
                ->route('loans-requirement-daily.create', ['loan' => $loan])
                ->with(['message' => 'Requisitos eliminado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()
                ->route('loans-requirement-daily.create', ['loan' => $loan])
                ->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function successRequirement($loan)
    {
        DB::beginTransaction();
        try {
            Loan::where('id', $loan)->update(['inspector_userId' => Auth::user()->id, 'inspector_agentType' => Auth::user()->role->name, 'status' => 'verificado']);
            LoanRequirement::where('loan_id', $loan)->update(['status' => '1', 'success_userId' => Auth::user()->id, 'success_agentType' => Auth::user()->role->name]);

            DB::commit();
            return redirect()
                ->route('loans-requirement-daily.create', ['loan' => $loan])
                ->with(['message' => 'Requisitos aprobado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()
                ->route('loans-requirement-daily.create', ['loan' => $loan])
                ->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
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

            $loan->update(['code' => 'CP-' . str_pad($loan->id, 5, '0', STR_PAD_LEFT)]);
            LoanRoute::create([
                'loan_id' => $loan->id,
                'route_id' => $request->route_id,
                'observation' => 'Primer ruta',
                'register_userId' => Auth::user()->id,
                'register_agentType' => Auth::user()->role->name,
            ]);

            LoanRequirement::create([
                'loan_id' => $loan->id,
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
        $loan = Loan::with(['loanDay', 'loanRoute', 'loanRequirement', 'people', 'guarantor'])
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
            if (setting('servidores.whatsapp') && setting('servidores.whatsapp-session') && $loan->people->cell_phone) {
                Http::post(setting('servidores.whatsapp') . '/send?id=' . setting('servidores.whatsapp-session'), [
                    'phone' => '591' . $loan->people->cell_phone,
                    'text' => 'Hola *' . $loan->people->first_name . ' ' . $loan->people->last_name1 . ' ' . $loan->people->last_name2 . '* SU SOLICITUD DE PRESTAMO HA SIDO APROBADA EXITOSAMENTE. Pase por favor por las oficinas para entregarle su solicitud de prestamos, Gracias🤝',
                    'image_url' => '',
                ]);
            }

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

    // funcion para entregar dinero al beneficiario
    public function moneyDeliver(Request $request, $loan)
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
            // $user = Auth::user();

            // $date = date("d-m-Y",strtotime(date('y-m-d h:i:s')."+ 1 days"));

            // $date = Carbon::parse($request->fechass);
            // $date = date("Y-m-d", strtotime($date));
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

                        'register_userId' => Auth::user()->id,
                        'register_agentType' => Auth::user()->role->name,

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

                        'register_userId' => Auth::user()->id,
                        'register_agentType' => Auth::user()->role->name,

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
                // 'dateDelivered' => $request->dateDelivered,
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

    // function server_prinf($id)
    // {
    //     $loan = Loan::with(['people', 'agentDelivered', 'loanDay'])
    //         ->where('id', $id)
    //         ->first();

    //     $data = [
    //         'template' => 'voucherLoan', // Asegúrate de usar 'template' en lugar de 'templeate'
    //         'code' => $loan->code,
    //         'ci' => $loan->people->ci ? $loan->people->ci : 'Sin CI',
    //         'customer' => $loan->people->first_name . ' ' . $loan->people->last_name1 . ' ' . $loan->people->last_name2,
    //         'dateLoan' => Carbon::parse($loan->dateDelivered)->format('d/m/Y'),

    //         'register' => $loan->agentDelivered->name,
    //         'dateStart' => Carbon::parse($loan->loanDay->first()->date)->format('d/m/Y'),
    //         'dateFinish' => Carbon::parse($loan->loanDay->last()->date)->format('d/m/Y'),
    //         'amountLoan' => $loan->amountLoan,
    //         'amountPorcentage' => $loan->amountPorcentage,
    //         'amountTotal' => $loan->amountTotal,
    //     ];

    //     Http::withHeaders([
    //         'Content-Type' => 'application/json',
    //         'Accept' => 'application/json',
    //     ])->post(setting('servidores.prinf'), $data);
    // }

    

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
        $loan = Loan::with(['loanDay', 'loanRoute', 'loanRequirement', 'people', 'guarantor'])
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
                // WhatsappJob::dispatch($server, $session, $code, $phone, $message, $type)->delay(now()->addSeconds($this->whatsappDelay));
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
        // --- 1. MENSAJE DE SALUDO (Aviso de envío) ---
        $nameStr = $name ? " ".ucfirst(strtolower($name)) : "";
        $greetings = ["Hola{$nameStr}", "Saludos{$nameStr}", "Estimado cliente{$nameStr}", "Buen día{$nameStr}", "Hola{$nameStr}, ¿cómo estás?", "Buenas{$nameStr}"];
        $preludes = [
            "En unos momentos le enviamos su comprobante de pago.",
            "Ya estamos procesando su comprobante, se lo envío en breve.",
            "Deme unos minutos y le paso su recibo.",
            "Su pago fue registrado, enseguida le adjunto el comprobante.",
            "Estamos generando su recibo, aguarde un instante por favor."
        ];
        $msg1 = $greetings[array_rand($greetings)] . " " . $preludes[array_rand($preludes)];
        
        // --- 2. MENSAJE DEL COMPROBANTE (Con la imagen) ---
        $receiptTexts = [
            "Aquí tiene su comprobante 👇",
            "Le adjunto el recibo de su pago:",
            "Listo, aquí está su comprobante:",
            "Comprobante generado exitosamente:",
            "Su recibo digital:"
        ];
        $msg2 = $receiptTexts[array_rand($receiptTexts)];

        // --- 3. MENSAJE DE AGRADECIMIENTO (Cierre) ---
        $thanks = [
            "Gracias por su preferencia!",
            "Agradecemos su confianza en nosotros.",
            "Gracias por ser parte de nuestra comunidad.",
            "Su operación se realizó con éxito.",
            "Pago registrado correctamente."
        ];
        $closings = ["Atentamente, el equipo.", "Cualquier duda, estamos aquí.", "Nos vemos pronto.", "Que tenga buen resto de jornada.", "Gracias por su tiempo."];
        $emojis = ['✅', '👍', '😊', '👋', '✨', '🤝'];
        
        $msg3 = $thanks[array_rand($thanks)] . " " . $emojis[array_rand($emojis)] . "\n" . $closings[array_rand($closings)];

        // --- GESTIÓN DE TIEMPOS (Cache para cola secuencial) ---
        
        // Recuperar la última hora programada globalmente
        $lastScheduled = Cache::get('last_whatsapp_schedule');
        $lastScheduled = $lastScheduled ? Carbon::parse($lastScheduled) : now();

        // Si la última hora programada ya pasó, reiniciamos a 'ahora'
        if ($lastScheduled < now()) { $lastScheduled = now(); }

        // --- ENVÍO 1: Saludo (1-3 min después del último job global) ---
        $sendAt1 = $lastScheduled->copy()->addMinutes(rand(10, 25));
        Cache::put('last_whatsapp_schedule', $sendAt1, now()->addDay());
        WhatsappJob::dispatch($servidor, $session, $code, $phone, null, $msg1, $type)->delay($sendAt1);

        // --- ENVÍO 2: Comprobante (2-5 min después del saludo) ---
        $sendAt2 = $sendAt1->copy()->addMinutes(rand(3, 6));
        Cache::put('last_whatsapp_schedule', $sendAt2, now()->addDay());
        WhatsappJob::dispatch($servidor, $session, $code, $phone, $url, $msg2, $type)->delay($sendAt2);

        // --- ENVÍO 3: Agradecimiento (1-3 min después del comprobante) ---
        $sendAt3 = $sendAt2->copy()->addMinutes(rand(1, 3));
        Cache::put('last_whatsapp_schedule', $sendAt3, now()->addDay());
        WhatsappJob::dispatch($servidor, $session, $code, $phone, null, $msg3, $type)->delay($sendAt3);
    }
}
