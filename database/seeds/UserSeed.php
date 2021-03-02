<?php

use Illuminate\Database\Seeder;

class UserSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            
            ['id' => 1, 'name' => 'Admin', 'email' => 'admin@admin.com', 'password' => '$2y$10$u5EBywjXyKqzJ1BlHxiO1uAfQl30K5jq1amO9TCfheqZXm5hMFe62', 'role_id' => 1, 'remember_token' => '',],
            ['id' => 2, 'name' => 'Simple User', 'email' => 'simple@simple.simple', 'password' => '$2y$10$T0aGonvYLJqLM.4GEWjyteL/80CrnGbTs5Rd6Yk8WOr69MLbTSAxm', 'role_id' => 2, 'remember_token' => '',],
            ['id' => 3, 'name' => 'File Manager', 'email' => 'file@file.file', 'password' => '$2y$10$bekzGFfUCuWsswukGEQhoOVhdu4nWacVyJsAPQpag8Fg40RfwL/Bi', 'role_id' => 3, 'remember_token' => '',],
            ['id' => 4, 'name' => 'ROR Supervisor', 'email' => 'ror2@ror2.ror2', 'password' => '$2y$10$rhxjs9EDxYghLa9GUnb0BONlz75jnH4mi0H621mFd/pQ41wG3JvyK', 'role_id' => 4, 'remember_token' => '',],
            ['id' => 5, 'name' => 'ROR Employee', 'email' => 'ror1@ror1.ror1', 'password' => '$2y$10$u5EBywjXyKqzJ1BlHxiO1uAfQl30K5jq1amO9TCfheqZXm5hMFe62', 'role_id' => 5, 'remember_token' => '',]

        ];

        foreach ($items as $item) {
            \App\User::create($item);
        }
    }
}
