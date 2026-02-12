<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ItemCategoriesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('item_categories')->delete();
        
        \DB::table('item_categories')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Joyas',
                'description' => 'Joyas de oro, plata, etc.',
                'status' => 1,
                'created_at' => '2023-09-21 23:32:40',
                'updated_at' => '2023-09-21 23:34:02',
                'deleted_at' => NULL,
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Electrodomesticos',
                'description' => 'Televisores, lavadoras, licuadoras, etc.',
                'status' => 1,
                'created_at' => '2023-09-21 23:32:50',
                'updated_at' => '2023-09-21 23:34:32',
                'deleted_at' => NULL,
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'Vehículos',
                'description' => 'Automóviles, motocicletas, bicicletas, etc.',
                'status' => 1,
                'created_at' => '2023-09-21 23:32:57',
                'updated_at' => '2023-09-21 23:34:55',
                'deleted_at' => NULL,
            ),
        ));
        
        
    }
}