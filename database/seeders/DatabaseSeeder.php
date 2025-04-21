<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */

    
    public function run()
    {
        // Changer explicitement le format de date pour cette session
        // DB::statement("SET LANGUAGE us_english");
        DB::statement("SET DATEFORMAT mdy");
        DB::statement("SET LANGUAGE ENGLISH;"); 

        $this->call([
            RoleSeeder::class,
        ]);
    
        // Puis faire l'insertion avec le format ISO

        // $password = Hash::make("password");
        // DB::insert("
        //     INSERT INTO users (name, email, email_verified_at, password, full_name, phone, created_at, updated_at)
        //     VALUES (
        //         'admin', 
        //         'admin@admin.com', 
        //         CONVERT(datetime, '2025-04-16T14:57:41', 126), 
        //         '123456789', 
        //         '{$password}', 
        //         '05483847374', 
        //         CONVERT(datetime, '2025-04-16T14:57:42.100', 126), 
        //         CONVERT(datetime, '2025-04-16T14:57:42.100', 126)
        //     )
        // ");

        // Role::create([
        //     "name" => "admin",
        // ]);

       

        

        // User::create([
        //     'name' => 'admin',
        //     'email' => 'admin@admin.com',
        //     'email_verified_at' => Carbon::parse('2025-04-16 14:57:41'),
        //     'password' => bcrypt('password'),
        //     'full_name' => 'Hassan Agmir',
        //     'phone' => '05483847374',
        //     'created_at' => Carbon::now(),
        //     'updated_at' => Carbon::now(),
        // ]);
        
    }
}
