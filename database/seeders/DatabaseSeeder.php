<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use KodePandai\Indonesia\IndonesiaDatabaseSeeder;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        DB::table('users')->insert([
            "name" => "admin",
            "email"=>"admin@gmail.com",
            "password" => Hash::make('admin12345'),
        ]);

        $this->call(IndonesiaDatabaseSeeder::class);
    }
}
