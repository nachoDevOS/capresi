<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\Permission;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     */
    public function run()
    {

        \DB::table('permissions')->delete();

        Permission::firstOrCreate([
            'key'        => 'browse_admin',
            'table_name' => 'admin',
        ]);

        $keys = [
            // 'browse_admin',
            'browse_bread',
            'browse_database',
            'browse_media',
            'browse_compass',
            'browse_clear-cache'
        ];

        foreach ($keys as $key) {
            Permission::firstOrCreate([
                'key'        => $key,
                'table_name' => null,
            ]);
        }

        Permission::generateFor('menus');
        Permission::generateFor('roles');
        Permission::generateFor('users');
        Permission::generateFor('settings');
        // Permission::generateFor('category_garments');
        // Permission::generateFor('brand_garments');
        // Permission::generateFor('model_garments');
        // Permission::generateFor('jewels');
        // Permission::generateFor('quilates');
        Permission::generateFor('item_categories');
        Permission::generateFor('item_features');
        Permission::generateFor('item_types');
        // Permission::generateFor('pawn');
        Permission::generateFor('payments_periods');
        Permission::generateFor('employe_jobs');
        Permission::generateFor('employes');
        Permission::generateFor('employe_payments');
        Permission::generateFor('expenses');
        Permission::generateFor('managers');
        Permission::generateFor('cashier_movement_categories');
        

        $keys = [
            'browse_articles',
            'add_articles',
            'edit_articles',
            'read_articles',
            'delete_articles',
            'input_articles',
        ];

        foreach ($keys as $key) {
            Permission::firstOrCreate([
                'key'        => $key,
                'table_name' => 'articles',
            ]);
        }


        
        $keys = [
            'browse_vaults',
            'add_vaults',
            'open_vaults',
            'movements_vaults',
            'close_vaults',
            'print_vaults',
        ];

        foreach ($keys as $key) {
            Permission::firstOrCreate([
                'key'        => $key,
                'table_name' => 'vaults',
            ]);
        }

        // para crear prestamos varios
        $keys = [
            'browse_loans',
            'add_loans',
            'read_loans',
            'delete_loans',
            'successLoan_loans',//para que el gerente apruebe el prestamo
            'deliverMoney_loans', //para quye entregen el dinero al beneficiario
            'addMoneyDaily_loans',//para agregar o pagar el prestamo diario
            'addRecovery_loans',//Para poder 

        ];

        foreach ($keys as $key) {
            Permission::firstOrCreate([
                'key'        => $key,
                'table_name' => 'loans',
            ]);
        }


        //Prendario
        $keys = [
            'browse_pawn',
            'add_pawn',
            'read_pawn',
            'delete_pawn',
            'successPawn_pawn',
            'deliverMoney_pawn',
            // 'addMoneyDaily_pawn',
        ];

        foreach ($keys as $key) {
            Permission::firstOrCreate([
                'key'        => $key,
                'table_name' => 'pawn',
            ]);
        }



        // para reportes en general GERENTE Y ADMINISTRADOR
        $keys = [
            'browse_printdailyCollection',
            'browse_printloanAll',
            'browse_printdailyList', //para imprimir la lista diaria de cobro por dias y rutas
            'browse_printloanListLate',
            'browse_printloanCollection', //reportes para el cajero y el cobrador en moto y prenda
            'browse_printloanDelivered', //reportes para obtener los prestamos diarios entregados o en fecha
            'browse_printgeneral',
            'browse_printloanGestion',
            'browse_printloanRangeGestion',
            'browse_printloanDetailGestion',
            'browse_printbonusCollection',
            'browse_printloanRecovery',
            'browse_printregisters'
        ];

        foreach ($keys as $key) {
            Permission::firstOrCreate([
                'key'        => $key,
                'table_name' => 'reports_gerente',
            ]);
        }

        // _________________________________________________________


        //  Rutas

        $keys = [
            'browse_routes',
            'add_routes',
            'edit_routes',
            'read_routes',
            'collector_routes',
            'browse_routesloanexchange'
        ];

        foreach ($keys as $key) {
            Permission::firstOrCreate([
                'key'        => $key,
                'table_name' => 'routes',
            ]);
        }

        // cajeros
        $keys = [
            'browse_cashiers',
            'add_cashiers',
            'read_cashiers',
            // 'open_cashiers',
            // 'movements_cashiers',
            // 'close_vaults',
            // 'print_vaults',
            
        ];
        foreach ($keys as $key) {
            Permission::firstOrCreate([
                'key'        => $key,
                'table_name' => 'cashiers',
            ]);
        }


        // poople
        $keys = [
            'browse_people',
            'add_people',
            'edit_people',
            'read_people',
            'delete_people',
            'sponsor_people',            
        ];

        foreach ($keys as $key) {
            Permission::firstOrCreate([
                'key'        => $key,
                'table_name' => 'people',
            ]);
        }


        $keys = [
            'browse_user',
            'add_user',
            'edit_user',
            'status_user',          
        ];

        foreach ($keys as $key) {
            Permission::firstOrCreate([
                'key'        => $key,
                'table_name' => 'user',
            ]);
        }

        // #################################################################################################
        // ###########################################   PRENDARIO   #######################################
        // #################################################################################################

        $keys = [
            'browse_garments',
            'add_garments',
            'read_garments',
            'delete_garments',
            'successLoan_garments',//para que el gerente apruebe el prestamo del prendario
            'deliverMoney_garments', //para quye entregen el dinero al beneficiario
            // 'addMoneyDaily_garments',//para agregar o pagar el prestamo diario
        ];

        foreach ($keys as $key) {
            Permission::firstOrCreate([
                'key'        => $key,
                'table_name' => 'garments',
            ]);
        }



        //###################################### ADM. ASISTENCIA #######################################
        $keys = [
            'browse_attendances',
            'add_attendances',
        ];

        foreach ($keys as $key) {
            Permission::firstOrCreate([
                'key'        => $key,
                'table_name' => 'attendances',
            ]);
        }


        $keys = [
            'browse_hours',
            'add_hours',
        ];

        foreach ($keys as $key) {
            Permission::firstOrCreate([
                'key'        => $key,
                'table_name' => 'hours',
            ]);
        }

        $keys = [
            'browse_shifts',
            'add_shifts',
            'delete_shifts',
            'read_shifts',
            'edit_shifts',
        ];

        foreach ($keys as $key) {
            Permission::firstOrCreate([
                'key'        => $key,
                'table_name' => 'shifts',
            ]);
        }

        $keys = [
            'browse_late_penalties',
            'add_late_penalties',
            'delete_late_penalties',
            'read_late_penalties',
        ];

        foreach ($keys as $key) {
            Permission::firstOrCreate([
                'key'        => $key,
                'table_name' => 'late_penalties',
            ]);
        }

        $keys = [
            'browse_penalty_fouls',
            'add_penalty_fouls',
            'delete_penalty_fouls',
            'read_penalty_fouls',
            'edit_penalty_fouls',
        ];

        foreach ($keys as $key) {
            Permission::firstOrCreate([
                'key'        => $key,
                'table_name' => 'penalty_fouls',
            ]);
        }

        //################################## ADMINISTRACION ###################################
        $keys = [
            'browse_contracts',
            'add_contracts',
            'delete_contracts',
            'read_contracts',
            'edit_contracts',
            'add_contractsAdvancement',
            'add_contractsLicense'
        ];

        foreach ($keys as $key) {
            Permission::firstOrCreate([
                'key'        => $key,
                'table_name' => 'contracts',
            ]);
        }

        $keys = [
            'browse_spreadsheets',
            'add_spreadsheets',
            'delete_spreadsheets',
            'read_spreadsheets',
            'edit_spreadsheets',
        ];

        foreach ($keys as $key) {
            Permission::firstOrCreate([
                'key'        => $key,
                'table_name' => 'spreadsheets',
            ]);
        }

        $keys = [
            'browse_bonus',
            'add_bonus',
            'delete_bonus',
            'read_bonus',
            'edit_bonus',
        ];

        foreach ($keys as $key) {
            Permission::firstOrCreate([
                'key'        => $key,
                'table_name' => 'bonus',
            ]);
        }


        //Pago de planilla
        $keys = [
            'browse_paymentSheet',
        ];

        foreach ($keys as $key) {
            Permission::firstOrCreate([
                'key'        => $key,
                'table_name' => 'paymentSheet',
            ]);
        }



        // :::::::::::::::::::::::::::     INVENTARIO     ::::::::::::::::::::::::::::::

        Permission::generateFor('inventories');
        $keys = [
            'browse_printinventories' //Para reporte de registro de inventario Manual||Prendario
        ];

        foreach ($keys as $key) {
            Permission::firstOrCreate([
                'key'        => $key,
                'table_name' => 'report_inventories',
            ]);
        }


        // :::::::::::::::::::::::::::     Ventas     ::::::::::::::::::::::::::::::

        Permission::generateFor('sales');
        $keys = [
            'browse_printsales' //Para reporte de ventas
        ];

        foreach ($keys as $key) {
            Permission::firstOrCreate([
                'key'        => $key,
                'table_name' => 'report_sales',
            ]);
        }


        // :::::::::::::::::::::::::::::    COMPRA DE SUELDO    :::::::::::::::::::::::::

        $keys = [
            'browse_salary_purchases',
            'add_salary_purchases',
            'delete_salary_purchases',
            'read_salary_purchases',
            'edit_salary_purchases',
        ];
        foreach ($keys as $key) {
            Permission::firstOrCreate([
                'key'        => $key,
                'table_name' => 'salary_purchases',
            ]);
        }

        // Permission::generateFor('salary_purchases');




    }
}
