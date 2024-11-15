<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\EncryptionKey;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //\App\Models\Tenant::factory(3)->create();
        //\App\Models\User::factory(10)->create();
        \App\Models\Post::factory(100)->create();
        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        //// check first encryption key exists 
        //$key = EncryptionKey::where('key',explode("base64:",env('APP_KEY'))[1])->first();
        // if(!isset($key)){
        //     $key = new EncryptionKey();
        //     $key->version = 1;
        //     $key->key = explode("base64:",env('APP_KEY'))[1];
        //     $key->save();
        // }
    }
}
