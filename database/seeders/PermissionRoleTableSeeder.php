<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\Permission;
use TCG\Voyager\Models\Role;

class PermissionRoleTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     *
     * @return void
     */
    public function run()
    {
        
        $role = Role::where('name', 'admin')->firstOrFail();

        $permissions = Permission::all();

        $role->permissions()->sync(
            $permissions->pluck('id')->all()
        );


        //############## Gerente ####################
        $role = Role::where('name', 'gerente')->firstOrFail();
        $permissions = Permission::whereRaw('table_name = "admin" or
                                            table_name = "vaults" or
                                            table_name = "people" or
                                            table_name = "cashiers" or
                                            table_name = "routes" or

                                            table_name = "user" or

                                            table_name = "category_garments" or
                                            table_name = "brand_garments" or
                                            table_name = "model_garments" or
                                            table_name = "articles" or
                                            table_name = "jewels" or
                                            table_name = "quilates" or
                                            table_name = "garments" or

                                            table_name = "attendances" or
                                            table_name = "hours" or
                                            table_name = "shifts" or
                                            table_name = "late_penalties" or
                                            table_name = "penalty_fouls" or
                                            table_name = "contracts" or
                                            table_name = "employe_jobs" or 
                                            table_name = "spreadsheets" or 
                                            table_name = "bonus" or 
                                            table_name = "expenses" or 
                                            table_name = "paymentSheet" or
                                            table_name = "cashier_movement_categories" or

                                            table_name = "pawn" or


                                            table_name = "inventories" or 
                                            table_name = "sales" or 
                                            table_name = "salary_purchases" or 

                                            table_name = "report_inventories" or 
                                            table_name = "report_sales" or 





                                            `key` = "browse_loans" or 
                                            `key` = "delete_loans" or


                                            `key` = "browse_printdailyCollection" or 
                                            `key` = "browse_printloanAll" or 
                                            `key` = "browse_printloanCollection" or                                             
                                            `key` = "browse_printloanListLate" or 
                                            `key` = "browse_printdailyList" or
                                            `key` = "browse_printgeneral" or 
                                            `key` = "browse_printloanGestion" or 
                                            `key` = "browse_printloanRangeGestion" or 
                                            `key` = "browse_printloanDetailGestion" or 
                                            `key` = "browse_printbonusCollection" or 
                                            `key` = "browse_printloanRecovery" or 



                                            `key` = "browse_printregisters" or
                                            `key` = "browse_printloanDelivered" or

                                            table_name = "pawn" or
                                            table_name = "loans" or
                                            table_name = "item_types" or


                                            table_name = "settings" or 
                                            table_name = "managers" or
                                            `key` = "browse_clear-cache"')->get();
        $role->permissions()->sync($permissions->pluck('id')->all());

        //############## Administrador ####################
        $role = Role::where('name', 'administrador')->firstOrFail();
        $permissions = Permission::whereRaw('table_name = "admin" or
                                            table_name = "people" or
                                            table_name = "user" or
                                            table_name = "cashiers" or
                                            table_name = "routes" or

                                            table_name = "attendances" or
                                            table_name = "hours" or
                                            table_name = "shifts" or
                                            table_name = "late_penalties" or
                                            table_name = "penalty_fouls" or
                                            table_name = "contracts" or
                                            table_name = "employe_jobs" or
                                            table_name = "spreadsheets" or 
                                            table_name = "bonus" or 
                                            table_name = "expenses" or 
                                            table_name = "paymentSheet" or
                                            table_name = "cashier_movement_categories" or

                                            table_name = "loans" or


                                            table_name = "inventories" or 
                                            table_name = "sales" or
                                            table_name = "salary_purchases" or 

                                            table_name = "report_inventories" or 
                                            table_name = "report_sales" or 






                                            




                                            `key` = "browse_printdailyCollection" or
                                            `key` = "browse_printloanCollection" or                                             
                                            `key` = "browse_printloanListLate" or
                                            `key` = "browse_printdailyList" or
                                            `key` = "browse_printgeneral" or 
                                            `key` = "browse_printloanGestion" or 
                                            `key` = "browse_printloanRangeGestion" or 
                                            `key` = "browse_printloanDetailGestion" or 
                                            `key` = "browse_printbonusCollection" or 
                                            `key` = "browse_printloanRecovery" or 

                                            `key` = "browse_printregisters" or
                                            `key` = "browse_printloanDelivered" or

                                            table_name = "item_types" or

                                            table_name = "pawn" or

                                            
                                            

                                            `key` = "browse_clear-cache"')->get();
        $role->permissions()->sync($permissions->pluck('id')->all());

        

        //############## Cajero ####################
        $role = Role::where('name', 'cajeros')->firstOrFail();
        $permissions = Permission::whereRaw('table_name = "admin" or
                                            table_name = "people" or

                                            `key` = "browse_loans" or
                                            `key` = "add_loans" or
                                            `key` = "delete_loans" or

                                            `key` = "deliverMoney_loans" or 
                                            
                                            `key` = "addMoneyDaily_loans" or 


                                            `key` = "browse_printloanCollection" or 
                                            `key` = "browse_printdailyList" or
                                            `key` = "browse_printloanDelivered" or
                                            `key` = "browse_clear-cache"')->get();
        $role->permissions()->sync($permissions->pluck('id')->all());

        //############## Cobrador ####################
        $role = Role::where('name', 'cobrador')->firstOrFail();
        $permissions = Permission::whereRaw('table_name = "admin" or

                                            `key` = "browse_loans" or
                                            
                                            `key` = "addMoneyDaily_loans" or 
                                            `key` = "browse_printloanCollection" or 
                                            `key` = "browse_printdailyList" or


                                            
                                            `key` = "browse_clear-cache"')->get();
        $role->permissions()->sync($permissions->pluck('id')->all());




        //############## Prendario ####################
        $role = Role::where('name', 'prenda')->firstOrFail();
        $permissions = Permission::whereRaw('table_name = "admin" or

                                            table_name = "brand_garments" or
                                            table_name = "category_garments" or
                                            table_name = "model_garments" or
                                            table_name = "articles" or
                                            table_name = "garments" or

                                            table_name = "people" or

                                            table_name = "item_types" or 

                                            table_name = "attendances" or
                                            table_name = "contracts" or
                                            table_name = "spreadsheets" or 
                                            table_name = "bonus" or 
                                            table_name = "paymentSheet" or





                                            


                                            table_name = "inventories" or 
                                            table_name = "sales" or 
                                            table_name = "salary_purchases" or 

                                            table_name = "report_inventories" or 
                                            table_name = "report_sales" or 

                                            

                                            `key` = "browse_pawn" or 
                                            `key` = "add_pawn" or 
                                            `key` = "read_pawn" or 
                                            `key` = "delete_pawn" or 
                                            `key` = "deliverMoney_pawn" or 

                                            `key` = "browse_printloanCollection" or  
                                            `key` = "browse_printloanDelivered" or



                                            
                                            `key` = "browse_clear-cache"')->get();
        $role->permissions()->sync($permissions->pluck('id')->all());
    }

    
}