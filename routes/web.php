<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AgentController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\PeopleController;
use App\Http\Controllers\VaultController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\AjaxController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\BonuController;
use App\Http\Controllers\CollectorController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\DevController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\DevelopmentController;
// use App\Http\Controllers\GarmentController;
use App\Http\Controllers\GpsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportCashierController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportManagerController;
use App\Http\Controllers\PawnController;
use App\Http\Controllers\ItemTypesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\EmployesController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\HourController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\LatePenaltyController;
use App\Http\Controllers\LoanRecoveryController;
use App\Http\Controllers\PaymentSheetController;
use App\Http\Controllers\ReportInventoryController;
use App\Http\Controllers\ReportLoanController;
use App\Http\Controllers\ReportSaleController;
use App\Http\Controllers\SalaryPurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ShiftsController;
use App\Http\Controllers\SpreadsheetController;
use App\Models\Contract;
use App\Models\Inventory;
use App\Models\PawnRegister;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', [TemplateController::class, 'index']);
// Route::get('message/{id?}/verification', [MessageController::class, 'verification']);

Route::get('/', function () {
    return redirect('admin/login');
})->name('login');


Route::post('/api/verificar-ubicacion', function (Request $request) {
    // Aquí podrías validar si está dentro de una zona permitida, por ejemplo
    return response()->json(['ok' => true]);
});

Route::get('/gpsBlockAccess', function () {
    return view('error.gpsBlockAccess');
});



//Ruta para poner el sistema en mantenimiento
Route::get('/development', [DevelopmentController::class , 'development'])->name('development');

// Ruta que renderiza el recibo de pago que se envía al usuario
Route::get('admin/pawn/payment/{id}/notification', [HomeController::class, 'payment_notification'])->name('pawn.payment.notification');
Route::get('admin/payment/transaction/{id}', [TransactionController::class, 'payment_notification'])->name('loans.payment.notification');

