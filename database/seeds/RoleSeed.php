<?php

use Illuminate\Database\Seeder;

class RoleSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            
            ['id' => 1, 'title' => 'Administrator (can create other users)'],
            ['id' => 2, 'title' => 'Simple User'],
            ['id' => 3, 'title' => 'File Manager'],
            ['id' => 4, 'title' => 'ROR Supervisor'],
            ['id' => 5, 'title' => 'ROR Employee'],
        ];

        foreach ($items as $item) {
            \App\Role::create($item);
        }
    }
}
