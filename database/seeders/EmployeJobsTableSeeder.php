<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class EmployeJobsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('employe_jobs')->delete();
        
        \DB::table('employe_jobs')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Cajero',
                'description' => NULL,
                'created_at' => '2024-05-08 01:43:43',
                'updated_at' => '2024-05-08 22:49:20',
                'deleted_at' => NULL,
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Cobrador',
                'description' => NULL,
                'created_at' => '2024-05-08 22:49:25',
                'updated_at' => '2024-05-08 22:49:25',
                'deleted_at' => NULL,
            ),
        ));
        
        
    }
}