// Route::group(['prefix' => 'admin', 'middleware' => 'loggin'], function () {
Route::group(['prefix' => 'admin', 'middleware' => ['loggin', 'block.after.hours']], function () {

    Voyager::routes();

    // Route::get('/admin/session/extend', function () {
    //     session()->put('last_activity', time()); // Actualiza la hora de actividad de la sesión
    //     return response()->json(['message' => 'Sesión extendida']);
    // })->name('voyager.session.extend');

    Route::get('articles', [ArticleController::class, 'index'])->name('voyager.articles.index');
    Route::get('articles/ajax/list/{search?}', [ArticleController::class, 'list']);
    Route::get('articles/{article_id?}/developer', [ArticleController::class, 'developer'])->name('articles.developer');
    Route::post('articles/{article_id?}/developer/store', [ArticleController::class, 'developerStore'])->name('articles-developer.store');
    Route::delete('articles/{article_id?}/developer/{detail_id?}/destroy', [ArticleController::class, 'developerDestroy'])->name('articles-developer.destroy');
    Route::get('articles/developer/ajax/{article_id?}', [ArticleController::class, 'ajaxDeveloper'])->name('articles-developer.ajax');//para obtener las herramientas para genarrar los reqiisitos

    // Route::resources('people', PeopleController::class);
    Route::get('people', [PeopleController::class, 'index'])->name('voyager.people.index');
    Route::get('people/ajax/list/{search?}', [PeopleController::class, 'list']);
    Route::get('people/search/ajax', [PeopleController::class, 'ajaxPeople']);//Para obtener las persona en el select2
    Route::post('person/store', [PeopleController::class, 'peopleStore']); //Para registrar en un modal

    Route::post('people/store', [PeopleController::class, 'store'])->name('people.store');
    Route::get('people/{id?}/sponsor', [PeopleController::class, 'indexSponsor'])->name('people-sponsor.index');
    Route::post('people/{id?}/sponsor/store', [PeopleController::class, 'storeSponsor'])->name('people-sponsor.store');
    Route::delete('people/{people?}/sponsor/{sponsor?}/delete', [PeopleController::class, 'destroySponsor'])->name('people-sponsor.delete');
    Route::get('people/{people?}/sponsor/{sponsor?}/inhabilitar', [PeopleController::class, 'inhabilitarSponsor'])->name('people-sponsor.inhabilitar');
    Route::get('people/{people?}/sponsor/{sponsor?}/habilitar', [PeopleController::class, 'habilitarSponsor'])->name('people-sponsor.habilitar');

    Route::post('people/verification', [PeopleController::class, 'verification'])->name('people.verification');

    Route::post('people/importar', [PeopleController::class, 'import'])->name('people.import');

    

    // Contratos
    // Route::get('contracts', [ContractController::class, 'index'])->name('contracts.index');
    Route::resource('contracts', ContractController::class);
    Route::get('contracts/ajax/list/{type}/{search?}', [ContractController::class, 'list']);
    Route::get('contracts/{contract?}/success', [ContractController::class, 'successLoan'])->name('contracts.success');//Para aporobar el contrato
    Route::get('contracts/{contract?}/rechazar', [ContractController::class, 'rechazar'])->name('contracts.rechazar'); //para rechazar  los perestamos
    //Para los pagos que se dan en adelantos
    Route::post('contracts/advancement', [ContractController::class, 'storeAdvancement'])->name('contracts-advancement.store');
    Route::delete('contracts/advancement/{id?}/destroy', [ContractController::class, 'destroyAdvancement'])->name('contracts-advancement.destroy'); //para la destruccion de un adelanto

    Route::get('contracts/advancement/money/print/{contract_id}/{id?}', [ContractController::class, 'printAdvancementMoney']);//imprecion de adelantos 
    Route::post('contracts/contractDay/{contractDay_id?}/hour/save', [ContractController::class, 'saveContractDayHour'])->name('contracts-contractDay-hour.save');//Para poder hacer el cambio de horario de un dia en la vista del contato

    Route::post('contracts/licenseall/store', [ContractController::class, 'storeLicenseAll'])->name('contracts-license-all.store');
    Route::delete('contracts/licenseall/{licence?}/destroy', [ContractController::class, 'destroyLicenseAll'])->name('contracts-licenseAll.destroy'); //para la eliminacion de una licencia




    //Para planilla
    Route::resource('spreadsheets', SpreadsheetController::class);
    Route::get('spreadsheets/ajax/list/{type}/{search?}', [SpreadsheetController::class, 'list']);
    Route::get('spreadsheets/{spreadsheet?}/rechazar', [SpreadsheetController::class, 'rechazar'])->name('spreadsheets.rechazar'); //para rechazar la planilla
    Route::get('spreadsheets/{spreadsheet?}/success', [SpreadsheetController::class, 'generate'])->name('spreadsheets.generate');//Para generar la planilla
    Route::get('spreadsheets/{spreadsheet?}/print', [SpreadsheetController::class, 'printSpreadSheet'])->name('spreadsheets.print');//Para generar la planilla 
    //aguinaldo
    Route::resource('bonus', BonuController::class);
    Route::get('bonus/ajax/list/{type}/{search?}', [BonuController::class, 'list']);
    Route::get('bonus/{bonu?}/rechazar', [BonuController::class, 'rechazar'])->name('bonus.rechazar');
    Route::get('bonus/{bonu?}/success', [BonuController::class, 'generate'])->name('bonus.generate');
    Route::get('bonus/{bonu}/print', [BonuController::class, 'printBonuses'])->name('bonus.print');


    //Para la asistencia
    Route::resource('attendances', AttendanceController::class);
    Route::get('attendances/ajax/list/{search?}', [AttendanceController::class, 'list']);
    Route::post('attendances/import', [AttendanceController::class, 'import'])->name('attendances.import');//Para importar


    //Para el Horario 
    Route::resource('hours', HourController::class);
    Route::get('hours/ajax/list/{search?}', [HourController::class, 'list']);
    Route::delete('hour/{hour?}/delete', [HourController::class, 'destroyHour'])->name('hour.delete');


    //Para los Turnos
    Route::resource('shifts', ShiftsController::class);
    Route::get('shifts/ajax/list/{search?}', [ShiftsController::class, 'list']);
    Route::get('shifts/save/{id?}/{name?}/{description?}', [ShiftsController::class, 'nameStore'])->name('shifts.name');
    Route::post('shifts/hours/store', [ShiftsController::class, 'storeShiftsHour'])->name('shifts-hours.store');
    Route::delete('shifts/{shifts?}/hour/{shiftsHour?}/delete', [ShiftsController::class, 'destroyShiftsHour'])->name('shifts-hour.delete');
    Route::post('shifts/hours/save', [ShiftsController::class, 'save'])->name('shifts-hours.save');//Para guardar el tuno
    Route::get('shifts/{id?}/hour/decline', [ShiftsController::class, 'decline'])->name('shifts-hour.decline');//Para descartar el turno


    Route::get('late-penalties/create', [LatePenaltyController::class, 'create'])->name('voyager.late-penalties.create');
    Route::post('late-penalties/store', [LatePenaltyController::class, 'store'])->name('late-penalties.store');
    Route::get('late-penalties/{id}/edit', [LatePenaltyController::class, 'edit'])->name('voyager.late-penalties.edit');
    Route::put('late-penalties/{id}', [LatePenaltyController::class, 'update'])->name('late-penalties.update');


    Route::resource('paymentSheet', PaymentSheetController::class);
    Route::get('contractPeople/ajax', [PeopleController::class, 'contractPeople']);//Para obtener las personas que tienen contrato finalizados, activos
    Route::post('paymentSheet/list', [PaymentSheetController::class, 'list'])->name('paymentSheet.list');

    // Route::get('paymentSheet/contract/{contract?}/paymentcontract', [PaymentSheetController::class, 'paymentContract'])->name('paymentSheet-contract.paymentcontract');//Para pagar
    Route::get('paymentSheet/{type?}/save/{id?}', [PaymentSheetController::class, 'save_spreadsheet']);//para pagar imprimir el comprobante de pago del empleado
    Route::get('paymentSheet/print/{types}/{id?}', [PaymentSheetController::class, 'print_payment']);//para pagar imprimir el comprobante de pago del empleado




    // Route::post('print/dailyList/list', [ReportCashierController::class, 'dailyListList'])->name('print-dailyList.list');



    // Route::get('contracts/{loan?}/success', [ContractController::class, 'successLoan'])->name('contracts.success');//Para aporobar el contrato

    Route::resource('loans', LoanController::class);
    Route::get('loans/ajax/list/{type?}/{search?}', [LoanController::class, 'list']);
    Route::post('loans/update/payments-period', [LoanController::class, 'update_payments_period'])->name('loans.update.payments-period');
    Route::get('loans/people/ajax', [PeopleController::class, 'ajaxPeople']);//para obtener las personas o clientes para darles u prestamos

    Route::get('loans/{loan}/decline', [LoanController::class, 'declineLoan'])->name('loans.decline'); //para rechazar  los perestamos
    // Route::delete('loans/{loan?}/cashierclose/destroy', [LoanController::class, 'destroyLoan'])->name('loans-cashierclose.destroy'); //para la destruccion de prestamo con caja cerrada
    // Route::get('loans/ajax/notPeople/{id?}', [LoanController::class, 'ajaxNotPeople'])->name('loans-ajax.notpeople');
    Route::get('loans/{loan}/list/transaction', [TransactionController::class, 'listTransaction'])->name('loans-list.transaction');
    Route::get('loans/{loan?}/print/calendar', [LoanController::class, 'printCalendar'])->name('loans-print.calendar');
    Route::get('loans/{loan?}/requirement/daily/create', [LoanController::class, 'createDaily'])->name('loans-requirement-daily.create');
    Route::post('loans/{loan?}/requirement/daily/store', [LoanController::class, 'storeRequirement'])->name('loans-requirement-daily.store');
    Route::get('loans/daily/{loan?}/requirement/delete/{col?}', [LoanController::class, 'deleteRequirement'])->name('loans-daily-requirement.delete');
    Route::get('loans/daily/{loan?}/requirement/success', [LoanController::class, 'successRequirement'])->name('loans-daily-requirement.success');
    Route::post('loans/{loan?}/money/deliver', [LoanController::class, 'moneyDeliver'])->name('loans-money.deliver');
    Route::get('loans/contract/daily/{loan?}', [LoanController::class, 'printContracDaily']);
    Route::get('loans/{loan}/approve', [LoanController::class, 'approveLoan'])->name('loans.approve'); //Para aprobar el prestamos para que pueda ser entregado al cliente
    Route::post('loans/{loan?}/agent/update', [LoanController::class, 'updateAgent'])->name('loans-agent.update');
    Route::get('loans/{loan?}/notification', [LoanController::class, 'notificationAutomatic'])->name('notificationAutomatic');

    Route::get('loans/{loan}/daily/money', [LoanController::class, 'dailyMoney'])->name('loans-daily.money');//para abrir la ventana de abonar dinero a un prestamo
    Route::post('loans/daily/money/store', [LoanController::class, 'dailyMoneyStore'])->name('loans-daily-money.store');
    Route::get('loans/comprobante/print/{loan_id?}', [LoanController::class, 'printLoanComprobante']);//para imprimir el comprobante de prestamo al entregar el prestamo al cliente
    Route::get('loans/daily/money/print/{loan_id}/{transaction_id}', [LoanController::class, 'printDailyMoney']);//impresionde de pago diario de cada cuota pagada mediante los cajeros de las oficinas

    // PARA CAMBIOS DE RUTAS DE LOS PRESTAMOS DIARIOS Y ESPECIALES
    Route::get('loans/{loan?}/routeOld', [RouteController::class, 'loanRouteOld'])->name('loan-routeOld.index');
    Route::post('loans/{loan?}/route/store', [RouteController::class, 'updateRouteLoan'])->name('loan-route.store');

    // Funcional de transacciones

    Route::get('loans/{loan}/transaction/{transaction}/whatsapp', [LoanController::class, 'transactionWhatsapp'])->name('loans-transaction.whatsapp');




    Route::resource('loanRecoveries', LoanRecoveryController::class);
    Route::get('loanRecoveries/ajax/list/{search?}', [LoanRecoveryController::class, 'list']);
    Route::get('loanRecoveries/list/print', [LoanRecoveryController::class, 'listPrint'])->name('loanRecoveries-list.print');





    // Route::post('loans/routeOld', [RouteController::class, 'loanRouteOld'])->name('loan-routeOld.index');

    Route::resource('agents', AgentController::class);
    Route::get('agents', [AgentController::class, 'index'])->name('voyager.agents.index');
    Route::get('agents/ajax/list/{search?}', [AgentController::class, 'list']);
    Route::post('agents/store', [AgentController::class, 'store'])->name('agents.store');
    // Route::delete('agents/destroy/{id}', [AgentController::class, 'destroy'])->name('voyager.agents.destroy');

    Route::get('routes', [RouteController::class, 'index'])->name('voyager.routes.index');
    Route::get('routes/ajax/list/{search?}', [RouteController::class, 'list']);

    Route::get('routes/{route?}/collector', [RouteController::class, 'indexCollector'])->name('routes.collector.index');
    Route::get('routes/collector/ajax/list/{id?}/{search?}', [RouteController::class, 'listCollector']);
    Route::post('routes/{route?}/collector/store', [RouteController::class, 'storeCollector'])->name('routes.collector.store');

    Route::get('routes/{route?}/collector/{collector?}/inhabilitar', [RouteController::class, 'inhabilitarCollector'])->name('routes.collector.inhabilitar');
    Route::get('routes/{route?}/collector/{collector?}/habilitar', [RouteController::class, 'habilitarCollector'])->name('routes.collector.habilitar');
    Route::delete('routes/{route?}/collector/{collector?}/delete', [RouteController::class, 'deleteCollector'])->name('routes.collector.delete');

    // para mostrar los prestamos de rutas y para intercambia de rutas
    Route::get('routes/loan/exchange', [RouteController::class, 'indexExchange'])->name('routes-loan-exchange.index');
    Route::post('routes/loan/exchange/search', [RouteController::class, 'listLoan'])->name('routes-loan-exchange.search');
    Route::post('routes/loan/exchange/transfer', [RouteController::class, 'storeExchangeLoan'])->name('routes-loan-exchange.transfer');

    Route::resource('collectors', CollectorController::class);
    Route::get('collectors/ajax/list/{search?}', [PeopleController::class, 'list']);
    
    // ##################################################################################################################################
    // ###########################################################    PRENDARIO    ##########################################################
    // ##################################################################################################################################

    // Route::resource('garments', GarmentController::class);
    // Route::get('garments/category/ajax', [GarmentController::class, 'ajaxCategory']);//para obtener los articulo para realizar la prenda
    Route::get('garments/model/ajax', [ArticleController::class, 'ajaxModel'])->name('garments-model.ajax');//para obtener los articulo para realizar la prenda
    Route::get('garments/marca/ajax', [ArticleController::class, 'ajaxMarca'])->name('garments-marca.ajax');//para obtener los articulo para realizar la prenda
    // Route::get('garments/quilate/ajax/{id?}', [GarmentController::class, 'ajaxQuilate'])->name('garments-quilate.ajax');//para obtener los articulo para realizar la prenda
    // Route::get('garments/ajax/list/{cashier_id}/{type}/{search?}', [GarmentController::class, 'list']);//Para generar la lista en el index
    // Route::get('garments/{garment?}/rechazar', [GarmentController::class, 'rechazar'])->name('garments.rechazar'); //para rechazar  los prendfarios ante de prestar
    // Route::get('garments/{garment?}/success', [GarmentController::class, 'successLoan'])->name('garments.success');
    // Route::post('garments/{garment?}/money/deliver', [GarmentController::class, 'moneyDeliver'])->name('garments-money.deliver');
    // Route::get('garments/contract/daily/{garment?}', [GarmentController::class, 'printContracDaily']);//Para imprimir contrato privado
    // Route::post('garments/payment/month/{month?}/add', [GarmentController::class, 'paymentMonth'])->name('garments-payment-month.add');//Para abonar o pagar cada mes
    // Route::post('garments/payment/month/{garment_id?}/add/all', [GarmentController::class, 'paymentMonthAll'])->name('garments-payment-month-add.all');//Para extraer todos los meses que se deben mas el total que se pagara
    // Route::get('garments/list/month/debt/all/{garment_id?}', [GarmentController::class, 'ajaxListMonthDebt'])->name('garments-list-month-debt.all');//Para recoger la prenda y pagar los meses
    // Route::get('garments/voucher/print/{garment_id?}', [GarmentController::class, 'printLoanVoucher']);//para imprimir el comprobante de prestamo al entregar el prestamo al cliente
    // Route::get('garments/tickets/print/{garment_id?}', [GarmentController::class, 'printGarmentTickets']);//para imprimir el comprobante de prestamo al entregar el prestamo al cliente
    // Route::get('garments/payment/money/print/{garment_id}/{transaction_id?}', [GarmentController::class, 'printDailyMoney']);//imprimir los meses diarios de pago de la premda

    Route::post('item_types/store', [ItemTypesController::class, 'store'])->name('item_types.store');
    
    Route::resource('pawn', PawnController::class);
    Route::get('pawn/list/ajax', [PawnController::class, 'list'])->name('pawn.list');
    Route::get('pawn/{pawn?}/rechazar', [PawnController::class, 'rechazar'])->name('pawn.rechazar'); //para rechazar  los perestamos
    Route::get('pawn/{pawn?}/success', [PawnController::class, 'successPawn'])->name('pawn.success');
    Route::post('pawn/{pawn}/code', [PawnController::class, 'codePawn'])->name('pawn.code');//Para codigo manuales
    // Route::delete('pawn/{pawn}/delete', [PawnController::class, 'destroyAux'])->name('pawn.delete');//Para codigo manuales
    Route::post('pawn/payment', [PawnController::class, 'pawnPyment'])->name('pawn.payment'); //Para pagar interes y recojo de las prendas
    Route::post('pawn/{id}/amountAditional', [PawnController::class, 'amountAditional'])->name('pawn.amountAditional'); //Para aumentar dinero al clinte por su prenda 
    Route::post('pawn/{pawn?}/money/deliver', [PawnController::class, 'moneyDeliver'])->name('pawn-money.deliver');
    Route::get('pawn/transaction/print/{pawn_id}/{transaction_id?}', [PawnController::class, 'printTransaction']);//imprecione de pago

    Route::get('pawn/voucher/{id}/print', [PawnController::class, 'printVoucher'])->name('pawn-voucher.print');
    Route::get('pawn/general/{id}/print', [PawnController::class, 'print'])->name('pawn.print');
    Route::get('pawn/vehicular/{id}/print', [PawnController::class, 'printV'])->name('pawn-vehicular.print');


    //Para inventario
    Route::post('pawn/{id}/inventory/store', [InventoryController::class, 'storePawn'])->name('pawn-inventory.store');
    Route::resource('inventories', InventoryController::class);
    Route::get('inventories/list/ajax', [InventoryController::class, 'list'])->name('inventories.list');
    Route::post('inventories/{id}/price/update', [InventoryController::class, 'updatePrice'])->name('inventories-price.update');
    Route::get('inventories/item/ajax', [InventoryController::class, 'listItem']);



    //Para la ventas de los productos en inventario
    Route::resource('sales', SaleController::class);
    Route::get('sales/list/ajax', [SaleController::class, 'list'])->name('sales.list');
    Route::get('sales/transaction/print/{id}/{transaction_id}', [SaleController::class, 'printTransaction']);//imprecion de pago
    Route::get('sales/{id}/prinf', [SaleController::class, 'prinf'])->name('sales.prinf');

    Route::post('sales/{id}/payment', [SaleController::class, 'salePyment'])->name('sales.payment'); //Para pagar ventas


    // Compra de sueldo

    Route::resource('salary-purchases', SalaryPurchaseController::class);
    Route::get('salary-purchases/list/ajax', [SalaryPurchaseController::class, 'list'])->name('salaryPurchase.list');
    Route::get('salary-purchases/{salaryPuchase}/approve', [SalaryPurchaseController::class, 'approveSalaryPuchase'])->name('salaryPurchases.approve'); //Para aprobar el prestamos para que pueda ser entregado al cliente
    Route::post('salaryPurchases/{salaryPuchase}/deliver', [SalaryPurchaseController::class, 'moneyDeliver'])->name('salaryPurchases.deliver');
    Route::post('salaryPurchases/payment', [SalaryPurchaseController::class, 'salaryPurchasePyment'])->name('salaryPurchases.payment'); //Para pagar interes o devolucion del dinero
    Route::get('salaryPurchases/transaction/print/{salary_id}/{transaction_id?}', [SalaryPurchaseController::class, 'printTransaction']);//imprecione de pago








    // Route::get('inventories', [::class, 'index'])->name('inventories.index');
    // Route::get('people/ajax/list/{search?}', [PeopleController::class, 'list']);





    // ##################################################################################################################################
    // ###########################################################       FIN       #####################################################
    // ##################################################################################################################################

    Route::resource('vaults', VaultController::class);

    Route::post('vaults/{id}/details/store', [VaultController::class, 'details_store'])->name('vaults.details.store');//***para agregar ingreso y egreso a la boveda
    Route::post('vaults/{id}/open', [VaultController::class, 'open'])->name('vaults.open');
    Route::get('vaults/{id}/close', [VaultController::class, 'close'])->name('vaults.close');
    Route::post('vaults/{id}/close/store', [VaultController::class, 'close_store'])->name('vaults.close.store');//***Para guardar cuando se cierre de boveda
    Route::get('vaults/{vault}/print/status', [VaultController::class, 'print_status'])->name('vaults.print.status');//***

    Route::resource('cashiers', CashierController::class);
    Route::get('cashiers/list/ajax', [CashierController::class, 'list'])->name('cashiers.list');
    Route::get('cashiers/{cashier}/amount', [CashierController::class, 'amount'])->name('cashiers.amount');//para abrir la vista de poder agregar dinero o aboinar mas dinero a la caja
    Route::post('cashiers/amount/store', [CashierController::class, 'amount_store'])->name('cashiers.amount.store');//para guardar el monto adicional de abonar dinero a la caja cuando este abierta
    Route::post('cashiers/amount/transfer/store', [CashierController::class, 'amountTransferStore'])->name('cashiers-amount-transfer.store');//para poder transferir dinero a otra caja de manera sensilla
    Route::delete('cashiers/{cashier_id}/amount/transfer/{transfer_id}/delete', [CashierController::class, 'cashierAmountTransferDetele'])->name('cashiers-amount-transfer.delete');//para poder eliminar la transferencia
    Route::get('cashiers/{cashier_id}/transfer/{transfer_id}/success', [CashierController::class, 'amountTransferSuccess'])->name('cashiers-transfer.success');//para poder aceptar la transferencia
    Route::get('cashiers/{cashier_id}/transfer/{transfer_id}/decline', [CashierController::class, 'amountTransferDecline'])->name('cashiers-transfer.decline');//para poder rechazar la transferencia
    
    Route::post('cashiers/expense/store', [CashierController::class, 'expense_store'])->name('cashiers.expense.store'); // Agregar gasto
    Route::delete('cashiers/{cashier}/expense/{expense}/delete', [CashierController::class, 'cashierExpenseDelete'])->name('cashiers-expense.delete'); // Agregar gasto

    Route::post('cashiers/{cashier}/change/status', [CashierController::class, 'change_status'])->name('cashiers.change.status');//*** Para que los cajeros Acepte o rechase el dinero dado por Boveda o gerente
    Route::get('cashiers/{cashier}/close/', [CashierController::class, 'close'])->name('cashiers.close');//***para cerrar la caja el cajero vista 
    Route::post('cashiers/{cashier}/close/store', [CashierController::class, 'close_store'])->name('cashiers.close.store'); //para que el cajerop cierre la caja 
    Route::post('cashiers/{cashier}/close/revert', [CashierController::class, 'close_revert'])->name('cashiers.close.revert'); //para revertir el cajero para q su caja vuelva 
    Route::get('cashiers/{cashier}/confirm_close', [CashierController::class, 'confirm_close'])->name('cashiers.confirm_close');
    Route::post('cashiers/{cashier}/confirm_close/store', [CashierController::class, 'confirm_close_store'])->name('cashiers.confirm_close.store');

    Route::get('cashiers/print/open/{id?}', [CashierController::class, 'print_open'])->name('print.open');//para imprimir el comprobante cuando se abre una caja
    Route::get('cashiers/print/close/{id?}', [CashierController::class, 'print_close'])->name('print.close');
    Route::get('cashiers/{id}/print', [CashierController::class, 'print'])->name('cashiers.print');

    Route::delete('cashiers/{cashier}/loans/transaction/{transaction}/delete', [CashierController::class, 'deleteTransaction'])->name('cashiers-loan.transaction.delete');//Para eliminar pagos de prestamos sin prenda
    Route::delete('cashiers/{cashier}loans/{loan}/delete', [CashierController::class, 'deleteLoan'])->name('cashiers-loan.delete');//para pider eliminar prestamos cuando no tenga dias pagados 


    Route::delete('cashiers/{cashier}/pawn/transaction/{transaction}/delete', [CashierController::class, 'pawnTransactionDelete'])->name('cashiers-pawn-transaction.delete');//Para aliminar pagos con prendas
    Route::delete('cashiers/{cashier}/pawn/{pawn}/delete', [CashierController::class, 'pawnDelete'])->name('cashier-pawn.delete');//Para eliminar un prestamo "prendario" de una caja abierta
    Route::delete('cashiers/{cashier}/pawn/aditional/{aditional}/delete', [CashierController::class, 'pawnAmountAditionalDelete'])->name('cashier-pawn-aditional.delete');//Para eliminar el monto adicional que se da por la prenda con la caja abierta

    Route::delete('cashiers/{cashier}/salaryPurchase/{salary}/delete', [CashierController::class, 'salaryPurchaseDelete'])->name('cashiers-salaryPurchase.delete');//Para eliminar un prestamo "prestamo de sueldo" de una caja abierta
    Route::delete('cashiers/{cashier}/salaryPurchase/transaction/{transaction}/delete', [CashierController::class, 'salaryPurchaseTransactionDelete'])->name('cashiers-salaryPurchase-transaction.delete');//Para aliminar pagos con prendas


    Route::delete('cashiers/{cashier}/sale/{saleAgent}/delete', [CashierController::class, 'cashierSaleDelete'])->name('cashier-sale.delete');//Para eliminar una venta al contado y credito como eliminar pagos al credito de una caja abierta


    // Para registrar usuario los gerente, administradores
    Route::resource('user', UserController::class);
    Route::get('user/ajax/list/{search?}', [UserController::class, 'list']);
    Route::get('user/{user?}/inhabilitar', [UserController::class, 'inhabilitarUser'])->name('user.inhabilitar');
    Route::get('userr/{user?}/habilitar', [UserController::class, 'habilitarUser'])->name('user.habilitar');

    //____________________________________________________________________________REPORTE________________________________________________________________________
    // $$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$                   FROM MANAGER ADMINISTRATOR                      $$$$$$$$$$$$$$$$$$$$$$$$$$$$
    //para poder mostrar su recaudacion de la persona CAJERO O COBRADOR EN MOTO
    Route::get('print/dailyCollection', [ReportManagerController::class, 'dailyCollection'])->name('print.dailyCollection');
    Route::post('print/dailyCollection/list', [ReportManagerController::class, 'dailyCollectionList'])->name('print-dailyCollection.list');

    //Para ver el total de los prestamos prestado total
    Route::get('print/loanAll', [ReportManagerController::class, 'loanAll'])->name('print-loanAll');
    Route::post('print/loanAll/list', [ReportManagerController::class, 'loanAllList'])->name('print-loanAll.list');
    
    //Para ver la lista de los prestamos con dias atrazados o lista de deudas atrazadas
    Route::get('print/loanListLate', [ReportController::class, 'loanListLate'])->name('print-loanListLate');
    Route::post('print/loanListLate/list', [ReportController::class, 'loanListLateList'])->name('print-loanListLate.list');

    Route::get('print/general', [ReportController::class, 'general_index'])->name('print.general');
    Route::post('print/general/list', [ReportController::class, 'general_list'])->name('print.general.list');


    Route::get('print/loanGestion', [ReportController::class, 'loanGestion'])->name('print.loanGestion'); //Para los prestamos por gestion
    Route::post('print/loanGestion/list', [ReportController::class, 'loanGestionList'])->name('print-loanGestion.list');

    Route::get('print/loanRangeGestion', [ReportController::class, 'loanRangeGestion'])->name('print.loanRangeGestion'); //Para los prestamos por rango de gestion
    Route::post('print/loanRangeGestion/list', [ReportController::class, 'loanRangeGestionList'])->name('print-loanRangeGestion.list');

    Route::get('print/loanDetailGestion', [ReportController::class, 'loanDetailGestion'])->name('print.loanDetailGestion'); //Para los prestamos por rango de gestion
    Route::post('print/loanDetailGestion/list', [ReportController::class, 'loanDetailGestionList'])->name('print-loanDetailGestion.list');

    Route::get('print/bonusCollection', [ReportController::class, 'bonusCollection'])->name('print.bonusCollection'); //Para obtener el porcentaje de cbranza diaria o por rango de fecha
    Route::post('print/bonusCollection/list', [ReportController::class, 'bonusCollectionList'])->name('print-bonusCollection.list');

    Route::get('print/loanRecovery', [ReportController::class, 'loanRecovery'])->name('print.loanRecovery'); //para los prestamos en recuperacion 
    Route::post('print/loanRecovery/list', [ReportController::class, 'loanRecoveryList'])->name('print-loanRecovery.list');
    
    


    // $$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$                   PARA CAJEROS                      $$$$$$$$$$$$$$$$$$$$$$$$$$$$
    //para poder mostrar su recaudacion de la persona CAJERO O COBRADOR EN MOTO
    Route::get('print/loanCollection', [ReportCashierController::class, 'loanCollection'])->name('print-loanCollection');
    Route::post('print/loanCollection/list', [ReportCashierController::class, 'loanCollectionList'])->name('print-loanCollection.list');

    Route::get('print/loanDelivered', [ReportCashierController::class, 'loanDelivered'])->name('print-loanDelivered');
    Route::post('print/loandelivered/list', [ReportCashierController::class, 'loanDeliveredList'])->name('print-loanDelivered.list');


    //:::::::::::::::::::::::::::::::::::::::::::::::::     INVENTARIO    ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    Route::get('print/inventories', [ReportInventoryController::class, 'inventoryAdd'])->name('print.inventories');
    Route::post('print/inventories/list', [ReportInventoryController::class, 'inventoryAddList'])->name('print-inventories.list');

    //:::::::::::::::::::::::::::::::::::::::::::::::::     VENTAS    ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    Route::get('print/sales', [ReportSaleController::class, 'sale'])->name('print.sales');
    Route::post('print/sales/list', [ReportSaleController::class, 'saleList'])->name('print-sales.list');


    // $$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$                   PARA PRENDA                      $$$$$$$$$$$$$$$$$$$$$$$$$$$$
    // //para poder mostrar su recaudacion de la persona CAJERO O COBRADOR EN MOTO
    // Route::get('print/pawnCollection', [ReportCashierController::class, 'pawnCollection'])->name('print-pawnCollection');
    // Route::post('print/loanCollection/list', [ReportCashierController::class, 'loanCollectionList'])->name('print-loanCollection.list');

    // para generar la lista de cobro diario por rutas
    Route::get('print/dailyList', [ReportCashierController::class, 'dailyList'])->name('print.dailyList');
    Route::post('print/dailyList/list', [ReportCashierController::class, 'dailyListList'])->name('print-dailyList.list');

    Route::get('print/registers', [ReportCashierController::class, 'registers_index'])->name('report.registers.index');
    Route::post('print/registers/list', [ReportCashierController::class, 'registers_list'])->name('report.registers.list');

    Route::resource('gps', GpsController::class);

    // PARA LAS NOTIFICACIONES
    Route::get('getAuth', [Controller::class, 'getAuth'])->name('getAuth');
    Route::get('notification/cashierOpen', [NotificationController::class, 'cashierOpen'])->name('notification.cashierOpen');


    //::::::::::::::::::::::::::::::::::::::::    history   :::::::::::::::::::::::::::::::::::::::::::::::::::

    Route::get('history/dailyList', [HistoryController::class, 'indexDailyList'])->name('history-dailyList.index');
    Route::get('history/dailyList/ajax/list/{search?}', [HistoryController::class, 'listDailyList']);
    Route::get('history/dailyList/{id}/print', [HistoryController::class, 'printDailyList'])->name('history-dailyList.print');
    Route::delete('history/dailyList/{id}/delete', [HistoryController::class, 'destroy'])->name('history-dailyList.delete');
    



    // ''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''   DEV  ''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
    Route::get('pawns/{id}/deliveredMoney', [DevController::class, 'moneyDeliver'])->name('pawns.deliveredMoney');
    // ''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''   DEV  ''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''



    Route::get('dev', [DeveloperController::class, 'index']);
    Route::post('dev/post', [DeveloperController::class, 'store'])->name('dev.post');


    // Reporte

    // Para prestamos actuales vigentes
    Route::get('reports/loans/currentloans', [ReportLoanController::class, 'currentLoans'])->name('reports-loans.currentloans');
    Route::post('reports/loans/currentloans/list', [ReportLoanController::class, 'currentLoansList'])->name('reports-loans.currentloans.list');

    // Para prestamos por gention
    Route::get('reports/loans/loangestions', [ReportLoanController::class, 'loanGestions'])->name('reports-loans.loanGestions');
    Route::post('reports/loans/loangestions/list', [ReportLoanController::class, 'loanGestionsList'])->name('reports-loans.loanGestions.list');

    // Para prestamos por rango de gention
    Route::get('reports/loans/loanrangegestions', [ReportLoanController::class, 'loanRangeGestions'])->name('reports-loans.loanRangeGestions');
    Route::post('reports/loans/loanrangegestions/list', [ReportLoanController::class, 'loanRangeGestionsList'])->name('reports-loans.loanRangeGestions.list');





});





// generado por cron
Route::get('loans/dayLate', [AjaxController::class, 'dayLate']);
Route::get('loans/loanDay/notificationLate', [AjaxController::class, 'notificationLate'])->name('loans-loanDay.notificationLate');
Route::get('loans/dailyListHistory', [AjaxController::class, 'dailyListHistory']);//Para generar un historial de lista diaria de recoleccion al iniciar y finalizar el dia
// Route::get('garments/month/late', [AjaxController::class, 'lateGarment'])->name('garments-month.late');

Route::post('template/loan/search', [TemplateController::class, 'searchLoan'])->name('template-loan.search');
Route::post('template/loan/search/codeVerification', [TemplateController::class, 'codeVerification'])->name('template-loan-search.codeverification');
Route::get('template/loan/search/verification/{loan?}/{phone?}/{code?}', [TemplateController::class, 'verification'])->name('template-loan-search.verification');

Route::get('/admin/clear-cache', function() {
    Artisan::call('optimize:clear');
    // Cache::forget('last_whatsapp_schedule');
    return redirect('/admin')->with(['message' => 'Cache eliminada.', 'alert-type' => 'success']);
})->name('clear.cache');
