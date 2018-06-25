<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\User::class, 20)->create()->each(function ($u) {
            $u->transactions()->save(factory(App\Transaction::class)->make());
            $u->transactions()->save(factory(App\Transaction::class)->make());
            $u->transactions()->save(factory(App\Transaction::class)->make());
            $u->transactions()->save(factory(App\Transaction::class)->make());
            $u->transactions()->save(factory(App\Transaction::class)->make());
        });
    }
}
