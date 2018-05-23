<?php

use Illuminate\Database\Seeder;
use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
        	'name' => 'Wesley vieira',
        	'email' => 'admin@admin.com',
        	'password' => bcrypt('wesley12'),
        ]);

        User::create([
            'name' => 'Lucas Dias',
            'email' => 'lucas@admin.com',
            'password' => bcrypt('wesley12'),
        ]);
    }
}
